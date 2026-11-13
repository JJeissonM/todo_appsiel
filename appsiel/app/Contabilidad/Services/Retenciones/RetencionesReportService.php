<?php

namespace App\Contabilidad\Services\Retenciones;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetencionesReportService
{
    public function generar($empresaId, array $filtros)
    {
        $clasificador = new ClasificacionRetenciones($empresaId);
        $query = DB::table('contab_registros_retenciones AS r')
            ->leftJoin('contab_retenciones AS ret', 'ret.id', '=', 'r.contab_retencion_id')
            ->leftJoin('core_terceros AS t', function ($join) {
                $join->on('t.id', '=', 'r.core_tercero_id')->on('t.core_empresa_id', '=', 'r.core_empresa_id');
            })
            ->leftJoin('core_tipos_docs_apps AS td', 'td.id', '=', 'r.core_tipo_doc_app_id')
            ->where('r.core_empresa_id', $empresaId)->where('r.estado', 'Activo')
            ->whereBetween('r.fecha', [$filtros['fecha_desde'], $filtros['fecha_hasta']])
            ->where('r.tipo', $filtros['naturaleza']);

        // El estado del documento prevalece aunque un proceso histórico no haya anulado su retención.
        foreach (TrazabilidadRetenciones::tablasDocumentos() as $i => $tabla) {
            if (!Schema::hasTable($tabla)) { continue; }
            $alias = 'doc'.$i;
            $query->where(function ($q) use ($tabla, $alias, $empresaId) {
                $q->whereNull('r.documento_id')->orWhere('r.documento_tabla', '<>', $tabla)
                    ->orWhereRaw('EXISTS (SELECT 1 FROM '.$tabla.' AS '.$alias.' WHERE '.$alias.'.id = r.documento_id AND '.$alias.'.core_empresa_id = ? AND '.$alias.'.estado <> ?)', [$empresaId, 'Anulado']);
            });
        }
        foreach (['retencion_id' => 'r.contab_retencion_id', 'tercero_id' => 'r.core_tercero_id'] as $filtro => $campo) {
            if (!empty($filtros[$filtro])) { $query->where($campo, $filtros[$filtro]); }
        }
        if (isset($filtros['tarifa']) && $filtros['tarifa'] !== '') { $query->where('r.tasa_retencion', $filtros['tarifa']); }
        $rows = $query->select('r.*', 'ret.descripcion AS retencion', 't.tipo AS persona_actual',
            't.numero_identificacion', 't.digito_verificacion', 't.descripcion AS tercero',
            'td.prefijo')
            ->orderBy('r.fecha')->orderBy('r.id')->get();

        $rows = array_merge($rows, (new AsientosRetenciones)->obtener($empresaId, $filtros, $clasificador));

        $detalle = []; $resumen = []; $terceros = []; $pendientes = []; $sinClasificar = 0; $casillas = []; $totalesDeclaracion = [];
        foreach ($rows as $row) {
            if (empty($row->origen_asiento)) {
                $clasificacion = $clasificador->porRetencion($row->contab_retencion_id, $filtros['naturaleza'], substr($row->fecha, 0, 4));
                foreach ($clasificacion as $campo => $valor) { $row->$campo = $valor; }
            }
            if (!empty($filtros['codigo_municipio']) && $row->codigo_municipio !== $filtros['codigo_municipio']) { continue; }
            if (!empty($filtros['concepto']) && $row->concepto !== $filtros['concepto']) { continue; }
            if (!empty($row->origen_asiento)) {
                if (!empty($filtros['retencion_id']) && (int)$row->contab_retencion_id !== (int)$filtros['retencion_id']) { continue; }
                if (!empty($filtros['tercero_id']) && (int)$row->core_tercero_id !== (int)$filtros['tercero_id']) { continue; }
                if (isset($filtros['tarifa']) && $filtros['tarifa'] !== '' && ($row->tasa_retencion === null || (float)$row->tasa_retencion !== (float)$filtros['tarifa'])) { continue; }
            }
            $row->tipo_impuesto = $row->tipo_impuesto ?: 'SIN_CLASIFICAR';
            if ($row->tipo_impuesto === 'SIN_CLASIFICAR') { $sinClasificar++; }
            if (!empty($filtros['tipo_impuesto']) && $row->tipo_impuesto !== $filtros['tipo_impuesto']) { continue; }
            $row->persona = $row->tipo_persona_retenida ?: ConceptosDeclaracion::persona($row->persona_actual);
            if (!empty($filtros['tipo_persona']) && $row->persona !== $filtros['tipo_persona']) { continue; }
            $row->anio = (int)substr($row->fecha, 0, 4);
            $row->periodo = substr($row->fecha, 0, 7);
            $row->concepto_nombre = isset(ConceptosDeclaracion::opciones()[$row->concepto])
                ? ConceptosDeclaracion::opciones()[$row->concepto] : 'Sin clasificar';
            list($row->casilla_base, $row->casilla_retencion) = ConceptosDeclaracion::casillas($row->concepto, $row->persona, $row->anio);
            if ($filtros['naturaleza'] !== 'practicada') { $row->casilla_base = null; $row->casilla_retencion = null; }
            $row->observaciones = [];
            if (!empty($row->clasificacion_origen) && $row->clasificacion_origen !== 'configuracion') {
                $row->observaciones[] = 'Clasificación de categoría/cuenta; validar en la configuración fiscal anual';
            }
            if (!empty($row->base_desconocida)) { $row->observaciones[] = 'Asiento sin auxiliar: base sujeta y tarifa pendientes de conciliación'; }
            if (strpos((string)$row->concepto, 'exterior_') === 0) {
                $row->observaciones[] = 'Completar anexo de pagos al exterior: país, beneficiario y convenio';
            }
            if (!$row->documento_id) { $row->observaciones[] = 'Documento de origen pendiente'; }
            if (!$row->tercero) { $row->observaciones[] = 'Tercero sin identificar'; }
            if ($row->persona === 'sin_clasificar') { $row->observaciones[] = 'Naturaleza del tercero pendiente'; }
            if ($row->tipo_impuesto === 'ICA') {
                if (!$row->codigo_municipio) { $row->observaciones[] = 'Municipio de la retención pendiente'; }
                if (!$row->actividad_ica) { $row->observaciones[] = 'Actividad ICA pendiente'; }
                $row->casilla_base = null; $row->casilla_retencion = null;
            } elseif (!$row->casilla_retencion && $filtros['naturaleza'] === 'practicada') {
                $row->observaciones[] = $row->anio !== 2026 ? 'Estructura del formulario sin validar para este año' : 'Concepto fiscal pendiente';
            }
            $row->base_centavos = (int)round((float)$row->valor_base_retencion * 100);
            $row->retencion_centavos = (int)round((float)$row->valor * 100);
            if (strpos((string)$row->concepto, 'devolucion_') === 0) {
                $row->retencion_centavos = -abs($row->retencion_centavos);
                $row->base_centavos = -abs($row->base_centavos);
            }
            if ($row->retencion_centavos < 0 && strpos((string)$row->concepto, 'devolucion_') !== 0) {
                $row->observaciones[] = 'Revisar imputación de devolución o ajuste';
            }
            if ($row->observaciones) { $pendientes[] = $row; }
            $detalle[] = $row;
            foreach (['casilla_base' => $row->base_centavos, 'casilla_retencion' => $row->retencion_centavos] as $campo => $valor) {
                if (!$row->$campo) { continue; }
                $key = $row->periodo.'|'.$row->$campo;
                if (!isset($casillas[$key])) {
                    $casillas[$key] = (object)['periodo' => $row->periodo, 'casilla' => $row->$campo,
                        'concepto' => $campo === 'casilla_base' ? 'Base: '.$row->concepto_nombre : $row->concepto_nombre,
                        'centavos' => 0];
                }
                $casillas[$key]->centavos += strpos((string)$row->concepto, 'devolucion_') === 0 ? abs($valor) : $valor;
            }
            if ($row->casilla_retencion && in_array($row->tipo_impuesto, ['FUENTE', 'IVA'])) {
                $casillaTotal = $row->tipo_impuesto === 'FUENTE' ? 130 : 134;
                $keyTotal = $row->periodo.'|'.$casillaTotal;
                if (!isset($totalesDeclaracion[$keyTotal])) {
                    $totalesDeclaracion[$keyTotal] = (object)['periodo' => $row->periodo, 'casilla' => $casillaTotal,
                        'concepto' => $row->tipo_impuesto === 'FUENTE' ? 'Total retenciones renta clasificadas' : 'Total retenciones IVA clasificadas', 'centavos' => 0];
                }
                $totalesDeclaracion[$keyTotal]->centavos += $row->retencion_centavos;
            }
            $clave = json_encode([$row->periodo, $row->tipo_impuesto, $row->concepto, $row->persona,
                $row->codigo_municipio, $row->actividad_ica]);
            $this->acumular($resumen, $clave, $row);
            $this->acumular($terceros, json_encode([$clave, $row->core_tercero_id, $row->tasa_retencion]), $row);
        }
        $casillas = array_merge($casillas, $totalesDeclaracion);
        return [
            'detalle' => collect($detalle), 'resumen' => collect(array_values($resumen)),
            'terceros' => collect(array_values($terceros)), 'pendientes' => collect($pendientes),
            'sin_clasificar' => $sinClasificar, 'casillas' => collect(array_values($casillas))->sortBy(function ($casilla) { return $casilla->periodo.'|'.str_pad($casilla->casilla, 3, '0', STR_PAD_LEFT); }),
            'base_total' => array_sum(array_map(function ($r) { return $r->base_centavos; }, $detalle)),
            'retencion_total' => array_sum(array_map(function ($r) { return $r->retencion_centavos; }, $detalle)),
            'filtros' => $filtros
        ];
    }

    private function acumular(&$grupos, $clave, $row)
    {
        if (!isset($grupos[$clave])) {
            $grupos[$clave] = clone $row;
            $grupos[$clave]->base_centavos = 0;
            $grupos[$clave]->retencion_centavos = 0;
            $grupos[$clave]->cantidad = 0;
        }
        $grupos[$clave]->base_centavos += $row->base_centavos;
        $grupos[$clave]->retencion_centavos += $row->retencion_centavos;
        $grupos[$clave]->cantidad++;
        $grupos[$clave]->observaciones = array_unique(array_merge($grupos[$clave]->observaciones, $row->observaciones));
    }
}
