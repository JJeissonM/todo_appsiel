<?php

namespace App\Contabilidad\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Contabilidad\Exports\AuxiliarXlsxWriter;

class AuxiliarTercerosService
{
    public static $months = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    private $filters;
    private $start;
    private $end;

    public function __construct(array $filters)
    {
        $validator = Validator::make($filters, [
            'empresa'=>'required|integer|min:1', 'ano'=>'required|integer|between:1900,9998',
            'tercero'=>'integer|min:1', 'cuenta'=>'integer|min:1',
            'cuenta_desde'=>'regex:/^[0-9]{1,30}$/', 'cuenta_hasta'=>'regex:/^[0-9]{1,30}$/'
        ]);
        if ($validator->fails()) { throw new \InvalidArgumentException($validator->errors()->first()); }
        if (!empty($filters['cuenta_desde']) && !empty($filters['cuenta_hasta']) && strcmp($filters['cuenta_desde'], $filters['cuenta_hasta']) > 0) {
            throw new \InvalidArgumentException('El código de cuenta inicial debe ser menor o igual al final.');
        }
        $this->filters = $filters;
        $this->start = sprintf('%04d-01-01', $filters['ano']);
        $this->end = sprintf('%04d-01-01', $filters['ano'] + 1);
    }

    public function baseQuery()
    {
        $q = DB::table('contab_movimientos as m')->where('m.core_empresa_id', $this->filters['empresa'])->where('m.fecha', '<', $this->end);
        foreach (['tercero'=>'core_tercero_id', 'cuenta'=>'contab_cuenta_id'] as $filter=>$field) {
            if (!empty($this->filters[$filter])) { $q->where('m.'.$field, $this->filters[$filter]); }
        }
        if (!empty($this->filters['cuenta_desde']) || !empty($this->filters['cuenta_hasta'])) {
            $filters = $this->filters;
            $q->whereIn('m.contab_cuenta_id', function ($accounts) use ($filters) {
                $accounts->select('id')->from('contab_cuentas');
                if (!empty($filters['cuenta_desde'])) { $accounts->where('codigo', '>=', $filters['cuenta_desde']); }
                if (!empty($filters['cuenta_hasta'])) { $accounts->where('codigo', '<=', $filters['cuenta_hasta']); }
            });
        }
        // Igual que get_movimiento_contable: sin filtro de estado ni naturaleza.
        return $q;
    }

    public function summaryQuery()
    {
        $q = $this->baseQuery()->select('m.core_tercero_id', 'm.contab_cuenta_id')
            ->selectRaw('SUM(CASE WHEN m.fecha < ? THEN m.valor_saldo ELSE 0 END) AS inicial', [$this->start])
            ->selectRaw('SUM(CASE WHEN m.fecha >= ? THEN 1 ELSE 0 END) AS movimientos', [$this->start])
            ->selectRaw('SUM(CASE WHEN ABS(m.valor_saldo - m.valor_debito - m.valor_credito) > 0.005 THEN 1 ELSE 0 END) AS inconsistencias')
            ->selectRaw('SUM(CASE WHEN m.valor_credito > 0 THEN 1 ELSE 0 END) AS creditos_positivos');
        foreach (range(1, 12) as $month) {
            $from = sprintf('%04d-%02d-01', $this->filters['ano'], $month);
            $to = $month === 12 ? $this->end : sprintf('%04d-%02d-01', $this->filters['ano'], $month + 1);
            foreach (['d'=>'valor_debito','c'=>'valor_credito','s'=>'valor_saldo'] as $alias=>$field) {
                $q->selectRaw('SUM(CASE WHEN m.fecha >= ? AND m.fecha < ? THEN m.'.$field.' ELSE 0 END) AS '.$alias.$month, [$from, $to]);
            }
        }
        foreach (['debito'=>'valor_debito','credito'=>'valor_credito','saldo'=>'valor_saldo'] as $alias=>$field) {
            $q->selectRaw('SUM(CASE WHEN m.fecha >= ? THEN m.'.$field.' ELSE 0 END) AS '.$alias, [$this->start]);
        }
        return $q->groupBy('m.core_tercero_id', 'm.contab_cuenta_id')->orderBy('m.core_tercero_id')->orderBy('m.contab_cuenta_id');
    }

    public function detailQuery()
    {
        return $this->baseQuery()->where('m.fecha', '>=', $this->start)
            ->leftJoin('core_tipos_docs_apps as doc', 'doc.id', '=', 'm.core_tipo_doc_app_id')
            ->select('m.*', 'doc.prefijo as documento_tipo')
            ->orderBy('m.core_tercero_id')->orderBy('m.contab_cuenta_id')->orderBy('m.fecha')->orderBy('m.id');
    }

    private function key($row)
    {
        return $row->core_tercero_id.':'.$row->contab_cuenta_id;
    }

    private function closeEnough($expected, $actual, $label)
    {
        // Columnas DOUBLE en origen: tolerancia de medio centavo, sin redondear cada asiento.
        if (abs($expected - $actual) > 0.005) {
            throw new \RuntimeException('No coincide la validación '.$label.'. No se entrega un reporte incompleto.');
        }
    }

    private function validatePair($summary, array $actual)
    {
        $debit = $credit = $balance = 0.0;
        foreach (range(1, 12) as $month) {
            foreach (['d', 'c', 's'] as $field) {
                $this->closeEnough($summary->{$field.$month}, $actual[$month][$field], $field.' del mes '.$month.' ('.$this->key($summary).')');
            }
            $debit += $summary->{'d'.$month}; $credit += $summary->{'c'.$month}; $balance += $summary->{'s'.$month};
        }
        $this->closeEnough($summary->debito, $debit, 'débitos anuales');
        $this->closeEnough($summary->credito, $credit, 'créditos anuales');
        $this->closeEnough($summary->inicial + $summary->saldo, $summary->inicial + $balance, 'saldo de diciembre');
        $this->closeEnough($summary->movimientos, $actual['count'], 'cantidad de movimientos');
    }

    private function emptyTotals()
    {
        return array_fill(1, 12, ['d'=>0.0, 'c'=>0.0, 's'=>0.0]) + ['count'=>0];
    }

    /** Genera un ZIP con Excel(s) y acta de validación, dentro de un directorio privado. */
    public function export($directory, $rowLimit = 1048575)
    {
        if ($rowLimit < 1 || $rowLimit > 1048575) { throw new \InvalidArgumentException('Límite de filas inválido.'); }
        if (!is_dir($directory) && !mkdir($directory, 0700, true)) { throw new \RuntimeException('No se pudo crear el directorio de salida.'); }
        $db = DB::connection();
        $db->disableQueryLog();
        $pdo = $db->getPdo();
        $mysql = $db->getDriverName() === 'mysql';
        $statement = null; $buffered = null;
        if ($mysql) { $db->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'); }
        $db->beginTransaction();
        try {
            // Sólo agregados y catálogos de los IDs involucrados permanecen en memoria.
            $summaries = $this->summaryQuery()->get();
            $thirdIds = $accountIds = [];
            foreach ($summaries as $s) { $thirdIds[$s->core_tercero_id] = $s->core_tercero_id; $accountIds[$s->contab_cuenta_id] = $s->contab_cuenta_id; }
            $thirds = $this->catalog('core_terceros', $thirdIds, ['id','numero_identificacion','descripcion']);
            $accounts = $this->catalog('contab_cuentas', $accountIds, ['id','codigo','descripcion']);
            $parts = [[]]; $index = []; $count = 0; $part = 0;
            $audit = ['empresa'=>(int)$this->filters['empresa'], 'ano'=>(int)$this->filters['ano'], 'filtros'=>$this->filters, 'combinaciones'=>count($summaries), 'movimientos'=>0, 'debito_anual'=>0.0, 'credito_anual'=>0.0, 'saldo_inicial'=>0.0, 'saldo_final'=>0.0, 'saldos_historicos_inconsistentes'=>0, 'creditos_positivos_historicos'=>0, 'combinaciones_sin_tercero'=>0, 'combinaciones_sin_cuenta'=>0, 'movimientos_sin_tipo_documento'=>0];
            foreach ($summaries as $s) {
                if ($s->movimientos > $rowLimit) { throw new \RuntimeException('Un tercero y cuenta supera el límite de una hoja. Se requiere una entrega alternativa para este filtro.'); }
                if (count($parts[$part]) && ($count + $s->movimientos > $rowLimit || count($parts[$part]) >= $rowLimit)) { $parts[++$part] = []; $count = 0; }
                $key = $this->key($s); $parts[$part][$key] = $s; $index[$key] = $part;
                $count += $s->movimientos;
                $audit['movimientos'] += $s->movimientos;
                $audit['debito_anual'] += $s->debito; $audit['credito_anual'] -= $s->credito;
                $audit['saldo_inicial'] += $s->inicial; $audit['saldo_final'] += $s->inicial + $s->saldo;
                $audit['saldos_historicos_inconsistentes'] += $s->inconsistencias;
                $audit['creditos_positivos_historicos'] += $s->creditos_positivos;
                $audit['combinaciones_sin_tercero'] += !isset($thirds[$s->core_tercero_id]);
                $audit['combinaciones_sin_cuenta'] += !isset($accounts[$s->contab_cuenta_id]);
            }
            $audit['ejemplos_saldos_inconsistentes'] = $this->baseQuery()
                ->whereRaw('ABS(m.valor_saldo - m.valor_debito - m.valor_credito) > 0.005')
                ->orderBy('m.id')->take(100)->get(['m.id', 'm.fecha', 'm.core_tercero_id', 'm.contab_cuenta_id', 'm.valor_debito', 'm.valor_credito', 'm.valor_saldo']);
            $headers = ['Identificación','Tercero','Código cuenta','Cuenta','Saldo inicial'];
            foreach (self::$months as $month) { foreach (['Débito ', 'Crédito ', 'Saldo '] as $label) { $headers[] = $label.$month; } }
            $headers = array_merge($headers, ['Débito anual','Crédito anual','Saldo final']);
            $detailHeaders = ['Fecha','Tipo documento','Número','Identificación','Tercero','Código cuenta','Cuenta','Concepto','Débito','Crédito','Saldo acumulado','ID movimiento','ID tercero','ID cuenta','ID transacción','ID tipo documento','Documento soporte','Estado'];
            $writers = $detailSheets = $summarySheets = $paths = [];
            foreach ($parts as $i=>$rows) {
                $dir = $directory.'/parte_'.($i + 1); mkdir($dir, 0700);
                $writers[$i] = new AuxiliarXlsxWriter($dir);
                $summarySheets[$i] = $writers[$i]->sheet('AUXILIAR_TERCEROS_MENSUAL', $headers);
                $detailSheets[$i] = $writers[$i]->sheet('AUXILIAR_TERCEROS_DETALLE', $detailHeaders);
            }
            $query = $this->detailQuery();
            if ($mysql) { $buffered = $pdo->getAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY); $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false); }
            $statement = $pdo->prepare($query->toSql());
            $statement->execute($query->getBindings());
            $previous = null; $totals = $this->emptyTotals(); $firstRows = []; $seen = [];
            while ($r = $statement->fetch(\PDO::FETCH_OBJ)) {
                $key = $this->key($r); $i = $index[$key]; $s = $parts[$i][$key];
                if ($key !== $previous) {
                    if ($previous !== null) { $this->validatePair($parts[$index[$previous]][$previous], $totals); }
                    $totals = $this->emptyTotals(); $delta = 0.0;
                    $previous = $key; $seen[$key] = true;
                }
                $month = (int)substr($r->fecha, 5, 2);
                $totals[$month]['d'] += $r->valor_debito; $totals[$month]['c'] += $r->valor_credito; $totals[$month]['s'] += $r->valor_saldo; $totals['count']++;
                $delta += $r->valor_saldo;
                $balance = (float)$s->inicial + $delta;
                $identity = $this->identity($s, $thirds, $accounts);
                $row = $writers[$i]->row($detailSheets[$i], array_merge([$r->fecha, $r->documento_tipo === null ? 'Tipo no encontrado #'.$r->core_tipo_doc_app_id : $r->documento_tipo, (string)$r->consecutivo], $identity, [$r->detalle_operacion, (float)$r->valor_debito, -(float)$r->valor_credito, $balance, (string)$r->id, (string)$r->core_tercero_id, (string)$r->contab_cuenta_id, (string)$r->core_tipo_transaccion_id, (string)$r->core_tipo_doc_app_id, $r->documento_soporte, $r->estado]));
                if (!isset($firstRows[$key][$month])) { $firstRows[$key][$month] = $row; }
                $audit['movimientos_sin_tipo_documento'] += $r->documento_tipo === null;
            }
            $statement->closeCursor(); $statement = null;
            if ($mysql) { $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $buffered); }
            if ($previous !== null) { $this->validatePair($parts[$index[$previous]][$previous], $totals); }
            // La instantánea ya fue leída; no retener la conexión durante la compresión.
            $db->commit();
            foreach ($parts as $i=>$rows) {
                foreach ($rows as $key=>$s) {
                    if (!isset($seen[$key])) { $this->validatePair($s, $this->emptyTotals()); }
                    $values = array_merge($this->identity($s, $thirds, $accounts), [(float)$s->inicial]);
                    $delta = 0.0; $links = [];
                    foreach (range(1, 12) as $month) {
                        $delta += $s->{'s'.$month};
                        $balance = (float)$s->inicial + $delta;
                        if (isset($firstRows[$key][$month])) {
                            foreach (range(count($values), count($values) + 2) as $column) { $links[$column] = "'AUXILIAR_TERCEROS_DETALLE'!A".$firstRows[$key][$month]; }
                        }
                        $values = array_merge($values, [(float)$s->{'d'.$month}, -(float)$s->{'c'.$month}, $balance]);
                    }
                    if (!empty($firstRows[$key])) {
                        foreach ([41, 42, 43] as $column) { $links[$column] = "'AUXILIAR_TERCEROS_DETALLE'!A".min($firstRows[$key]); }
                    }
                    $writers[$i]->row($summarySheets[$i], array_merge($values, [(float)$s->debito, -(float)$s->credito, $balance]), $links);
                }
                $paths[] = $directory.'/auxiliar_terceros_'.$this->filters['empresa'].'_'.$this->filters['ano'].'_parte_'.($i + 1).'.xlsx';
                $writers[$i]->save(end($paths)); rmdir($directory.'/parte_'.($i + 1));
            }
            $audit['archivos_excel'] = count($paths);
            $audit['validaciones'] = 'OK: débitos y créditos mensuales vs anuales; diciembre vs saldo final; resumen vs detalle por tercero, cuenta y mes; conteo exacto de movimientos.';
            file_put_contents($directory.'/validaciones.json', json_encode($audit, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $readme = "LIBRO AUXILIAR POR TERCEROS\nEmpresa: ".$this->filters['empresa'].". Año: ".$this->filters['ano'].".\nCada Excel contiene las dos hojas solicitadas. Las partes son disjuntas por tercero y cuenta; no sume dos veces saldos entre meses.\nLos enlaces en los importes mensuales llevan al primer movimiento del mes. El saldo incluye el inicial y los meses previos. Filtre el detalle por ID tercero, ID cuenta y fecha.\nSaldo inicial = SUM(valor_saldo) anterior al 1 de enero; saldo mensual = inicial + SUM(valor_saldo) hasta el cierre. Crédito mostrado = -valor_credito, conservando reversiones. No se invierte por naturaleza.\nSe incluyen todos los estados, igual que el auxiliar existente. Appsiel elimina los movimientos al anular documentos. No se modificó información contable.\nConsulte validaciones.json. Las inconsistencias históricas y los terceros faltantes se conservan; requieren revisión antes de presentar el reporte. Identificaciones y nombres son los del catálogo actual.\n";
            file_put_contents($directory.'/LEAME.txt', $readme);
            $zipPath = $directory.'/auxiliar_terceros_'.$this->filters['empresa'].'_'.$this->filters['ano'].'.zip';
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) { throw new \RuntimeException('No se pudo crear el paquete.'); }
            foreach (array_merge($paths, [$directory.'/validaciones.json', $directory.'/LEAME.txt']) as $path) {
                $zip->addFile($path, basename($path));
                if (substr($path, -5) === '.xlsx') { $zip->setCompressionName(basename($path), \ZipArchive::CM_STORE); }
            }
            if (!$zip->close()) { throw new \RuntimeException('No se pudo finalizar el paquete.'); }
            return ['path'=>$zipPath, 'audit'=>$audit];
        } finally {
            if ($statement) { $statement->closeCursor(); }
            if ($mysql && $buffered !== null) { $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, $buffered); }
            if ($db->transactionLevel() > 0) { $db->rollBack(); }
        }
    }

    private function catalog($table, array $ids, array $columns)
    {
        $result = [];
        foreach (array_chunk(array_values($ids), 1000) as $chunk) {
            foreach (DB::table($table)->whereIn('id', $chunk)->get($columns) as $row) { $result[$row->id] = $row; }
        }
        return $result;
    }

    private function identity($s, array $thirds, array $accounts)
    {
        $t = isset($thirds[$s->core_tercero_id]) ? $thirds[$s->core_tercero_id] : null;
        $c = isset($accounts[$s->contab_cuenta_id]) ? $accounts[$s->contab_cuenta_id] : null;
        return [$t ? (string)$t->numero_identificacion : '', $t ? $t->descripcion : 'Tercero no encontrado #'.$s->core_tercero_id, $c ? (string)$c->codigo : '', $c ? $c->descripcion : 'Cuenta no encontrada #'.$s->contab_cuenta_id];
    }

    public static function removeDirectory($directory)
    {
        if (!is_dir($directory)) { return; }
        foreach (new \FilesystemIterator($directory) as $file) {
            if ($file->isDir()) { self::removeDirectory($file->getPathname()); } else { unlink($file->getPathname()); }
        }
        rmdir($directory);
    }
}
