<?php

namespace App\Inventarios\Services;

use App\Inventarios\InvDocRegistro;

use App\Inventarios\InvProducto;

class AverageCost
{
    // COSTO PROMEDIO PONDERADO

    /**
     * $arr_ids_lineas_aceptadas_misma_fecha son los IDs de las lineas que pueden sumar para el calculo del costo promedio (ya han sido recosteadas). No se pueden tener en cuenta aquellas lineas con la misma fecha de la $linea_registro_documento pero que pueden tener un costo errado.
     */
    public function calcular_costo_promedio(InvDocRegistro $linea_registro_documento, $arr_ids_lineas_aceptadas_misma_fecha)
    {
        /**
         * 3> Salida (producto a consumir). Fabricacion
         * 4> Entrada (producto final). Fabricacion
         * 12> 	Inventario Físico: no afectan los movimientos.
         */
        $arr_motivos_no_recosteables_ids = [3, 4, 12];

        // NOTA: Ya el registro del item está agregado en el movimiento

        $array_wheres = [
            ['inv_doc_registros.inv_producto_id','=',$linea_registro_documento->inv_producto_id],
            ['inv_doc_encabezados.fecha', '<', $linea_registro_documento->encabezado_documento->fecha],
            ['inv_doc_registros.estado','<>','Anulado']
        ]; // Fecha menor
        
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1 ) {
            $array_wheres = array_merge($array_wheres, [['inv_bodega_id','=',$linea_registro_documento->inv_bodega_id]]);
        }
        
        $costo_total_movim_anterior = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                                        ->where($array_wheres)
                                        ->whereNotIn('inv_doc_registros.inv_motivo_id',$arr_motivos_no_recosteables_ids)
                                        ->sum('inv_doc_registros.costo_total');
        $cantidad_total_movim_anterior = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                                        ->where($array_wheres)
                                        ->whereNotIn('inv_doc_registros.inv_motivo_id',$arr_motivos_no_recosteables_ids)
                                        ->sum('inv_doc_registros.cantidad');

          
        $array_wheres = [
            ['inv_doc_registros.inv_producto_id','=',$linea_registro_documento->inv_producto_id],
            ['inv_doc_encabezados.fecha', '=', $linea_registro_documento->encabezado_documento->fecha],
            ['inv_doc_registros.estado','<>','Anulado']
        ]; // Fecha igual
        $costo_total_movim_misma_fecha = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                                        ->where($array_wheres)
                                        ->whereNotIn('inv_doc_registros.inv_motivo_id',$arr_motivos_no_recosteables_ids)
                                        ->whereIn('inv_doc_registros.id', $arr_ids_lineas_aceptadas_misma_fecha)
                                        ->sum('inv_doc_registros.costo_total');
        $cantidad_total_movim_misma_fecha = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                                        ->where($array_wheres)
                                        ->whereNotIn('inv_doc_registros.inv_motivo_id',$arr_motivos_no_recosteables_ids)
                                        ->whereIn('inv_doc_registros.id', $arr_ids_lineas_aceptadas_misma_fecha)
                                        ->sum('inv_doc_registros.cantidad');
        
        $cantidad_total_movim = $cantidad_total_movim_anterior + $cantidad_total_movim_misma_fecha;
        // Si la existencia del item estaba en cero. (antes del registro)
        if (($cantidad_total_movim - $linea_registro_documento->cantidad) == 0) {
            return $linea_registro_documento->costo_unitario;
        }
        
        if ($cantidad_total_movim == 0) {
            return $linea_registro_documento->costo_unitario;
        }

        return ($costo_total_movim_anterior + $costo_total_movim_misma_fecha) / $cantidad_total_movim;
    }

    // Almacenar el costo promedio en la tabla de la BD
    public function set_costo_promedio($id_bodega,$id_producto,$costo_prom)
    {        
        $item = InvProducto::find( $id_producto );
        $item->set_costo_promedio( $id_bodega, $costo_prom );
    }

}