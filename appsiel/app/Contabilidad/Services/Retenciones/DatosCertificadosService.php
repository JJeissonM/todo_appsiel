<?php
namespace App\Contabilidad\Services\Retenciones;

use Illuminate\Support\Facades\DB;

/** Agrupa documentos una sola vez y sus retenciones por concepto y tarifa, en centavos. */
class DatosCertificadosService
{
    public function generar($empresaId, array $f)
    {
        $empresa = DB::table('core_empresas')->where('id', $empresaId)->first();
        if (!$empresa) { throw new \InvalidArgumentException('Empresa no disponible.'); }
        $terceros = DB::table('core_terceros')->where('core_empresa_id', $empresaId);
        if ($f['tercero_id']) { $terceros->where('id', $f['tercero_id']); }
        if ($f['grupo_id']) {
            $terceros->whereRaw('EXISTS (SELECT 1 FROM compras_proveedores p WHERE p.core_tercero_id=core_terceros.id AND p.clase_proveedor_id=?)', [$f['grupo_id']]);
        }
        $proveedores = [];
        foreach ($terceros->get() as $t) { $proveedores[$t->id] = (array)$t; }
        if (!$proveedores) { return []; }
        $compras = DB::table('compras_doc_encabezados AS d')
            ->leftJoin('core_tipos_docs_apps AS td', 'td.id', '=', 'd.core_tipo_doc_app_id')
            ->where('d.core_empresa_id', $empresaId)->where('d.estado', 'Activo')
            ->whereIn('d.core_tipo_transaccion_id', [25, 48, 36])
            ->whereBetween('d.fecha', [$f['fecha_desde'], $f['fecha_hasta']])
            // Sólo operaciones efectivamente contabilizadas, no borradores ni importaciones pendientes.
            ->whereRaw('EXISTS (SELECT 1 FROM contab_movimientos m WHERE m.core_empresa_id=? AND m.core_tipo_transaccion_id=d.core_tipo_transaccion_id AND m.core_tipo_doc_app_id=d.core_tipo_doc_app_id AND m.consecutivo=d.consecutivo AND (m.estado IS NULL OR m.estado<>?))', [$empresaId, 'Anulado'])
            ->select('d.*', 'td.prefijo')->orderBy('d.fecha')->orderBy('d.id');
        if ($f['tercero_id']) { $compras->where('d.core_tercero_id', $f['tercero_id']); }
        $compras=$compras->get();
        // Certificar sólo auxiliares con base y tarifa. Los asientos suplementarios del reporte
        // no acreditan esos datos y pueden repetir auxiliares si cambió el catálogo de cuentas.
        $reporte = (new RetencionesReportService)->generar($empresaId, array_merge($f, ['naturaleza'=>'practicada', 'tipo_impuesto'=>'', 'codigo_municipio'=>'']), false);
        $retenciones = [];
        foreach ($reporte['detalle'] as $r) {
            if ($r->documento_tabla !== 'compras_doc_encabezados' || !isset($proveedores[$r->core_tercero_id])) { continue; }
            if ($r->tipo_impuesto !== $f['tipo_impuesto'] && $r->tipo_impuesto !== 'SIN_CLASIFICAR') { continue; }
            if ($f['tipo_impuesto'] === 'ICA' && $r->codigo_municipio && $r->codigo_municipio !== $f['codigo_municipio']) { continue; }
            $retenciones[$r->documento_id][] = $r;
        }
        $certificados = [];
        foreach ($compras as $d) {
            if (!isset($proveedores[$d->core_tercero_id])) { continue; }
            $filas = isset($retenciones[$d->id]) ? $retenciones[$d->id] : [];
            if ($f['compras'] === 'retenidas' && !$filas) { continue; }
            $anio = substr($d->fecha, 0, 4);
            $key = $d->core_tercero_id.'|'.$anio;
            if (!isset($certificados[$key])) {
                $certificados[$key] = ['empresa'=>(array)$empresa, 'tercero'=>$proveedores[$d->core_tercero_id],
                    'filtros'=>$f, 'anio'=>$anio, 'desde'=>max($f['fecha_desde'], $anio.'-01-01'),
                    'hasta'=>min($f['fecha_hasta'], $anio.'-12-31'), 'compra_total'=>0, 'base_total'=>0,
                    'retencion_total'=>0, 'tasas'=>[], 'documentos'=>[], 'bloqueos'=>[], 'avisos'=>[]];
            }
            $c = &$certificados[$key];
            $signo = (int)$d->core_tipo_transaccion_id === 36 ? -1 : 1;
            $total = $signo * abs((int)round($d->valor_total * 100));
            $c['compra_total'] += $total;
            $c['documentos'][] = ['id'=>$d->id, 'fecha'=>$d->fecha, 'documento'=>$d->prefijo.' '.$d->consecutivo,
                'factura'=>trim($d->doc_proveedor_prefijo.' '.$d->doc_proveedor_consecutivo),
                'concepto'=>$d->descripcion, 'total'=>$total, 'con_retencion'=>(bool)$filas];
            foreach ($filas as $r) {
                if ($r->tipo_impuesto === 'SIN_CLASIFICAR') { $c['bloqueos'][] = 'Clasifique el impuesto de la retención '.$r->retencion.' antes de certificar.'; continue; }
                if ($r->tasa_retencion === null || $r->valor_base_retencion === null || !empty($r->base_desconocida)
                    || (abs((float)$r->valor) >= 0.005 && (abs((float)$r->valor_base_retencion) < 0.005 || (float)$r->tasa_retencion <= 0))) {
                    $c['bloqueos'][] = 'Hay retenciones sin base o tarifa verificable.';
                }
                // El certificado exige el concepto del pago, no una casilla del formulario 350.
                if (!$r->concepto && !trim((string)$r->retencion)) { $c['bloqueos'][] = 'Debe completar el concepto de la retención.'; }
                if ($f['tipo_impuesto'] === 'ICA' && !$r->codigo_municipio) {
                    if (!$f['confirmar_municipio']) { $c['bloqueos'][] = 'Confirme la jurisdicción de las retenciones sin municipio registrado.'; }
                    $c['avisos'][] = 'Municipio de retenciones históricas asignado según la selección del responsable.';
                }
                $concepto = $r->concepto ? $r->concepto_nombre.': '.$r->retencion : $r->retencion;
                $k = $concepto.'|'.$r->tasa_retencion.'|'.$r->actividad_ica;
                if (!isset($c['tasas'][$k])) { $c['tasas'][$k] = ['concepto'=>$concepto, 'tarifa'=>$r->tasa_retencion,
                    'actividad_ica'=>$r->actividad_ica, 'base'=>0, 'retencion'=>0]; }
                $base = $signo < 0 ? -abs($r->base_centavos) : $r->base_centavos;
                $valor = $signo < 0 ? -abs($r->retencion_centavos) : $r->retencion_centavos;
                $c['tasas'][$k]['base'] += $base;
                $c['tasas'][$k]['retencion'] += $valor;
                $c['base_total'] += $base;
                $c['retencion_total'] += $valor;
            }
            unset($c);
        }
        foreach ($certificados as &$c) {
            if (empty($empresa->direccion1) || empty($empresa->numero_identificacion) || empty($empresa->descripcion)) {
                $c['bloqueos'][] = 'Complete la identificación y dirección de la empresa.';
            }
            if (empty($c['tercero']['numero_identificacion']) || empty($c['tercero']['descripcion'])) { $c['bloqueos'][] = 'Complete la identificación del proveedor.'; }
            if ($f['tipo_impuesto'] === 'FUENTE' && $f['firma_fuente'] === 'computador' && ConceptosDeclaracion::persona(isset($empresa->tipo) ? $empresa->tipo : '') !== 'juridica') {
                $c['bloqueos'][] = 'La emisión sin firma autógrafa de Retefuente sólo aplica a personas jurídicas. Seleccione firma manual.';
            }
            ksort($c['tasas']);
            $c['tasas'] = array_values($c['tasas']);
            $c['bloqueos'] = array_values(array_unique($c['bloqueos']));
            $c['avisos'] = array_values(array_unique($c['avisos']));
        }
        unset($c);
        usort($certificados, function ($a, $b) { return strcmp(str_slug($a['tercero']['descripcion']).$a['anio'], str_slug($b['tercero']['descripcion']).$b['anio']); });
        return array_values($certificados);
    }
}
