<?php 

namespace App\Inventarios\Services;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecosteoService
{
    /**
     * inv_motivo_id de entradas que traen un costo "externo" (No calculado por el sistema)
     * 1> Entrada Almacen
     * 11> Compras Nacionales
     * 16> Entrada por compras
     * 23> Saldos iniciales
     */
    public $arr_motivos_entradas_ids = [1, 11, 16, 22, 23];

    /**
     * Todos los ids de entradas y ademas
     * 3> Salida (producto a consumir). Fabricacion
     * 4> Entrada (producto final). Fabricacion
     * 12> 	Inventario Físico: no afectan los movimientos.
     * Nota: esta variable tambien sta en la clase AverageCost.
     */
    public $arr_motivos_ids_no_recosteables = [1, 11, 16, 23, 3, 4, 12];

	public function recostear( $operador1, $item_id, $fecha_desde, $fecha_hasta, $recontabilizar_contabilizar_movimientos )
	{
        $inv_bodega_id = 0;
        $user_email = Auth::user()->email;

        $costo_promedio_actual = $this->calcular_costo_promedio_ultima_entrada($fecha_desde, $item_id);

        if ( $costo_promedio_actual == 0 || $costo_promedio_actual == null ) {
            return (object)[
                'status'=>'mensaje_error',
                'message' => 'La Fecha desde debe empezar un día donde haya alguna entrada de mercancía.']
                ;
        }

        $costo_prom_serv = new AverageCost();

        $registros_de_entradas = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                        ->whereBetween( 'inv_doc_encabezados.fecha', [ $fecha_desde, $fecha_hasta] )
                        ->where('inv_doc_registros.inv_producto_id', $operador1, $item_id)
                        ->whereIn('inv_doc_registros.inv_motivo_id',$this->arr_motivos_entradas_ids)
                        ->select('inv_doc_registros.*','inv_doc_encabezados.fecha','inv_doc_encabezados.created_at')
                        ->orderBy('inv_doc_encabezados.fecha')
                        ->get()
                        ->toArray();

        $arr_fechas = [];
        $cant_lineas = count($registros_de_entradas);
        for ($i=0; $i < $cant_lineas; $i++)
        {            
            // Se calcula el costo promedio
            $obj_costo_promedio_actual = $costo_prom_serv->calcular_costo_promedio( $registros_de_entradas[$i], $costo_promedio_actual );

            if (isset($registros_de_entradas[$i + 1])) {
                $fecha_siguiente = $registros_de_entradas[$i + 1]['fecha'];
                $operador_fecha_siguiente = '<';
            }else{
                $fecha_siguiente = $fecha_hasta;
                $operador_fecha_siguiente = '<=';
            }

            $arr_fechas[] = [
                'auditoria' => $obj_costo_promedio_actual->auditoria,
                'costo_promedio_actual' => $obj_costo_promedio_actual->costo_prom,
                'actual' => '>=' . 'que ' .$registros_de_entradas[$i]['fecha'],
                'sig' => $operador_fecha_siguiente . ' que ' . $fecha_siguiente
            ];

            $array_wheres = [
                [ 'inv_doc_encabezados.fecha', '>=', $registros_de_entradas[$i]['fecha'] ],
                [ 'inv_doc_encabezados.fecha',$operador_fecha_siguiente, $fecha_siguiente ],
                [ 'inv_doc_registros.inv_producto_id', $operador1, $item_id ]
            ];

            // Actualizar todos los costos de los items hasta antes del siguiente registro de entrada
            // 1ro. El costo unitario
            InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
            ->where( $array_wheres )
            ->whereNotIn('inv_doc_registros.inv_motivo_id',$this->arr_motivos_ids_no_recosteables)
            ->toBase()
            ->update(
                [
                    'inv_doc_registros.costo_unitario' => $obj_costo_promedio_actual->costo_prom,
                    'inv_doc_registros.updated_at' => date('Y-m-d H:i:s'),
                    'inv_doc_registros.modificado_por' => $user_email
                ]
            );
            // 2do. El costo total -  ya cambio el costo_unitario
            InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                    ->where( $array_wheres )
                    ->whereNotIn('inv_doc_registros.inv_motivo_id',$this->arr_motivos_ids_no_recosteables)
                    ->toBase()
                    ->update(
                        [
                            'inv_doc_registros.costo_total' => DB::raw('inv_doc_registros.costo_unitario * inv_doc_registros.cantidad'),
                            'inv_doc_registros.updated_at' => date('Y-m-d H:i:s'),
                            'inv_doc_registros.modificado_por' => $user_email
                        ]
                    );
    
            // Se actualiza el movimiento de inventario
            $array_wheres = [
                [ 'inv_doc_encabezados.fecha', '>=', $registros_de_entradas[$i]['fecha'] ],
                [ 'inv_doc_encabezados.fecha',$operador_fecha_siguiente, $fecha_siguiente ],
                [ 'inv_movimientos.inv_producto_id', $operador1, $item_id ]
            ];
            // 1ro. El costo unitario
            InvMovimiento::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
            ->where( $array_wheres )
            ->whereNotIn('inv_movimientos.inv_motivo_id',$this->arr_motivos_ids_no_recosteables)
            ->toBase()
            ->update(
                [
                    'inv_movimientos.costo_unitario' => $obj_costo_promedio_actual->costo_prom,
                    'inv_movimientos.updated_at' => date('Y-m-d H:i:s'),
                    'inv_movimientos.modificado_por' => $user_email
                ]
            );
            // 2do. El costo total -  ya cambio el costo_unitario
            InvMovimiento::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                    ->where( $array_wheres )
                    ->whereNotIn('inv_movimientos.inv_motivo_id',$this->arr_motivos_ids_no_recosteables)
                    ->toBase()
                    ->update(
                        [
                            'inv_movimientos.costo_total' => DB::raw('inv_movimientos.costo_unitario * inv_movimientos.cantidad'),
                            'inv_movimientos.updated_at' => date('Y-m-d H:i:s'),
                            'inv_movimientos.modificado_por' => $user_email
                        ]
                    );         
            
            if ($recontabilizar_contabilizar_movimientos == 1) {

                // 2 = Transferencia, 3 = Salida de almacén, 24 = Remisión de ventas, 28 = Ajuste de iventarios, 34 = Devolución en ventas
                $arr_ids_transacciones_recosteables = [2, 3, 24, 28, 34];

                // Se actualiza el registro contable para la transacción de esa línea de registro (DB y CR)
                $array_wheres = [
                    [ 'fecha', '>=', $registros_de_entradas[$i]['fecha'] ],
                    [ 'fecha',$operador_fecha_siguiente, $fecha_siguiente ],
                    [ 'inv_producto_id', $operador1, $item_id ]
                ];

                ContabMovimiento::where( $array_wheres )
                            ->whereIn('core_tipo_transaccion_id', $arr_ids_transacciones_recosteables)
                            ->where('valor_credito', 0 )
                            ->update( [ 
                                'valor_debito' => abs( $obj_costo_promedio_actual->costo_prom ), 'valor_saldo' => abs( $obj_costo_promedio_actual->costo_prom ),
                                'modificado_por' => $user_email
                            ] );

                ContabMovimiento::where( $array_wheres )
                            ->whereIn('core_tipo_transaccion_id', $arr_ids_transacciones_recosteables)
                            ->where('valor_debito', 0 )
                            ->update( [ 
                                'valor_credito' => (abs( $obj_costo_promedio_actual->costo_prom ) * -1), 'valor_saldo' => (abs( $obj_costo_promedio_actual->costo_prom ) * -1),
                                'modificado_por' => $user_email
                            ] );
            }
        }

        // Se actualiza el costo prom. de Item
        $item = InvProducto::find($item_id);
        $item->set_costo_promedio( $inv_bodega_id, $obj_costo_promedio_actual->costo_prom);

        $num_reg_contab = ' junto con sus registros contables.';
        if ($recontabilizar_contabilizar_movimientos == 0) {
            $num_reg_contab = ', pero ningun registro contable';
        }
        
        dd('Se actualizaron las líneas de registros de inventarios'. $num_reg_contab, $arr_fechas);

        return (object)[
            'status'=>'flash_message',
            'message' => 'Se actualizaron las líneas de registros de inventarios'. $num_reg_contab]
            ;
	}

    public function actualizar_costo_una_linea_registro($linea_registro, $costo_promedio_actual,$recontabilizar_contabilizar_movimientos)
    {
        $user_email = Auth::user()->email;
        $encabezado_documento = $linea_registro->encabezado_documento;
        $costo_total = $linea_registro->cantidad * $costo_promedio_actual;

        // Se actualiza el costo_unitario y costo_total en cada línea de registro del documento
        $linea_registro->costo_unitario = $costo_promedio_actual;
        $linea_registro->costo_total = $costo_total;
        $linea_registro->modificado_por = $user_email;
        $linea_registro->save();

        $array_wheres = [
                    ['core_tipo_transaccion_id', '=', $encabezado_documento->core_tipo_transaccion_id],
                    ['core_tipo_doc_app_id', '=', $encabezado_documento->core_tipo_doc_app_id],
                    ['consecutivo', '=', $encabezado_documento->consecutivo],
                    ['inv_bodega_id', '=', $linea_registro->inv_bodega_id],
                    ['inv_producto_id', '=', $linea_registro->inv_producto_id],
                    ['cantidad', '=', $linea_registro->cantidad],
        ];

        // Se actualiza el movimiento de inventario
        InvMovimiento::where( $array_wheres )
                    ->update( [ 
                        'costo_unitario' => $costo_promedio_actual,
                        'costo_total' => $costo_total,
                        'modificado_por' => $user_email
                    ] );

        if ($recontabilizar_contabilizar_movimientos == 0) {
            return 0;
        }

        // Se actualiza el registro contable para la transacción de esa línea de registro (DB y CR)
        ContabMovimiento::where( $array_wheres )
                        ->where('valor_credito', 0 )
                        ->update( [ 
                            'valor_debito' => abs( $costo_total ), 'valor_saldo' => abs( $costo_total ),
                            'modificado_por' => $user_email
                        ] );

        ContabMovimiento::where( $array_wheres )
                        ->where('valor_debito', 0 )
                        ->update( [ 
                            'valor_credito' => (abs( $costo_total ) * -1), 'valor_saldo' => (abs( $costo_total ) * -1),
                            'modificado_por' => $user_email
                        ] );
    }

    public function calcular_costo_promedio_ultima_entrada($fecha_desde, $item_id)
    {
        // Pueden haber varias entradas el mismo dia
        $ultimas_entradas = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                        ->whereIn('inv_doc_registros.inv_motivo_id',$this->arr_motivos_entradas_ids)
                        ->where([
                            ['inv_doc_registros.inv_producto_id', '=', $item_id],
                            ['inv_doc_encabezados.fecha', '=', $fecha_desde]
                        ])
                        ->select('inv_doc_registros.*')
                        ->orderBy('inv_doc_encabezados.fecha')
                        ->get();

        $cantidad = $ultimas_entradas->sum('cantidad');

        if ($cantidad == 0) {
            return 0;
        }

        return $ultimas_entradas->sum('costo_total') / $cantidad;
    }
}