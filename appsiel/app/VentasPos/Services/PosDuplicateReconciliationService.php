<?php

namespace App\VentasPos\Services;

use PDO;
use RuntimeException;

/** Data repair only: no Eloquent events, counters, DDL, or automatic migration. */
class PosDuplicateReconciliationService
{
    private $pdo;
    private $mysql;
    private $columns = array();
    private $checks = array();
    private $changes = array();
    private $owners = array();
    private $headers = array();
    private $prefixes = array();
    private $required = array('vtas_pos_doc_encabezados', 'vtas_pos_doc_registros',
        'vtas_pos_movimientos', 'vtas_movimientos', 'teso_movimientos',
        'contab_movimientos', 'cxc_movimientos', 'cxc_abonos', 'core_consecutivos_documentos');

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if (!in_array($driver, array('mysql', 'sqlite'), true)) {
            throw new RuntimeException('La conciliación solo admite MySQL/MariaDB (SQLite para pruebas).');
        }
        $this->mysql = $driver === 'mysql';
    }

    public function reconcile($execute, $directory)
    {
        if ($this->pdo->inTransaction()) {
            throw new RuntimeException('Ejecute la conciliación fuera de otra transacción.');
        }
        $this->checks = $this->changes = $this->owners = $this->headers = $this->prefixes = array();
        $this->prepareDirectory($directory);
        $committed = false;
        $commitAttempted = false;
        try {
            // Discovery is read only, including when --ejecutar was requested.
            if ($this->mysql) {
                $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
                $this->pdo->exec('SET TRANSACTION READ ONLY');
            }
            $this->pdo->beginTransaction();
            $plan = $this->buildPlan();
            $this->pdo->rollBack();
            $this->writeJson($directory.'/plan.json', $plan);
            $inverse = array();
            foreach ($plan['changes'] as $change) {
                $inverse[] = array('table' => $change['table'], 'id' => $change['id'],
                    'before' => $change['after'], 'after' => $change['before']);
            }
            $this->writeJson($directory.'/rollback-plan.json', $inverse);
            $this->writeJson($directory.'/manifest.json', array(
                'plan.json' => hash_file('sha256', $directory.'/plan.json'),
                'rollback-plan.json' => hash_file('sha256', $directory.'/rollback-plan.json')));
            if (!$execute || !$plan['documents']) {
                $this->writeJson($directory.'/result.json', array('status' => 'DRY_RUN', 'changes' => count($plan['changes'])));
                return $plan;
            }

            // Counter first, like the invoice writers. All series are locked in a stable order.
            if ($this->mysql) {
                $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            }
            $this->pdo->beginTransaction();
            foreach ($plan['series'] as $series) {
                $rows = $this->query('SELECT * FROM core_consecutivos_documentos WHERE core_empresa_id = ? AND core_documento_app_id = ?'.$this->lock(), array($series['company'], $series['type']));
                $this->ensure(count($rows) === 1 && (string)$rows[0]['id'] === (string)$series['counter_id']
                    && (int)$rows[0]['consecutivo_actual'] >= $series['minimum'], 'El contador cambió o es ambiguo.');
            }
            $this->ensure($this->schema() === $plan['schema'], 'El esquema cambió desde el diagnóstico.');
            // Lock exact rows first; no table-wide locks for textual searches.
            foreach ($plan['checks'] as $check) {
                if (!$check['rows']) {
                    continue;
                }
                $ids = array_map(function ($row) { return $row['id']; }, $check['rows']);
                sort($ids, SORT_NUMERIC);
                $this->query('SELECT id FROM '.$this->quote($check['table']).' WHERE id IN ('.$this->marks($ids).') ORDER BY id'.$this->lock(), $ids);
            }
            foreach ($plan['checks'] as $check) {
                // Numeric scopes also protect the old and new identities against inserts.
                $current = $this->query($check['sql'].($check['numeric'] ? $this->lock() : ''), $check['params']);
                $this->ensure($this->canonical($current) === $this->canonical($check['rows']), 'Los datos cambiaron: '.$check['table'].'. Repita el diagnóstico.');
            }
            $this->ensure($this->canonical($this->duplicates()) === $this->canonical($plan['groups']), 'Cambió el conjunto de duplicados.');
            foreach ($plan['changes'] as $change) {
                $set = $params = array();
                foreach ($change['after'] as $column => $value) {
                    if ($value !== $change['before'][$column]) {
                        $set[] = $this->quote($column).' = ?';
                        $params[] = $value;
                    }
                }
                $params[] = $change['id'];
                $stmt = $this->pdo->prepare('UPDATE '.$this->quote($change['table']).' SET '.implode(', ', $set).' WHERE id = ?');
                $stmt->execute($params);
                $this->ensure($stmt->rowCount() === 1, 'Conteo inesperado al actualizar '.$change['table'].' #'.$change['id']);
            }
            $this->verify($plan, true);
            $this->writeJson($directory.'/validated.json', array('status' => 'VALIDATED_BEFORE_COMMIT'));
            $commitAttempted = true;
            $this->pdo->commit();
            $committed = true;
            $this->writeJson($directory.'/result.json', array('status' => 'COMMITTED', 'changes' => count($plan['changes'])));
            // New transaction after commit: do not rely on the original MVCC snapshot.
            $this->pdo->beginTransaction();
            $this->verify($plan, false);
            $this->pdo->rollBack();
            $this->writeJson($directory.'/verified.json', array('status' => 'VERIFIED_AFTER_COMMIT', 'duplicate_groups' => 0));
            return $plan;
        } catch (\Exception $error) {
            $this->abort($error, $directory, $committed, $commitAttempted);
        } catch (\Throwable $error) {
            // Also roll back PHP 7+ engine errors; the first catch supports PHP 5.
            $this->abort($error, $directory, $committed, $commitAttempted);
        }
    }

    private function abort($error, $directory, $committed, $commitAttempted)
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
        try {
            $this->writeJson($directory.'/failure.json', array('commit_state' => $committed ? 'COMMITTED' : ($commitAttempted ? 'UNKNOWN' : 'NOT_COMMITTED'), 'error' => $error->getMessage(),
                'checks' => $this->checks, 'proposed_changes' => array_values($this->changes)));
        } catch (\Exception $reportError) {
            // A full disk must not conceal whether the transaction was committed.
        }
        if ($committed) {
            throw new RuntimeException('Los cambios YA fueron confirmados; falló el registro o la verificación posterior. Revise '.$directory.'. '.$error->getMessage(), 0, $error);
        }
        if ($commitAttempted) {
            throw new RuntimeException('Falló la respuesta del COMMIT; su resultado es incierto. Verifique la base y el respaldo antes de repetir: '.$directory.'. '.$error->getMessage(), 0, $error);
        }
        throw $error;
    }

    private function buildPlan()
    {
        $schema = $this->schema();
        $groups = $this->duplicates();
        $documents = $series = array();
        if ($groups) {
            foreach ($this->required as $table) {
                $this->ensure(isset($this->columns[$table]), 'Falta la tabla necesaria '.$table);
            }
            $numbers = $headerIds = $reserved = array();
            foreach ($groups as $group) {
                $old = (int)$group['consecutivo'];
                $this->ensure($old > 0 && $old + (int)$group['n'] - 1 <= 2147483647, 'Consecutivo inválido o agotado.');
                for ($i = 0; $i < (int)$group['n']; $i++) {
                    $numbers[] = $old + $i;
                }
            }
            $numbers = array_values(array_unique($numbers));
            sort($numbers, SORT_NUMERIC);
            $snap = $this->numericSnapshot($numbers);
            foreach ($groups as $group) {
                $hs = array_values(array_filter($snap['vtas_pos_doc_encabezados'], function ($h) use ($group) {
                    return $this->identity($h, $group);
                }));
                usort($hs, function ($a, $b) {
                    $rankA = $a['estado'] === 'Anulado' ? 1 : 0;
                    $rankB = $b['estado'] === 'Anulado' ? 1 : 0;
                    return $rankA === $rankB ? ((int)$a['id'] - (int)$b['id']) : ($rankA - $rankB);
                });
                $this->ensure(count($hs) === (int)$group['n'], 'Cambió un grupo durante el diagnóstico.');
                foreach ($hs as $i => $h) {
                    $this->headers[(string)$h['id']] = $h;
                    $headerIds[] = $h['id'];
                    if (!$i) {
                        continue;
                    }
                    $new = (int)$group['consecutivo'] + $i;
                    $destination = $h['core_empresa_id'].':'.$h['core_tipo_doc_app_id'].':'.$new;
                    $this->ensure(!isset($reserved[$destination]), 'Dos grupos requieren el mismo destino: '.$destination);
                    $reserved[$destination] = true;
                    $this->assertFree($snap, $h, $new);
                    $documents[] = array('id' => $h['id'], 'kept_id' => $hs[0]['id'], 'company' => $h['core_empresa_id'],
                        'transaction' => $h['core_tipo_transaccion_id'], 'type' => $h['core_tipo_doc_app_id'],
                        'old' => $h['consecutivo'], 'new' => $new, 'total' => $h['valor_total']);
                    $key = $h['core_empresa_id'].':'.$h['core_tipo_doc_app_id'];
                    $counter = $this->query('SELECT * FROM core_consecutivos_documentos WHERE core_empresa_id = ? AND core_documento_app_id = ?', array($h['core_empresa_id'], $h['core_tipo_doc_app_id']));
                    $this->ensure(count($counter) === 1 && (int)$counter[0]['consecutivo_actual'] >= $new, 'El destino supera el contador o este es ambiguo.');
                    $series[$key] = array('company' => $h['core_empresa_id'], 'type' => $h['core_tipo_doc_app_id'],
                        'counter_id' => $counter[0]['id'], 'minimum' => max($new, isset($series[$key]) ? $series[$key]['minimum'] : 0));
                }
            }
            $details = $this->capture('vtas_pos_doc_registros', 'SELECT * FROM vtas_pos_doc_registros WHERE vtas_pos_doc_encabezado_id IN ('.$this->marks($headerIds).') ORDER BY id', $headerIds, true);
            foreach ($groups as $group) {
                $hs = array_values(array_filter($this->headers, function ($h) use ($group) { return $this->identity($h, $group); }));
                $this->reconcileGroup($hs, $snap, $details, $documents);
            }
            $this->checkOtherReferences($snap, $groups, $numbers);
            $this->textReferences($numbers, $documents);
        }
        ksort($series);
        ksort($this->changes);
        return array('database' => $this->database(), 'schema' => $schema, 'groups' => $groups,
            'series' => array_values($series), 'documents' => $documents, 'changes' => array_values($this->changes), 'checks' => $this->checks);
    }

    private function reconcileGroup(array $hs, array $snap, array $details, array $documents)
    {
        $tables = array('vtas_pos_movimientos', 'vtas_movimientos', 'teso_movimientos', 'contab_movimientos', 'cxc_movimientos');
        $assigned = array();
        foreach ($hs as $h) {
            $this->ensure(in_array($h['estado'], array('Contabilizado', 'Anulado'), true), 'Estado no soportado en factura #'.$h['id']);
            $lines = array_values(array_filter($details, function ($r) use ($h) { return (string)$r['vtas_pos_doc_encabezado_id'] === (string)$h['id']; }));
            $this->close($this->sum($lines, 'precio_total'), $h['valor_total'], 'Detalle de factura #'.$h['id']);
            $sets = array();
            foreach ($tables as $table) {
                $sets[$table] = array();
                foreach ($snap[$table] as $row) {
                    if (!$this->identity($row, $h)) {
                        continue;
                    }
                    $matches = array_values(array_filter($hs, function ($candidate) use ($row, $table) { return $this->matches($row, $candidate, $table); }));
                    $this->ensure(count($matches) === 1, 'Referencia ambigua o sin dueño: '.$table.' #'.$row['id']);
                    if ((string)$matches[0]['id'] === (string)$h['id']) {
                        $sets[$table][] = $row;
                        $assigned[$table.':'.$row['id']] = true;
                        $this->owners[$table.':'.$row['id']] = $h['id'];
                    }
                }
            }
            foreach (array('vtas_pos_movimientos', 'vtas_movimientos') as $table) {
                if ($sets[$table] || $h['estado'] === 'Contabilizado') {
                    $this->ensure($this->signature($lines) === $this->signature($sets[$table]), 'Productos/cantidades/importes no coinciden: '.$table.' factura #'.$h['id']);
                }
            }
            $accounting = $sets['contab_movimientos'];
            if ($accounting || $h['estado'] === 'Contabilizado') {
                $this->ensure(count($accounting) > 0, 'Factura contabilizada sin asiento: #'.$h['id']);
                $this->close($this->sum($accounting, 'valor_debito'), -$this->sum($accounting, 'valor_credito'), 'Asiento de factura #'.$h['id']);
                $products = array();
                foreach ($accounting as $row) {
                    if (!empty($row['inv_producto_id'])) {
                        $row['precio_total'] = -(float)$row['valor_credito'];
                        $products[] = $row;
                    }
                }
                $this->ensure($this->signature($lines) === $this->signature($products), 'Productos del asiento no coinciden: #'.$h['id']);
            }
            $this->ensure(in_array($h['forma_pago'], array('contado', 'credito'), true), 'Forma de pago no soportada: #'.$h['id']);
            $abonos = array();
            foreach ($snap['cxc_abonos'] as $row) {
                if ($this->receivableIdentity($row, $h) && (string)$row['core_tercero_id'] === (string)$h['core_tercero_id'] && (string)$row['referencia_tercero_id'] === (string)$h['cliente_id']) {
                    $abonos[] = $row;
                    $this->owners['cxc_abonos:'.$row['id']] = $h['id'];
                    $assigned['cxc_abonos:'.$row['id']] = true;
                }
            }
            if ($h['estado'] === 'Contabilizado' || $sets['teso_movimientos'] || $sets['cxc_movimientos']) {
                $expected = (float)$h['valor_total'] + (float)$h['valor_ajuste_al_peso'] + (float)$h['valor_total_bolsas'];
                if ($h['forma_pago'] === 'contado') {
                    $this->ensure(!$sets['cxc_movimientos'] && !$abonos, 'Venta contado con cartera: #'.$h['id']);
                    $this->close($this->sum($sets['teso_movimientos'], 'valor_movimiento'), $expected, 'Recaudo de factura #'.$h['id']);
                } else {
                    $this->ensure(!$sets['teso_movimientos'] && count($sets['cxc_movimientos']) === 1, 'Cartera ausente o ambigua: #'.$h['id']);
                    $this->close($this->sum($sets['cxc_movimientos'], 'valor_documento'), $expected, 'Cartera de factura #'.$h['id']);
                    $this->close($this->sum($abonos, 'abono'), $this->sum($sets['cxc_movimientos'], 'valor_pagado'), 'Abonos de factura #'.$h['id']);
                }
            } else {
                $this->ensure(!$abonos, 'Factura anulada sin cartera pero con abonos: #'.$h['id']);
            }
            foreach ($documents as $doc) {
                if ((string)$doc['id'] !== (string)$h['id']) {
                    continue;
                }
                $this->change('vtas_pos_doc_encabezados', $h, 'consecutivo', $doc['new']);
                foreach ($sets as $table => $rows) {
                    foreach ($rows as $row) {
                        $this->change($table, $row, 'consecutivo', $doc['new']);
                    }
                }
                foreach ($abonos as $row) {
                    $this->change('cxc_abonos', $row, 'doc_cxc_consecutivo', $doc['new']);
                }
            }
        }
        foreach ($snap['cxc_abonos'] as $row) {
            if ($this->receivableIdentity($row, $hs[0])) {
                $this->ensure(isset($assigned['cxc_abonos:'.$row['id']]), 'Abono sin asignación: #'.$row['id']);
            }
        }
    }

    private function matches(array $row, array $h, $table)
    {
        foreach (array('creado_por', 'fecha', 'core_tercero_id') as $column) {
            if ((string)$row[$column] !== (string)$h[$column]) {
                return false;
            }
        }
        if ($table === 'vtas_pos_movimientos' && ((string)$row['pdv_id'] !== (string)$h['pdv_id'] || (string)$row['cajero_id'] !== (string)$h['cajero_id'])) {
            return false;
        }
        if ($table === 'vtas_movimientos' && (string)$row['remision_doc_encabezado_id'] !== (string)$h['remision_doc_encabezado_id']) {
            return false;
        }
        if ($table === 'vtas_pos_movimientos' && !empty($row['remision_doc_encabezado_id']) && (string)$row['remision_doc_encabezado_id'] !== (string)$h['remision_doc_encabezado_id']) {
            return false;
        }
        if ($table === 'teso_movimientos' && !empty($row['pdv_id']) && (string)$row['pdv_id'] !== (string)$h['pdv_id']) {
            return false;
        }
        return $table !== 'cxc_movimientos' || (string)$row['referencia_tercero_id'] === (string)$h['cliente_id'];
    }

    private function numericSnapshot(array $numbers)
    {
        $snapshot = array();
        foreach ($this->columns as $table => $columns) {
            $conditions = $params = array();
            foreach ($columns as $column => $type) {
                if (strpos($column, 'consecutivo') !== false) {
                    $conditions[] = $this->quote($column).' IN ('.$this->marks($numbers).')';
                    $params = array_merge($params, $numbers);
                }
            }
            if ($conditions) {
                $snapshot[$table] = $this->capture($table, 'SELECT * FROM '.$this->quote($table).' WHERE '.implode(' OR ', $conditions).' ORDER BY id', $params, true);
            }
        }
        return $snapshot;
    }

    private function assertFree(array $snapshot, array $h, $new)
    {
        foreach ($snapshot as $table => $rows) {
            foreach ($rows as $row) {
                $direct = isset($row['core_empresa_id'], $row['core_tipo_doc_app_id'], $row['consecutivo'])
                    && (string)$row['core_empresa_id'] === (string)$h['core_empresa_id']
                    && (string)$row['core_tipo_doc_app_id'] === (string)$h['core_tipo_doc_app_id'] && (string)$row['consecutivo'] === (string)$new;
                $reference = isset($row['core_empresa_id'], $row['doc_cxc_tipo_doc_id'], $row['doc_cxc_consecutivo'])
                    && (string)$row['core_empresa_id'] === (string)$h['core_empresa_id']
                    && (string)$row['doc_cxc_tipo_doc_id'] === (string)$h['core_tipo_doc_app_id']
                    && (string)$row['doc_cxc_consecutivo'] === (string)$new;
                $this->ensure(!$direct && !$reference, 'Destino ocupado: '.$new.' en '.$table.'. Requiere revisión manual.');
            }
        }
    }

    private function checkOtherReferences(array $snapshot, array $groups, array $numbers)
    {
        $supported = array('vtas_pos_doc_encabezados', 'vtas_pos_movimientos', 'vtas_movimientos', 'teso_movimientos', 'contab_movimientos', 'cxc_movimientos');
        foreach ($snapshot as $table => $rows) {
            foreach ($rows as $row) {
                foreach ($groups as $group) {
                    if ($this->identity($row, $group)) {
                        $this->ensure(in_array($table, $supported, true), 'Referencia documental no soportada: '.$table.' #'.$row['id']);
                    }
                }
                foreach ($row as $column => $value) {
                    if (strpos($column, 'consecutivo') === false || $column === 'consecutivo' || $column === 'consecutivo_actual' || ($table === 'cxc_abonos' && $column === 'doc_cxc_consecutivo')) {
                        continue;
                    }
                    foreach ($numbers as $number) {
                        $this->ensure((string)$value !== (string)$number, 'Referencia numérica adicional por revisar: '.$table.'.'.$column.' #'.$row['id']);
                    }
                }
            }
        }
    }

    private function textReferences(array $numbers, array $documents)
    {
        $pattern = '(^|[^0-9])('.implode('|', $numbers).')([^0-9]|$)';
        foreach ($this->headers as $h) {
            $type = $h['core_tipo_doc_app_id'];
            if (!isset($this->prefixes[$type])) {
                $rows = $this->capture('core_tipos_docs_apps', 'SELECT * FROM core_tipos_docs_apps WHERE id = ?', array($type), true);
                $this->ensure(count($rows) === 1 && trim($rows[0]['prefijo']) !== '', 'No se identifica el prefijo documental.');
                $this->prefixes[$type] = $rows[0]['prefijo'];
            }
        }
        foreach ($this->columns as $table => $columns) {
            $conditions = $params = $textColumns = array();
            foreach ($columns as $column => $type) {
                if (preg_match('/char|text|json/i', $type) && preg_match('/descrip|detalle|referenc|document|observ|concept/i', $column)) {
                    $textColumns[] = $column;
                    $conditions[] = $this->quote($column).' REGEXP ?';
                    $params[] = $pattern;
                }
            }
            if (!$conditions) {
                continue;
            }
            $rows = $this->capture($table, 'SELECT * FROM '.$this->quote($table).' WHERE '.implode(' OR ', $conditions).' ORDER BY id', $params, false);
            foreach ($rows as $row) {
                foreach ($textColumns as $column) {
                    if (!preg_match('/'.$pattern.'/', (string)$row[$column])) {
                        continue;
                    }
                    $owner = isset($this->owners[$table.':'.$row['id']]) ? $this->owners[$table.':'.$row['id']] : null;
                    if ($table === 'vtas_pos_doc_encabezados' && isset($this->headers[$row['id']])) {
                        $owner = $row['id'];
                    }
                    if ($table === 'inv_doc_encabezados') {
                        $matches = array_values(array_filter($this->headers, function ($h) use ($row) {
                            return (string)$h['core_empresa_id'] === (string)$row['core_empresa_id'] && (string)$h['remision_doc_encabezado_id'] === (string)$row['id'];
                        }));
                        $this->ensure(count($matches) === 1, 'Referencia textual de inventario sin remisión inequívoca: #'.$row['id']);
                        $owner = $matches[0]['id'];
                    }
                    $this->ensure($owner !== null, 'Referencia textual no soportada: '.$table.'.'.$column.' #'.$row['id']);
                    $h = $this->headers[$owner];
                    $prefix = preg_quote($this->prefixes[$h['core_tipo_doc_app_id']], '/');
                    $regex = '/(?<![\pL\pN_])'.$prefix.'\s+'.preg_quote((string)$h['consecutivo'], '/').'(?![0-9])/u';
                    $this->ensure(preg_match($regex, (string)$row[$column]) === 1, 'Referencia textual no identificada: '.$table.' #'.$row['id']);
                    // Refuse mixed references rather than guessing which document they describe.
                    $remaining = preg_replace($regex, '', (string)$row[$column]);
                    $this->ensure(!preg_match('/'.$pattern.'/', $remaining), 'Referencia textual mixta: '.$table.' #'.$row['id']);
                    foreach ($documents as $doc) {
                        if ((string)$doc['id'] === (string)$owner) {
                            $after = preg_replace_callback($regex, function ($match) use ($doc) {
                                return preg_replace('/[0-9]+$/', (string)$doc['new'], $match[0]);
                            }, (string)$row[$column]);
                            $this->change($table, $row, $column, $after);
                        }
                    }
                }
            }
        }
    }

    private function schema()
    {
        $this->columns = array();
        if ($this->mysql) {
            $tables = $this->query('SELECT TABLE_NAME, ENGINE, TABLE_TYPE FROM information_schema.tables WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME');
            $triggers = $this->query('SELECT TRIGGER_NAME, EVENT_OBJECT_TABLE, ACTION_STATEMENT FROM information_schema.triggers WHERE TRIGGER_SCHEMA = DATABASE() ORDER BY TRIGGER_NAME');
            // Unknown side effects cannot be reconciled by a generic data repair.
            $this->ensure(!$triggers, 'La base contiene triggers; requieren revisión manual antes de conciliar.');
            foreach ($this->query('SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE FROM information_schema.columns WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME, ORDINAL_POSITION') as $row) {
                $this->columns[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row['DATA_TYPE'];
            }
            foreach ($tables as $table) {
                if ($table['TABLE_TYPE'] !== 'BASE TABLE') {
                    unset($this->columns[$table['TABLE_NAME']]);
                    continue;
                }
                $name = $table['TABLE_NAME'];
                if (in_array($name, $this->required, true) || $name === 'inv_doc_encabezados') {
                    $this->ensure($table['ENGINE'] === 'InnoDB', 'La tabla '.$name.' debe usar InnoDB.');
                }
            }
        } else {
            $tables = $this->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name");
            $triggers = $this->query("SELECT name FROM sqlite_master WHERE type = 'trigger'");
            $this->ensure(!$triggers, 'La base contiene triggers.');
            foreach ($tables as $table) {
                foreach ($this->query('PRAGMA table_info('.$this->quote($table['name']).')') as $row) {
                    $this->columns[$table['name']][$row['name']] = $row['type'];
                }
            }
        }
        return array('tables' => $tables, 'columns' => $this->columns, 'triggers' => $triggers);
    }

    private function duplicates()
    {
        if (!isset($this->columns['vtas_pos_doc_encabezados'])) {
            throw new RuntimeException('No existe vtas_pos_doc_encabezados en esta base.');
        }
        return $this->query('SELECT core_empresa_id, core_tipo_transaccion_id, core_tipo_doc_app_id, consecutivo, COUNT(*) AS n FROM vtas_pos_doc_encabezados GROUP BY core_empresa_id, core_tipo_transaccion_id, core_tipo_doc_app_id, consecutivo HAVING COUNT(*) > 1 ORDER BY core_empresa_id, core_tipo_doc_app_id, core_tipo_transaccion_id, consecutivo');
    }

    private function verify(array $plan, $locking)
    {
        foreach ($plan['checks'] as $check) {
            $expected = $check['rows'];
            foreach ($expected as &$row) {
                $key = $check['table'].':'.$row['id'];
                if (isset($this->changes[$key])) {
                    $row = $this->changes[$key]['after'];
                }
            }
            unset($row);
            $current = $this->query($check['sql'].($locking && $check['numeric'] ? $this->lock() : ''), $check['params']);
            $this->ensure($this->canonical($current) === $this->canonical($expected), 'Verificación de filas fallida: '.$check['table']);
        }
        $this->ensure(!$this->duplicates(), 'Aún existen consecutivos POS duplicados.');
    }

    private function change($table, array $row, $column, $value)
    {
        $key = $table.':'.$row['id'];
        if (!isset($this->changes[$key])) {
            $this->changes[$key] = array('table' => $table, 'id' => $row['id'], 'before' => $row, 'after' => $row);
        }
        $this->changes[$key]['after'][$column] = is_string($row[$column]) ? (string)$value : $value;
    }

    private function identity(array $row, array $h)
    {
        foreach (array('core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo') as $column) {
            if (!isset($row[$column]) || (string)$row[$column] !== (string)$h[$column]) {
                return false;
            }
        }
        return true;
    }

    private function receivableIdentity(array $row, array $h)
    {
        foreach (array('core_empresa_id' => 'core_empresa_id', 'doc_cxc_transacc_id' => 'core_tipo_transaccion_id', 'doc_cxc_tipo_doc_id' => 'core_tipo_doc_app_id', 'doc_cxc_consecutivo' => 'consecutivo') as $column => $source) {
            if (!isset($row[$column]) || (string)$row[$column] !== (string)$h[$source]) {
                return false;
            }
        }
        return true;
    }

    private function signature(array $rows)
    {
        $signature = array();
        foreach ($rows as $row) {
            // Stable precision for the historical DOUBLE columns; money is also summed separately.
            $signature[] = $row['inv_producto_id'].'|'.sprintf('%.8F', $row['cantidad']).'|'.sprintf('%.4F', $row['precio_total']);
        }
        sort($signature, SORT_STRING);
        return $signature;
    }

    private function sum(array $rows, $column)
    {
        $sum = 0.0;
        foreach ($rows as $row) {
            $this->ensure(isset($row[$column]) && is_numeric($row[$column]), 'Importe ausente o inválido: '.$column);
            $sum += (float)$row[$column];
        }
        return $sum;
    }

    private function close($a, $b, $context)
    {
        $this->ensure(is_numeric($a) && is_numeric($b) && is_finite((float)$a) && is_finite((float)$b) && abs((float)$a - (float)$b) < 0.01, $context.': los importes no cuadran.');
    }

    private function capture($table, $sql, array $params, $numeric)
    {
        $this->ensure(isset($this->columns[$table]['id']), 'La tabla '.$table.' no tiene ID; requiere revisión manual.');
        $rows = $this->query($sql, $params);
        $this->checks[] = array('table' => $table, 'sql' => $sql, 'params' => $params, 'numeric' => $numeric, 'rows' => $rows);
        return $rows;
    }

    private function query($sql, array $params = array())
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function canonical(array $rows)
    {
        foreach ($rows as &$row) {
            foreach ($row as &$value) {
                if ($value !== null) {
                    $value = (string)$value;
                }
            }
            unset($value);
            ksort($row);
        }
        unset($row);
        usort($rows, function ($a, $b) { return strcmp(json_encode($a), json_encode($b)); });
        return $rows;
    }

    private function database()
    {
        return $this->mysql ? $this->query('SELECT DATABASE() AS name')[0]['name'] : 'sqlite';
    }

    private function lock() { return $this->mysql ? ' FOR UPDATE' : ''; }
    private function marks(array $values) { return implode(',', array_fill(0, count($values), '?')); }
    private function quote($name) { return '`'.str_replace('`', '``', $name).'`'; }
    private function ensure($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }

    private function prepareDirectory($directory)
    {
        $this->ensure(!file_exists($directory), 'El directorio de respaldo ya existe; use uno nuevo.');
        $this->ensure(mkdir($directory, 0700, true), 'No se pudo crear el directorio privado de respaldo.');
        $this->ensure(chmod($directory, 0700), 'No se pudieron restringir los permisos del respaldo.');
    }

    private function writeJson($path, $data)
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $this->ensure($json !== false, 'No se pudo serializar el respaldo: '.json_last_error_msg());
        $handle = fopen($path, 'x');
        $this->ensure($handle !== false, 'No se pudo crear '.$path);
        try {
            $this->ensure(chmod($path, 0600), 'No se pudieron proteger los permisos de '.$path);
            $this->ensure(fwrite($handle, $json) === strlen($json) && fflush($handle), 'No se pudo completar '.$path);
        } finally {
            fclose($handle);
        }
    }
}
