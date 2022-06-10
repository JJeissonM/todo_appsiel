<?php

namespace App\Inventarios\Services;

use App\Inventarios\InvProducto;
use App\Inventarios\InvMovimiento;

class StockAmountService
{
    public function get_stock_amount_item($inv_bodega_id, $inv_producto_id, $deadline_date)
    {
        $array_wheres = [
            ['inv_bodega_id', '=', $inv_bodega_id],
            ['inv_producto_id', '=', $inv_producto_id],
            ['fecha', '<=', $deadline_date]
        ];

        $cantidad = InvMovimiento::where($array_wheres)
            ->sum('cantidad');

        if ($cantidad == null) {
            return 0;
        }

        return $cantidad;
    }


    public function get_total_cost_amount_item($inv_bodega_id, $inv_producto_id, $deadline_date)
    {
        $array_wheres = [
            ['inv_bodega_id', '=', $inv_bodega_id],
            ['inv_producto_id', '=', $inv_producto_id],
            ['fecha', '<=', $deadline_date]
        ];

        $costo_total = InvMovimiento::where($array_wheres)
            ->sum('costo_total');

        if ($costo_total == null) {
            return 0;
        }

        return $costo_total;
    }

    public function validate_stock_document_lines($lines, $warehouse_id, $fecha)
    {
        if (config('appsiel.inventories.allow_negative_stock')) {
            return (object)[
                'status' => 'success',
                'list_faileds' => ''
            ];
        }

        $lines = $this->prepare_lines($lines, $warehouse_id, $fecha);

        $status = 'success';
        $list_faileds = '';
        foreach ($lines as $line) {
            $difference = $line['actual_stock_amount'] - $line['cantidad'];
            if ($difference < 0) {
                $item = InvProducto::find($line['inv_producto_id']);

                $list_faileds .=  'InvProducto: ' . $item->get_value_to_show() . ', Diferencia: ' . $difference . ', Fecha: ' . $line['fecha'] . '<br> ';

                $status = 'error';
            }
        }

        return (object)[
            'status' => $status,
            'list_faileds' => $list_faileds
        ];
    }

    public function prepare_lines($lines, $warehouse_id, $fecha)
    {
        $arr_lines = [];
        foreach ($lines as $line) {

            $last_date = $this->get_date_last_movement($warehouse_id, $line['inv_producto_id']);

            if ($last_date != null) {
                $fecha = $last_date;
            }

            $actual_stock_amount = $this->get_stock_amount_item($warehouse_id, $line['inv_producto_id'], $fecha);

            $arr_lines[] = [
                'inv_producto_id' => $line['inv_producto_id'],
                'actual_stock_amount' => $actual_stock_amount,
                'cantidad' => $line['cantidad'],
                'fecha' => $fecha,
            ];
        }
        return $arr_lines;
    }

    public function get_date_last_movement($warehouse_id, $inv_producto_id)
    {
        $movement_line = InvMovimiento::where([
            ['inv_bodega_id', '=', $warehouse_id],
            ['inv_producto_id', '=', $inv_producto_id]
        ])
            ->orderBy('fecha')
            ->get()
            ->last();

        if ($movement_line == null) {
            return null;
        }

        return $movement_line->fecha;
    }
}
