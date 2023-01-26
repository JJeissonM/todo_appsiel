<?php 

namespace App\Inventarios\Services;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\Services\TallaItem;
use Illuminate\Support\Facades\Auth;
use League\Fractal\Resource\Item;

class RecosteoService
{
    /**
     * inv_motivo_id
     * 1> Entrada Almacen
     * 9> Entrada bodega destino (en Transferencias)
     * 11> Compras Nacionales 
     * 13> Entrada (+) Sobrante 
     * 16> Entrada por compras
     * 23> Saldos iniciales
     */
    public $arr_motivos_entradas_ids = [1, 9, 11, 13, 16, 23];

    /**
     * 3> Salida (producto a consumir). Fabricacion
     * 4> Entrada (producto final). Fabricacion
     */
    public $arr_motivos_no_recosteables_ids = [3, 4];

	public function recostear( $operador1, $item_id, $fecha_desde, $fecha_hasta, $modo_recosteo, $tener_en_cuenta_movimientos_anteriores, $recontabilizar_contabilizar_movimientos )
	{
        $i = 1;
        $inv_bodega_id = 0;
        
        $item = InvProducto::find($item_id);

        $costo_promedio_actual = $this->calcular_costo_promedio_ultima_entrada($fecha_desde, $item_id, $tener_en_cuenta_movimientos_anteriores, []);

        if ( $costo_promedio_actual == 0 || $costo_promedio_actual == null ) {
            return (object)[
                'status'=>'mensaje_error',
                'message' => 'La Fecha desde debe empezar un día donde haya alguna entrada de mercancía.']
                ;
        }

        $costo_prom_serv = new AverageCost();

        $registros_sin_filtro = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                        ->whereBetween( 'inv_doc_encabezados.fecha', [ $fecha_desde, $fecha_hasta] )
                        ->where('inv_doc_registros.inv_producto_id', $operador1, $item_id)
                        ->select('inv_doc_registros.*','inv_doc_encabezados.fecha')
                        ->orderBy('inv_doc_encabezados.fecha')
                        ->orderBy('inv_doc_registros.cantidad')
                        ->get();

        $arr_ids_movim_recosteados = [];
        foreach ($registros_sin_filtro as $linea_registro)
        {
            // No se recostean los Ensambles
            if (in_array($linea_registro->motivo->id, $this->arr_motivos_no_recosteables_ids)) {
                continue;
            }

            // Se cambia el costo promedio
            if (in_array($linea_registro->motivo->id, $this->arr_motivos_entradas_ids) && $modo_recosteo == 'recalcular_costo_promedio') {
                $arr_ids_movim_recosteados[] = $linea_registro->id;

                $costo_promedio_actual = $costo_prom_serv->calcular_costo_promedio($linea_registro,$arr_ids_movim_recosteados);

                continue; // No se recostean arr_motivos_entradas_ids
            }

            $this->actualizar_costo_una_linea_registro($linea_registro, $costo_promedio_actual,$recontabilizar_contabilizar_movimientos);

            $arr_ids_movim_recosteados[] = $linea_registro->id;
            
            $i++;
        }

        // Se actualiza el costo prom. de Item
        $item->set_costo_promedio( $inv_bodega_id, $costo_promedio_actual);

        $num_reg_contab = ($i-1) * 2;
        if (!$recontabilizar_contabilizar_movimientos) {
            $num_reg_contab = 0;
        }
            
        return (object)[
            'status'=>'flash_message',
            'message' => 'Se actualizaron '.($i-1).' líneas de registros de inventarios,<br> y '. $num_reg_contab .' registros contables.']
            ;
	}

    public function actualizar_costo_una_linea_registro($linea_registro, $costo_promedio_actual,$recontabilizar_contabilizar_movimientos)
    {            
        $encabezado_documento = $linea_registro->encabezado_documento;
        $costo_total = $linea_registro->cantidad * $costo_promedio_actual;

        // Se actualiza el costo_unitario y costo_total en cada línea de registro del documento
        $linea_registro->costo_unitario = $costo_promedio_actual;
        $linea_registro->costo_total = $costo_total;
        $linea_registro->save();

        // Se actualiza el movimiento de inventario
        InvMovimiento::where('core_tipo_transaccion_id', $encabezado_documento->core_tipo_transaccion_id )
                    ->where('core_tipo_doc_app_id', $encabezado_documento->core_tipo_doc_app_id )
                    ->where('consecutivo', $encabezado_documento->consecutivo )
                    ->where('inv_bodega_id', $linea_registro->inv_bodega_id )
                    ->where('inv_producto_id', $linea_registro->inv_producto_id )
                    ->where('cantidad', $linea_registro->cantidad )
                    ->update( [ 
                        'costo_unitario' => $costo_promedio_actual, 'costo_total' => $costo_total,
                        'modificado_por' => Auth::user()->email
                    ] );

        if (!$recontabilizar_contabilizar_movimientos) {
            return 0;
        }

        // Se actualiza el registro contable para la transacción de esa línea de registro (DB y CR)
        ContabMovimiento::where('core_tipo_transaccion_id', $encabezado_documento->core_tipo_transaccion_id )
                        ->where('core_tipo_doc_app_id', $encabezado_documento->core_tipo_doc_app_id )
                        ->where('consecutivo', $encabezado_documento->consecutivo )
                        ->where('inv_bodega_id', $linea_registro->inv_bodega_id )
                        ->where('inv_producto_id', $linea_registro->inv_producto_id )
                        ->where('cantidad', $linea_registro->cantidad )
                        ->where('valor_credito', 0 )
                        ->update( [ 
                            'valor_debito' => abs( $costo_total ), 'valor_saldo' => abs( $costo_total ),
                            'modificado_por' => Auth::user()->email
                        ] );

        ContabMovimiento::where('core_tipo_transaccion_id', $encabezado_documento->core_tipo_transaccion_id )
                        ->where('core_tipo_doc_app_id', $encabezado_documento->core_tipo_doc_app_id )
                        ->where('consecutivo', $encabezado_documento->consecutivo )
                        ->where('inv_bodega_id', $linea_registro->inv_bodega_id )
                        ->where('inv_producto_id', $linea_registro->inv_producto_id )
                        ->where('cantidad', $linea_registro->cantidad )
                        ->where('valor_debito', 0 )
                        ->update( [ 
                            'valor_credito' => (abs( $costo_total ) * -1), 'valor_saldo' => (abs( $costo_total ) * -1),
                            'modificado_por' => Auth::user()->email
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