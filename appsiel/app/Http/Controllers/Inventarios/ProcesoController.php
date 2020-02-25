<?php

namespace App\Http\Controllers\Inventarios;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Contabilidad\ContabilidadController;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvCostoPromProducto;

use App\Ventas\VtasMovimiento;
use App\Compras\ComprasMovimiento;
use App\Contabilidad\ContabMovimiento;

use Input;

class ProcesoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function recontabilizar_un_documento( $documento_id )
    {
        ProcesoController::recontabilizar_documento($documento_id);
        return redirect( 'inventarios/'.$documento_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( 'flash_message', 'Documento Recontabilizado.' );
    }

    public static function recontabilizar_documento( $documento_id )
    {
        $documento = InvDocEncabezado::find( $documento_id );

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id',$documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$documento->core_tipo_doc_app_id)
                        ->where('consecutivo',$documento->consecutivo)
                        ->delete();        

        // Obtener líneas de registros del documento
        $registros_documento = InvDocRegistro::where( 'inv_doc_encabezado_id', $documento->id )->get();

        foreach ($registros_documento as $linea)
        {
            $motivo = InvMotivo::find( $linea->inv_motivo_id );

            $detalle_operacion = 'Recontabilizado. '.$linea->descripcion;

            // Si el movimiento es de ENTRADA de inventarios, se DEBITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'entrada')
            {
                // Inventarios (DB)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, abs($linea->costo_total), 0);
                
                // Cta. Contrapartida (CR)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, 0, abs($linea->costo_total) );
            }

            // Si el movimiento es de SALIDA de inventarios, se ACREDITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'salida')
            {
                // Inventarios (CR)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, 0, abs($linea->costo_total));
                
                // Cta. Contrapartida (DB)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, abs($linea->costo_total), 0 );
            }
                
        }
    }


    /*
     * RECONTABILIZACION TODOS LOS DOCUMENTOS
     */
    public function recontabilizar_documentos_inventarios()
    {
        $fecha_desde = Input::get('fecha_desde');//'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta');//'2019-10-28';

        if ( is_null( $fecha_desde ) || is_null( $fecha_hasta) )
        {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> recontabilizar_documentos_inventarios?fecha_desde=2019-10-28&fecha_hasta=2019-10-28';
            dd('Operación cancelada.');
        }

        // Obtener TODOS los documentos entre las fechas indicadas
        $documentos = InvDocEncabezado::where('estado','<>','Anulado')
                                        ->whereBetween( 'fecha', [ $fecha_desde, $fecha_hasta] )
                                        ->get();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            ProcesoController::recontabilizar_documento( $un_documento->id );
            echo $i.'  ';
            $i++;            
        }

        echo '<br>Se Recontabilizaron '.($i-1).' documentos de inventarios.';
    }


    // DESDE EL MOVIMIENTO DE COMPRAS
    public function corregir_cantidades()
    {
        // Se va a actualizar los registros de los documentos de inventario y el movimiento con base en los movimientos de compras y ventas que si quedaron bien registrados

        // Registros de compras
        $movimiento_compras = ComprasMovimiento::select('entrada_almacen_id','inv_producto_id','cantidad')->get();

        $i = 0;
        foreach ($movimiento_compras as $linea) {
            // Actualizar registros de documentos de inventarios
            $registro = InvDocRegistro::where('inv_doc_encabezado_id',$linea->entrada_almacen_id)
                                    ->where('inv_producto_id',$linea->inv_producto_id)
                                    ->get()
                                    ->first();

            if ( !is_null($registro) ) {
                $costo_total = $registro->costo_unitario * $linea->cantidad;

                $registro->update( [ 
                                    'cantidad' => $linea->cantidad,
                                    'costo_total' => $costo_total
                                    ]);
            }

            // Actualizar movimientos
            $movimiento = InvMovimiento::where('inv_doc_encabezado_id',$linea->entrada_almacen_id)
                                        ->where('inv_producto_id',$linea->inv_producto_id)
                                        ->get()
                                        ->first();

            if ( !is_null($movimiento) ) {
                $costo_total = $movimiento->costo_unitario * $linea->cantidad;

                $movimiento->update( [ 
                                    'cantidad' => $linea->cantidad,
                                    'costo_total' => $costo_total
                                    ]);
            }

            $i++;
        }

        echo 'Se actualizaron '.$i.' registros las tablas de inv_doc_registros e inv_movimientos.';
    }

    /*
     * RECOSTEO: Este proceso actualiza todos los valores de los campos costo_unitario y costo_total en las líneas de registros de documentos de inventarios, en el movimiento de inventarios y los registros contables del movimiento de inventario
     * Tablas: inv_doc_registros, inv_movimientos y contab_movimientos
     */
    /*
        NOTA: Esto proceso genera diferencias entre las compras y los inventarios (Cuenta por legalizar)
    */
    public function recosteo()
    {
        $fecha_desde = Input::get('fecha_desde');//'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta');//'2019-10-28';

        if ( is_null( $fecha_desde ) || is_null( $fecha_hasta) )
        {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> inv_recosteo?fecha_desde=2019-10-28&fecha_hasta=2019-10-28&inv_producto_id=1';
            dd('Operación cancelada.');
        }

        $inv_producto_id = '%%';
        $operador1 = 'LIKE';

        if ( Input::get('inv_producto_id') != '' )
        {
            $inv_producto_id = Input::get('inv_producto_id');
            $operador1 = '=';
        }

        // Obtener TODOS los documentos de inventarios entre las fechas indicadas
        // No se DEBEN recostear los documentos de Ensambles (Fabricación)
        $documentos = InvDocEncabezado::where( 'estado', '<>', 'Anulado')
                                    ->where( 'core_tipo_transaccion_id', '<>', 4)
                                    ->whereBetween( 'fecha', [ $fecha_desde, $fecha_hasta] )
                                    ->get();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            // Los registros de cada documento
            $registros = InvDocRegistro::where('inv_doc_encabezado_id',$un_documento->id)
                                        ->where('inv_producto_id', $operador1, $inv_producto_id)
                                        ->get();

            foreach ($registros as $un_registro)
            {
                // Por cada registro se obtiene el costo promedio ACTUAL del producto para la bodega
                $costo_promedio = InvCostoPromProducto::where( 'inv_bodega_id', $un_registro->inv_bodega_id )
                                                    ->where( 'inv_producto_id', $un_registro->inv_producto_id )
                                                    ->value( 'costo_promedio');
                
                $costo_total = $un_registro->cantidad * $costo_promedio;

                // Se actualiza el costo_unitario y costo_total en cada línea de registro del documento
                $un_registro->costo_unitario = $costo_promedio;
                $un_registro->costo_total = $costo_total;
                $un_registro->save();

                // Se actualiza el movimiento de inventario
                InvMovimiento::where('core_tipo_transaccion_id', $un_documento->core_tipo_transaccion_id )
                            ->where('core_tipo_doc_app_id', $un_documento->core_tipo_doc_app_id )
                            ->where('consecutivo', $un_documento->consecutivo )
                            ->where('inv_bodega_id', $un_registro->inv_bodega_id )
                            ->where('inv_producto_id', $un_registro->inv_producto_id )
                            ->where('cantidad', $un_registro->cantidad )
                            ->update( [ 'costo_unitario' => $costo_promedio, 'costo_total' => $costo_total  ] );


                // Se actualiza el registro contable para la transacción de esa línea de registro (DB y CR)
                ContabMovimiento::where('core_tipo_transaccion_id', $un_documento->core_tipo_transaccion_id )
                                ->where('core_tipo_doc_app_id', $un_documento->core_tipo_doc_app_id )
                                ->where('consecutivo', $un_documento->consecutivo )
                                ->where('inv_bodega_id', $un_registro->inv_bodega_id )
                                ->where('inv_producto_id', $un_registro->inv_producto_id )
                                ->where('cantidad', $un_registro->cantidad )
                                ->where('valor_credito', 0 )
                                ->update( [ 'valor_debito' => abs( $costo_total ), 'valor_saldo' => abs( $costo_total ) ] );

                ContabMovimiento::where('core_tipo_transaccion_id', $un_documento->core_tipo_transaccion_id )
                                ->where('core_tipo_doc_app_id', $un_documento->core_tipo_doc_app_id )
                                ->where('consecutivo', $un_documento->consecutivo )
                                ->where('inv_bodega_id', $un_registro->inv_bodega_id )
                                ->where('inv_producto_id', $un_registro->inv_producto_id )
                                ->where('cantidad', $un_registro->cantidad )
                                ->where('valor_debito', 0 )
                                ->update( [ 'valor_credito' => (abs( $costo_total ) * -1), 'valor_saldo' => (abs( $costo_total ) * -1) ] );

                echo $i.'  ';
                $i++;
            }
            
        }

        return redirect( 'inv_recosteo_form?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message','Se actualizaron '.($i-1).' líneas de registros de inventarios,<br> y '.(($i-1) * 2).' registros contables.');
    }
}
