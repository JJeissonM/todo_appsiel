<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Inventarios\ProcesoController as InvProcesoController;
use App\Http\Controllers\Ventas\VentaController;
use App\Http\Controllers\Ventas\NotaCreditoController;


use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Ventas\InvCostoPromProducto;

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

    public function recontabilizar_documento_factura( $documento_id )
    {
        ProcesoController::recontabilizar_documento($documento_id);
        return redirect( 'ventas/'.$documento_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( 'flash_message', 'Documento Recontabilizado.' );
    }

    public function recontabilizar_documento_nota_credito( $documento_id )
    {
        ProcesoController::recontabilizar_nota_credito($documento_id);
        return redirect( 'ventas/'.$documento_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion').'&vista=ventas.notas_credito.show' )->with( 'flash_message', 'Documento Recontabilizado.' );
    }

    // Recontabilizar un documento dada su ID
    public static function recontabilizar_documento( $documento_id )
    {
        $documento = VtasDocEncabezado::find( $documento_id );

        // Recontabilizar la remisión
        /* ¿Qué hacer cuando tiene varias remisiones?
        if ( $documento->remision_doc_encabezado_id != 0)
        {
            InvProcesoController::recontabilizar_documento( $documento->remision_doc_encabezado_id );
        }
        */

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id',$documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$documento->core_tipo_doc_app_id)
                        ->where('consecutivo',$documento->consecutivo)
                        ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = VtasDocRegistro::where( 'vtas_doc_encabezado_id', $documento->id )->get();

        $total_documento = 0;
        $n = 1;
        foreach ($registros_documento as $linea)
        {
            $detalle_operacion = 'Recontabilizado. '.$linea->descripcion;
            VentaController::contabilizar_movimiento_credito( $documento->toArray() + $linea->toArray(), $detalle_operacion );
            $total_documento += $linea->precio_total;
            $n++;
        }

        $forma_pago = $documento->forma_pago;
        VentaController::contabilizar_movimiento_debito( $forma_pago, $documento->toArray(), $total_documento, $detalle_operacion );/**/
    }


    /*
     * RECONTABILIZACION FACTURAS DE VENTAS
     */
    public function recontabilizar_documentos_ventas()
    {
        $fecha_desde = Input::get('fecha_desde');//'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta');//'2019-10-28';

        if ( is_null( $fecha_desde ) || is_null( $fecha_hasta) )
        {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> recontabilizar_documentos_ventas?fecha_desde=2019-10-28&fecha_hasta=2019-10-28';
            dd('Operación cancelada.');
        }

        // Obtener TODOS los documentos entre las fechas indicadas
        $documentos = VtasDocEncabezado::where('estado','<>','Anulado')
                                        ->whereIn('core_tipo_transaccion_id', [23] ) // 23 = Facturas de ventas
                                        ->whereBetween( 'fecha', [ $fecha_desde, $fecha_hasta] )
                                        ->get();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            ProcesoController::recontabilizar_documento( $un_documento->id );
            echo $i.'  ';
            $i++;            
        }

        echo '<br>Se Recontabilizaron '.($i-1).' documentos de ventas.'; //con sus repectivas remisiones
    }

    /*
     * RECONTABILIZACION NOTAS CRÉDITO DE VENTAS
     */
    public function recontabilizar_notas_creditos_ventas()
    {
        $fecha_desde = Input::get('fecha_desde');//'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta');//'2019-10-28';

        if ( is_null( $fecha_desde ) || is_null( $fecha_hasta) )
        {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> recontabilizar_documentos_ventas?fecha_desde=2019-10-28&fecha_hasta=2019-10-28';
            dd('Operación cancelada.');
        }

        // Obtener TODOS los documentos entre las fechas indicadas
        $documentos = VtasDocEncabezado::where('estado','<>','Anulado')
                                        ->whereIn('core_tipo_transaccion_id', [38, 41] ) // Nota crédito y NC Directa 
                                        ->whereBetween( 'fecha', [ $fecha_desde, $fecha_hasta] )
                                        ->get();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            ProcesoController::recontabilizar_nota_credito( $un_documento->id );
            echo $i.'  ';
            $i++;            
        }

        echo '<br>Se Recontabilizaron '.($i-1).' documentos de ventas con sus repectivas remisiones.';
    }

    // Recontabilizar una NOTA CRÉDITO dada su ID
    public static function recontabilizar_nota_credito( $documento_id )
    {
        $documento = VtasDocEncabezado::find( $documento_id );

        // Recontabilizar la devolución
        /* ¿Qué hacer cuando tiene varias devoluciones?
        if ( $documento->remision_doc_encabezado_id != 0)
        {
            InvProcesoController::recontabilizar_documento( $documento->remision_doc_encabezado_id );
        }
        */
        

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id',$documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$documento->core_tipo_doc_app_id)
                        ->where('consecutivo',$documento->consecutivo)
                        ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = VtasDocRegistro::where( 'vtas_doc_encabezado_id', $documento->id )->get();

        $total_documento = 0;
        $n = 1;
        foreach ($registros_documento as $linea)
        {
            $detalle_operacion = 'Recontabilizado. '.$linea->descripcion;
            NotaCreditoController::contabilizar_movimiento_debito( $documento->toArray() + $linea->toArray(), $detalle_operacion );
            $total_documento += $linea->precio_total;
            $n++;
        }

        NotaCreditoController::contabilizar_movimiento_credito( $documento->toArray(), $total_documento, $detalle_operacion );/**/
    }



    public function actualizar_valor_total_vtas_encabezados_doc()
    {
        $documentos = VtasDocEncabezado::all();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            $valor_total = VtasDocRegistro::where('vtas_doc_encabezado_id',$un_documento->id)->sum('precio_total');
            $un_documento->valor_total = $valor_total;
            $un_documento->save();
            echo $i.'  ';
            $i++;
        }

        echo '<br>Se actualizaron '.($i-1).' documentos.';
    }
}
