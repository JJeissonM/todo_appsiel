<?php

namespace App\Ventas\Services;

class ProfitabilityReportService
{
    public static function opciones_campo_select()
    {
        $options = ['' => 'Todos'];
        foreach (\App\VentasPos\Pdv::where('core_empresa_id', \Illuminate\Support\Facades\Auth::user()->empresa_id)
            ->orderBy('descripcion')->get() as $pdv) {
            $options[$pdv->id] = $pdv->descripcion;
        }
        return $options;
    }

    public static function filterByPdv($query, $pdvId)
    {
        // Match the entire document identity; EXISTS avoids multiplying sales rows.
        return $query->whereRaw('EXISTS (SELECT 1 FROM vtas_pos_doc_encabezados AS rentabilidad_pdv
            WHERE rentabilidad_pdv.pdv_id = ?
            AND rentabilidad_pdv.core_empresa_id = vtas_movimientos.core_empresa_id
            AND rentabilidad_pdv.core_tipo_transaccion_id = vtas_movimientos.core_tipo_transaccion_id
            AND rentabilidad_pdv.core_tipo_doc_app_id = vtas_movimientos.core_tipo_doc_app_id
            AND rentabilidad_pdv.consecutivo = vtas_movimientos.consecutivo)', [$pdvId]);
    }

    public static function sortRows(array $rows, $order = null)
    {
        if ($order === 'id' || $order === 'alfabetico') {
            usort($rows, function ($a, $b) use ($order) {
                if ($order === 'alfabetico') {
                    $nameA = \Illuminate\Support\Str::ascii(mb_strtolower($a['orden_nombre'], 'UTF-8'));
                    $nameB = \Illuminate\Support\Str::ascii(mb_strtolower($b['orden_nombre'], 'UTF-8'));
                    $result = strcmp($nameA, $nameB);
                    if ($result !== 0) {
                        return $result;
                    }
                }
                if ($a['orden_id'] == $b['orden_id']) {
                    return 0;
                }
                return $a['orden_id'] < $b['orden_id'] ? -1 : 1;
            });
        } else {
            // Keep the original tie-breaking behavior when no new order is chosen.
            foreach ($rows as &$row) {
                unset($row['orden_id'], $row['orden_nombre']);
            }
            unset($row);
            $prices = array_column($rows, 'precio');
            array_multisort($prices, SORT_DESC, $rows);
        }
        return $rows;
    }
}
