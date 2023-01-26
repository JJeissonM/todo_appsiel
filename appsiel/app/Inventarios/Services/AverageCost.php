<?php

namespace App\Inventarios\Services;

use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;

class AverageCost
{
    // COSTO PROMEDIO PONDERADO
    public function calcular_costo_promedio(InvDocRegistro $linea_registro_documento, $arr_ids_lineas_recosteadas)
    {
        /**
         * 3> Salida (producto a consumir). Fabricacion
         * 4> Entrada (producto final). Fabricacion
         */
        $arr_motivos_no_recosteables_ids = [3, 4];

        // NOTA: Ya el registro del item está agregado en el movimiento

        $array_wheres = [
            ['inv_doc_registros.inv_producto_id','=',$linea_registro_documento->inv_producto_id],
            ['inv_doc_encabezados.fecha', '<=', $linea_registro_documento->encabezado_documento->fecha]
        ];
        
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1 ) {
            $array_wheres = array_merge($array_wheres, [['inv_bodega_id','=',$linea_registro_documento->inv_bodega_id]]);
        }
        
        $costo_total_movim = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                                        ->where($array_wheres)
                                        ->whereNotIn('inv_doc_registros.inv_motivo_id',$arr_motivos_no_recosteables_ids)
                                        ->whereIn('inv_doc_registros.id', $arr_ids_lineas_recosteadas)
                                        ->sum('inv_doc_registros.costo_total');
        $cantidad_total_movim = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                                        ->where($array_wheres)
                                        ->whereNotIn('inv_doc_registros.inv_motivo_id',$arr_motivos_no_recosteables_ids)
                                        ->whereIn('inv_doc_registros.id', $arr_ids_lineas_recosteadas)
                                        ->sum('inv_doc_registros.cantidad');

        // Si la existencia del item estaba en cero. (antes del registro)
        if (($cantidad_total_movim - $linea_registro_documento->cantidad) == 0) {
            return $linea_registro_documento->costo_unitario;
        }
        
        if ($cantidad_total_movim == 0) {
            return $linea_registro_documento->costo_unitario;
        }

        return $costo_total_movim / $cantidad_total_movim;
    }

    // Almacenar el costo promedio en la tabla de la BD
    public function set_costo_promedio($id_bodega,$id_producto,$costo_prom)
    {        
        $item = InvProducto::find( $id_producto );
        $item->set_costo_promedio( $id_bodega, $costo_prom );
    }

}