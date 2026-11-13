<?php
namespace App\Contabilidad\Services\Retenciones;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Incluye retenciones contabilizadas manualmente o por nómina sin duplicar el registro auxiliar. */
class AsientosRetenciones
{
    public function obtener($empresaId, array $filtros)
    {
        if (!Schema::hasTable('contab_movimientos')) { return []; }
        $retenciones = DB::table('contab_retenciones')->get();
        $configuraciones = DB::table('contab_retenciones_reporte_config')->where('core_empresa_id', $empresaId)->get();
        $config = [];
        foreach ($configuraciones as $c) { $config[$c->anio][$c->contab_retencion_id] = $c; }
        $cuentas = [];
        $campos = $filtros['naturaleza'] === 'practicada'
            ? ['cta_compras_id', 'cta_compras_devol_id'] : ['cta_ventas_id', 'cta_ventas_devol_id'];
        foreach ($retenciones as $retencion) {
            foreach ($campos as $campo) {
                if (!empty($retencion->$campo)) { $cuentas[$retencion->$campo][$retencion->id] = $retencion; }
            }
        }
        if (!$cuentas) { return []; }
        // Sólo esta compañía y estas cuentas. Un asiento ya soportado por el auxiliar no se vuelve a sumar.
        $movimientos = DB::table('contab_movimientos AS m')->where('m.core_empresa_id', $empresaId)
            ->whereIn('m.contab_cuenta_id', array_keys($cuentas))->whereBetween('m.fecha', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->where(function ($q) { $q->whereNull('m.estado')->orWhere('m.estado', '<>', 'Anulado'); })
            ->whereRaw('NOT EXISTS (SELECT 1 FROM contab_registros_retenciones AS r INNER JOIN contab_retenciones AS ret ON ret.id = r.contab_retencion_id WHERE r.core_empresa_id = m.core_empresa_id AND r.core_tipo_transaccion_id = m.core_tipo_transaccion_id AND r.core_tipo_doc_app_id = m.core_tipo_doc_app_id AND r.consecutivo = m.consecutivo AND r.tipo = ? AND (ret.'.$campos[0].' = m.contab_cuenta_id OR ret.'.$campos[1].' = m.contab_cuenta_id))', [$filtros['naturaleza']])->orderBy('m.fecha')->orderBy('m.id')->get();
        $rows = [];
        $origenes = []; $terceros = [];
        foreach ($movimientos as $m) {
            $valor = (float)$m->valor_debito + (float)$m->valor_credito;
            if ($filtros['naturaleza'] === 'practicada') { $valor *= -1; }
            if (abs($valor) < 0.005) { continue; }
            $claveDoc = json_encode([$m->core_tipo_transaccion_id, $m->core_tipo_doc_app_id, $m->consecutivo]);
            if (!array_key_exists($claveDoc, $origenes)) {
                $datos = (array)$m;
                // El tercero del asiento puede ser la DIAN. Se obtiene el beneficiario del encabezado.
                $origenes[$claveDoc] = (new TrazabilidadRetenciones)->resolver($datos);
            }
            $origen = $origenes[$claveDoc];
            if ($origen) {
                $doc = DB::table($origen['documento_tabla'])->where('id', $origen['documento_id'])->first();
                if (!$doc || $doc->estado === 'Anulado') { continue; }
            } else {
                // Conservar el importe contable sin atribuirlo a un tercero no confirmado.
                $origen = ['core_tercero_id' => null, 'tipo_persona_retenida' => 'sin_clasificar',
                    'documento_id' => null, 'documento_tabla' => null];
            }
            if (!array_key_exists($origen['core_tercero_id'], $terceros)) {
                $terceros[$origen['core_tercero_id']] = DB::table('core_terceros')->where('id', $origen['core_tercero_id'])->where('core_empresa_id', $empresaId)->first();
            }
            $tercero = $terceros[$origen['core_tercero_id']];
            $posibles = $cuentas[$m->contab_cuenta_id];
            $clasificaciones = [];
            foreach ($posibles as $ret) {
                $c = isset($config[substr($m->fecha, 0, 4)][$ret->id]) ? $config[substr($m->fecha, 0, 4)][$ret->id] : null;
                $clasificaciones[json_encode($c ? [$c->tipo_impuesto, $c->concepto, $c->codigo_municipio, $c->actividad_ica] : null)] = $c;
            }
            $clasificacion = count($clasificaciones) === 1 ? reset($clasificaciones) : null;
            $retencion = count($posibles) === 1 ? reset($posibles) : null;
            $tipoDoc = DB::table('core_tipos_docs_apps')->where('id', $m->core_tipo_doc_app_id)->value('prefijo');
            $rows[] = (object)array_merge((array)$m, $origen, [
                'id' => 'asiento-'.$m->id, 'fecha' => $m->fecha, 'contab_retencion_id' => $retencion ? $retencion->id : 0,
                'tipo' => $filtros['naturaleza'], 'valor' => $valor, 'valor_base_retencion' => 0,
                'tasa_retencion' => null,
                'retencion' => $retencion ? $retencion->descripcion : 'Cuenta de retenciones '.$m->contab_cuenta_id,
                'tercero' => $tercero ? $tercero->descripcion : '', 'numero_identificacion' => $tercero ? $tercero->numero_identificacion : '',
                'digito_verificacion' => $tercero ? $tercero->digito_verificacion : '', 'persona_actual' => $tercero ? $tercero->tipo : '',
                'tipo_impuesto' => $clasificacion ? $clasificacion->tipo_impuesto : null,
                'concepto' => $clasificacion ? $clasificacion->concepto : null,
                'codigo_municipio' => $clasificacion ? $clasificacion->codigo_municipio : null,
                'actividad_ica' => $clasificacion ? $clasificacion->actividad_ica : null,
                'numero_certificado' => '', 'prefijo' => $tipoDoc, 'base_desconocida' => true,
                'origen_asiento' => true
            ]);
        }
        return $rows;
    }
}
