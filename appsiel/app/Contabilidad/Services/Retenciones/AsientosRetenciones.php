<?php
namespace App\Contabilidad\Services\Retenciones;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Incluye retenciones contabilizadas manualmente o por nómina sin duplicar el registro auxiliar. */
class AsientosRetenciones
{
    public function obtener($empresaId, array $filtros, ClasificacionRetenciones $clasificador = null)
    {
        if (!Schema::hasTable('contab_movimientos')) { return []; }
        $retenciones = DB::table('contab_retenciones')->get();
        $clasificador = $clasificador ?: new ClasificacionRetenciones($empresaId);
        $cuentas = [];
        $campos = $filtros['naturaleza'] === 'practicada'
            ? ['cta_compras_id', 'cta_compras_devol_id'] : ['cta_ventas_id', 'cta_ventas_devol_id'];
        foreach ($retenciones as $retencion) {
            foreach ($campos as $campo) {
                if (!empty($retencion->$campo) && $clasificador->esCuentaRetencion($retencion->$campo, $filtros['naturaleza'])) {
                    $cuentas[$retencion->$campo][$retencion->id] = $retencion;
                }
            }
        }
        foreach ($clasificador->cuentasIdentificables($filtros['naturaleza']) as $id => $cuenta) {
            if (!isset($cuentas[$id])) { $cuentas[$id] = []; }
        }
        if (!$cuentas) { return []; }
        // Sólo esta compañía y estas cuentas. Un asiento ya soportado por el auxiliar no se vuelve a sumar.
        $movimientos = DB::table('contab_movimientos AS m')->where('m.core_empresa_id', $empresaId)
            ->whereIn('m.contab_cuenta_id', array_keys($cuentas))->whereBetween('m.fecha', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->where(function ($q) { $q->whereNull('m.estado')->orWhere('m.estado', '<>', 'Anulado'); })
            ->whereRaw('NOT EXISTS (SELECT 1 FROM contab_registros_retenciones AS r INNER JOIN contab_retenciones AS ret ON ret.id = r.contab_retencion_id WHERE r.core_empresa_id = m.core_empresa_id AND r.core_tipo_transaccion_id = m.core_tipo_transaccion_id AND r.core_tipo_doc_app_id = m.core_tipo_doc_app_id AND r.consecutivo = m.consecutivo AND r.tipo = ? AND (ret.'.$campos[0].' = m.contab_cuenta_id OR ret.'.$campos[1].' = m.contab_cuenta_id))', [$filtros['naturaleza']])->orderBy('m.fecha')->orderBy('m.id')->get();
        $rows = [];
        $origenes = []; $terceros = []; $documentos = [];
        $trazabilidad = new TrazabilidadRetenciones;
        $prefijos = DB::table('core_tipos_docs_apps')->lists('prefijo', 'id');
        foreach ($movimientos as $m) {
            $clasificacion = $clasificador->porCuenta($m->contab_cuenta_id, $filtros['naturaleza'], substr($m->fecha, 0, 4));
            $esDevolucion = strpos((string)$clasificacion['concepto'], 'devolucion_') === 0;
            // Pagar la obligación a la DIAN no es una nueva retención ni una devolución.
            $valor = $filtros['naturaleza'] === 'practicada' ? -(float)$m->valor_credito : (float)$m->valor_debito;
            if ($esDevolucion) {
                $valor = $filtros['naturaleza'] === 'practicada' ? -(float)$m->valor_debito : (float)$m->valor_credito;
            }
            if (abs($valor) < 0.005) { continue; }
            $claveDocumento = json_encode([$m->core_tipo_transaccion_id, $m->core_tipo_doc_app_id, $m->consecutivo]);
            // Nómina contiene varios beneficiarios en el mismo documento.
            $claveDoc = $claveDocumento.'|'.$m->core_tercero_id;
            if (!array_key_exists($claveDoc, $origenes)) {
                $datos = (array)$m;
                // El tercero del asiento puede ser la DIAN. Se obtiene el beneficiario del encabezado.
                $origenes[$claveDoc] = $trazabilidad->resolver($datos);
            }
            $origen = $origenes[$claveDoc];
            if ($origen) {
                if (!array_key_exists($claveDocumento, $documentos)) {
                    $documentos[$claveDocumento] = DB::table($origen['documento_tabla'])->where('id', $origen['documento_id'])->first();
                }
                $doc = $documentos[$claveDocumento];
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
            $retencion = count($posibles) === 1 ? reset($posibles) : null;
            $cuenta = $clasificador->cuenta($m->contab_cuenta_id);
            $tipoDoc = isset($prefijos[$m->core_tipo_doc_app_id]) ? $prefijos[$m->core_tipo_doc_app_id] : '';
            $rows[] = (object)array_merge((array)$m, $origen, [
                'id' => 'asiento-'.$m->id, 'fecha' => $m->fecha, 'contab_retencion_id' => $retencion ? $retencion->id : 0,
                'tipo' => $filtros['naturaleza'], 'valor' => $valor, 'valor_base_retencion' => 0,
                'tasa_retencion' => null,
                'retencion' => $retencion ? $retencion->descripcion : ($cuenta ? $cuenta->codigo.' '.$cuenta->descripcion : 'Cuenta de retenciones '.$m->contab_cuenta_id),
                'tercero' => $tercero ? $tercero->descripcion : '', 'numero_identificacion' => $tercero ? $tercero->numero_identificacion : '',
                'digito_verificacion' => $tercero ? $tercero->digito_verificacion : '', 'persona_actual' => $tercero ? $tercero->tipo : '',
                'tipo_impuesto' => $clasificacion['tipo_impuesto'],
                'concepto' => $clasificacion['concepto'],
                'codigo_municipio' => $clasificacion['codigo_municipio'],
                'actividad_ica' => $clasificacion['actividad_ica'],
                'numero_certificado' => '', 'prefijo' => $tipoDoc, 'base_desconocida' => true,
                'origen_asiento' => true, 'clasificacion_origen' => $clasificacion['clasificacion_origen']
            ]);
        }
        return $rows;
    }
}
