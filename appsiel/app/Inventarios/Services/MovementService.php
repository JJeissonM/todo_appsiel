<?php

namespace App\Inventarios\Services;

use App\Core\Transactions\TransactionDocument;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMovimiento;

class MovementService
{
    public function get_movement_with_item_relations($array_wheres)
    {
        return InvMovimiento::where($array_wheres)
                        ->select('inv_movimientos.*')
                        ->orderBy('fecha')
                        ->get();
    }

    

    public function movements_by_purpose($init_date,$end_date,$transaction_type_id,$purpose_id)
    {
        $array_wheres = [
            ['inv_movimientos.fecha','>=',$init_date],
            ['inv_movimientos.fecha','<=',$end_date]
        ];

        if ( $purpose_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_movimientos.inv_motivo_id' => $purpose_id] );
        }

        $arr_purposes_id = InvMotivo::where([
            ['core_tipo_transaccion_id','=',$transaction_type_id]
        ])->get()->pluck('id')->toArray();

        return InvMovimiento::where($array_wheres)
                        ->whereIn('inv_movimientos.inv_motivo_id',$arr_purposes_id)
                        ->select('inv_movimientos.*')
                        ->orderBy('fecha')
                        ->get();
    }
}
