<?php 

namespace App\Inventarios\Services;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\Services\TallaItem;

class RecosteoService
{
	public function recostear( $operador1, $item_id, $fecha_desde, $fecha_hasta )
	{                                    

        $i = 1;
        // Los registros de cada documento con motivo diferente a Entradas que afectan costo
        /**
         * inv_motivo_id
         * 1> Entrada Almacen
         * 11> Compras Nacionales 
         * 23> Saldos iniciales
         */
        $registros = InvDocRegistro::join('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_doc_registros.inv_doc_encabezado_id')
                        ->whereBetween( 'inv_doc_encabezados.fecha', [ $fecha_desde, $fecha_hasta] )
                        ->whereNotIn('inv_doc_registros.inv_motivo_id',[1,11,23])
                        ->where('inv_doc_registros.inv_producto_id', $operador1, $item_id)
                        ->select('inv_doc_registros.*')
                        ->orderBy('inv_doc_encabezados.fecha')
                        ->get();

        foreach ($registros as $linea_registro)
        {
            $encabezado_documento = $linea_registro->encabezado_documento;
            
            $costo_total_acumulado_item = InvMovimiento::where('fecha','<=',$encabezado_documento->fecha)
            ->whereIn('inv_motivo_id',[1,11,23])
            ->sum('costo_total');
            
            $cantidad_acumulada_item = InvMovimiento::where('fecha','<=',$encabezado_documento->fecha)
            ->whereIn('inv_motivo_id',[1,11,23])
            ->sum('cantidad');

            if ($cantidad_acumulada_item == 0) {
                return (object)[
                    'status'=>'error',
                    'message' => 'No se actualizó ningún registro. No hay movimientos de entradas de mercancía anteriores a la fecha '.$encabezado_documento->fecha.' para obtener obtener el costo promedio. Revisar movimiento hacia atrás del documento ' . $encabezado_documento->tipo_documento_app->prefijo . ' ' . $encabezado_documento->consecutivo];
            }

            $costo_promedio = $costo_total_acumulado_item / $cantidad_acumulada_item;
            
            $costo_total = $linea_registro->cantidad * $costo_promedio;

            dd($encabezado_documento->tipo_documento_app->prefijo . ' ' . $encabezado_documento->consecutivo,$costo_total_acumulado_item, $cantidad_acumulada_item, $costo_total, $linea_registro->cantidad, $costo_promedio);

            // Se actualiza el costo_unitario y costo_total en cada línea de registro del documento
            $linea_registro->costo_unitario = $costo_promedio;
            $linea_registro->costo_total = $costo_total;
            $linea_registro->save();

            // Se actualiza el movimiento de inventario
            InvMovimiento::where('core_tipo_transaccion_id', $encabezado_documento->core_tipo_transaccion_id )
                        ->where('core_tipo_doc_app_id', $encabezado_documento->core_tipo_doc_app_id )
                        ->where('consecutivo', $encabezado_documento->consecutivo )
                        ->where('inv_bodega_id', $linea_registro->inv_bodega_id )
                        ->where('inv_producto_id', $linea_registro->inv_producto_id )
                        ->where('cantidad', $linea_registro->cantidad )
                        ->update( [ 'costo_unitario' => $costo_promedio, 'costo_total' => $costo_total  ] );


            // Se actualiza el registro contable para la transacción de esa línea de registro (DB y CR)
            ContabMovimiento::where('core_tipo_transaccion_id', $encabezado_documento->core_tipo_transaccion_id )
                            ->where('core_tipo_doc_app_id', $encabezado_documento->core_tipo_doc_app_id )
                            ->where('consecutivo', $encabezado_documento->consecutivo )
                            ->where('inv_bodega_id', $linea_registro->inv_bodega_id )
                            ->where('inv_producto_id', $linea_registro->inv_producto_id )
                            ->where('cantidad', $linea_registro->cantidad )
                            ->where('valor_credito', 0 )
                            ->update( [ 'valor_debito' => abs( $costo_total ), 'valor_saldo' => abs( $costo_total ) ] );

            ContabMovimiento::where('core_tipo_transaccion_id', $encabezado_documento->core_tipo_transaccion_id )
                            ->where('core_tipo_doc_app_id', $encabezado_documento->core_tipo_doc_app_id )
                            ->where('consecutivo', $encabezado_documento->consecutivo )
                            ->where('inv_bodega_id', $linea_registro->inv_bodega_id )
                            ->where('inv_producto_id', $linea_registro->inv_producto_id )
                            ->where('cantidad', $linea_registro->cantidad )
                            ->where('valor_debito', 0 )
                            ->update( [ 'valor_credito' => (abs( $costo_total ) * -1), 'valor_saldo' => (abs( $costo_total ) * -1) ] );

            $i++;
        } 
            
        return (object)[
            'status'=>'success',
            'message' => 'Se actualizaron '.($i-1).' líneas de registros de inventarios,<br> y '.(($i-1) * 2).' registros contables.']
            ;
	}
}