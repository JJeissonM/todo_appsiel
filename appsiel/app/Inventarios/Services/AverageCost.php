<?php

namespace App\Inventarios\Services;

use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use Illuminate\Support\Facades\DB;

class AverageCost
{
    // COSTO PROMEDIO PONDERADO

    /**
     * $arr_ids_lineas_aceptadas_misma_fecha son los IDs de las lineas que pueden sumar para el calculo del costo promedio (ya han sido recosteadas). No se pueden tener en cuenta aquellas lineas con la misma fecha de la $linea_registro_documento['ero'] que pueden tener un costo errado.
     */
    public function calcular_costo_promedio(array $linea_registro_documento, $costo_promedio_actual)
    {
        /**
         * inv_motivo_id de entradas que traen un costo "externo" (No calculado por el sistema)
         * 1> Entrada Almacen
         * 11> Compras Nacionales
         * 16> Entrada por compras
         * 23> Saldos iniciales
         */
        $arr_motivos_entradas_ids = [1, 11, 16, 23];
        
        // Fecha menor
        $datos = $this->costo_y_cantidad_fecha_antes_de_la_entrada($linea_registro_documento['inv_bodega_id'], $linea_registro_documento['inv_producto_id'], $linea_registro_documento['fecha']);
        
        // Fecha igual
        $array_wheres2 = [
            ['inv_doc_registros.inv_producto_id','=',$linea_registro_documento['inv_producto_id']],
            ['inv_doc_encabezados.fecha', '=', $linea_registro_documento['fecha']],
            ['inv_doc_registros.estado','<>','Anulado']
        ]; 
        
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1 ) {
            $array_wheres2 = array_merge($array_wheres2, [['inv_bodega_id','=',$linea_registro_documento['inv_bodega_id']]]);
        }

        $entradas_del_dia = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                        ->where( $array_wheres2 )
                        ->whereIn('inv_doc_registros.inv_motivo_id',$arr_motivos_entradas_ids)
                        ->select('inv_doc_registros.*')
                        ->orderBy('inv_doc_encabezados.fecha')
                        ->get();

        $costo_total_entradas_del_dia = $entradas_del_dia->sum('costo_total');

        $cantidad_total_entradas_del_dia = $entradas_del_dia->sum('cantidad');
        
        if (round($datos->total_cantidad_anterior,0) <= 0) {
            if ($cantidad_total_entradas_del_dia != 0) {
                return (object)[
                    'auditoria' => [
                        'entradas_del_dia' => $entradas_del_dia,
                        'costo_total_entradas_del_dia' => $costo_total_entradas_del_dia,
                        'cantidad_total_entradas_del_dia' => $cantidad_total_entradas_del_dia,
                        'return_devuelto' => 1,
                        'formula_calculo' => 'costo_total_entradas_del_dia / cantidad_total_entradas_del_dia'
                    ],
                    'costo_prom' => $costo_total_entradas_del_dia / $cantidad_total_entradas_del_dia
                ];
            }

            return (object)[
                'auditoria' => [
                    'costo_y_cantidad_fecha_antes_de_la_entrada' => $datos,
                    'entradas_del_dia' => $entradas_del_dia,
                    'costo_total_entradas_del_dia' => $costo_total_entradas_del_dia,
                    'cantidad_total_entradas_del_dia' => $cantidad_total_entradas_del_dia,
                    'return_devuelto' => 2,
                    'formula_calculo' => 'linea_registro_documento[costo_unitario]'
                ],
                'costo_prom' => $linea_registro_documento['costo_unitario']
            ];
        }
        
        $cantidad_total_movim = $datos->total_cantidad_anterior + $cantidad_total_entradas_del_dia;
        
        if (round($cantidad_total_movim,0) <= 0) {
            if ($cantidad_total_entradas_del_dia != 0) {
                return (object)[
                    'auditoria' => [
                        'entradas_del_dia' => $entradas_del_dia,
                        'costo_total_entradas_del_dia' => $costo_total_entradas_del_dia,
                        'cantidad_total_entradas_del_dia' => $cantidad_total_entradas_del_dia,
                        'datos->total_cantidad_anterior' => $datos->total_cantidad_anterior,
                        'cantidad_total_movim' => $cantidad_total_movim,
                        'return_devuelto' => 3,
                        'formula_calculo' => 'costo_total_entradas_del_dia / cantidad_total_entradas_del_dia'
                    ],
                    'costo_prom' => $costo_total_entradas_del_dia / $cantidad_total_entradas_del_dia
                ];
            }
            
            return (object)[
                'auditoria' => [
                    'costo_y_cantidad_fecha_antes_de_la_entrada' => $datos,
                    'entradas_del_dia' => $entradas_del_dia,
                    'costo_total_entradas_del_dia' => $costo_total_entradas_del_dia,
                    'cantidad_total_entradas_del_dia' => $cantidad_total_entradas_del_dia,
                    'datos->total_cantidad_anterior' => $datos->total_cantidad_anterior,
                    'cantidad_total_movim' => $cantidad_total_movim,
                    'return_devuelto' => 4,
                    'formula_calculo' => 'linea_registro_documento[costo_unitario]'
                ],
                'costo_prom' => $linea_registro_documento['costo_unitario']
            ];
        }

        return (object)[
            'auditoria' => [
                'costo_y_cantidad_fecha_antes_de_la_entrada' => $datos,
                'entradas_del_dia' => $entradas_del_dia,
                'costo_total_entradas_del_dia' => $costo_total_entradas_del_dia,
                'cantidad_total_entradas_del_dia' => $cantidad_total_entradas_del_dia,
                'datos->total_cantidad_anterior' => $datos->total_cantidad_anterior,
                'datos->total_costo_anterior' => $datos->total_costo_anterior,
                'cantidad_total_movim' => $cantidad_total_movim,
                'return_devuelto' => 5,
                'formula_calculo' => '(datos->total_costo_anterior + costo_total_entradas_del_dia) / cantidad_total_movim'
            ],
            'costo_prom' => ($datos->total_costo_anterior + $costo_total_entradas_del_dia) / $cantidad_total_movim
        ];
    }

    /**
     * 
     */
    public function costo_y_cantidad_fecha_antes_de_la_entrada($id_bodega, $id_producto, $fecha_transaccion)
    {
        /**
         * 3> Salida (producto a consumir). Fabricacion
         * 4> Entrada (producto final). Fabricacion
         * 12> 	Inventario Físico: no afectan los movimientos.
         * 9> Entrada bodega destino. Transferencia (entrada)
         * 2> Transferencia. Transferencia (salida)
         */
        $arr_motivos_ids_no_afectan_costo_promedio = [12];//[3, 4, 12, 9, 2];

        $array_wheres = [
            ['inv_movimientos.inv_producto_id','=',$id_producto],
            ['inv_movimientos.fecha', '<', $fecha_transaccion]
        ];

        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1 ) {
            $array_wheres = array_merge( $array_wheres, [ ['inv_movimientos.inv_bodega_id','=',$id_bodega] ] );
        }

        $datos = InvMovimiento::where( $array_wheres )->get();

        return (object)[
            'total_costo_anterior' => $datos->sum('costo_total'),
            'total_cantidad_anterior' => $datos->sum('cantidad_total')
        ];
    }

    // Almacenar el costo promedio en la tabla de la BD
    public function set_costo_promedio($id_bodega,$id_producto,$costo_prom)
    {        
        $item = InvProducto::find( $id_producto );
        $item->set_costo_promedio( $id_bodega, $costo_prom );
    }

}