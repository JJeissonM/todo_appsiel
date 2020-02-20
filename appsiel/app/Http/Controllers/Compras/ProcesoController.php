<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Inventarios\ProcesoController as InvProcesoController;

use App\Http\Controllers\Compras\ComprasController;
use App\Http\Controllers\Compras\NotaCreditoController;


use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasMovimiento;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;

use App\Contabilidad\ContabMovimiento;

use Input;

class ProcesoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function recontabilizar_documento_nota_credito( $documento_id )
    {
        ProcesoController::recontabilizar_nota_credito($documento_id);
        return redirect( 'compras/'.$documento_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion').'&vista=compras.notas_credito.show' )->with( 'flash_message', 'Documento Recontabilizado.' );
    }

    // Recontabilizar un documento dada su ID
    public static function recontabilizar_documento( $documento_id )
    {
        $documento = ComprasDocEncabezado::find( $documento_id );

        // Recontabilizar la entrada de almacén
        if ( $documento->entrada_almacen_id != 0)
        {
            InvProcesoController::recontabilizar_documento( $documento->entrada_almacen_id );
        }

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id',$documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$documento->core_tipo_doc_app_id)
                        ->where('consecutivo',$documento->consecutivo)
                        ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = ComprasDocRegistro::where( 'compras_doc_encabezado_id', $documento->id )->get();

        $total_documento = 0;
        $n = 1;
        foreach ($registros_documento as $linea)
        {
            $detalle_operacion = 'Recontabilizado. '.$linea->descripcion;
            CompraController::contabilizar_movimiento_debito( $documento->toArray() + $linea->toArray(), $detalle_operacion );
            $total_documento += $linea->precio_total;
            $n++;
        }

        $forma_pago = 'credito';
        CompraController::contabilizar_movimiento_credito( $forma_pago, $documento->toArray(), $total_documento, $detalle_operacion );
    }


    /*
     * RECONTABILIZACION FACTURAS DE COMPRAS
     */
    public function recontabilizar_documentos_compras()
    {
        $fecha_desde = Input::get('fecha_desde');//'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta');//'2019-10-28';

        if ( is_null( $fecha_desde ) || is_null( $fecha_hasta) )
        {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> recontabilizar_documentos_compras?fecha_desde=2019-10-28&fecha_hasta=2019-10-28';
            dd('Operación cancelada.');
        }

        // Obtener TODOS los documentos entre las fechas indicadas
        $documentos = ComprasDocEncabezado::where('estado','<>','Anulado')
                                        ->whereIn('core_tipo_transaccion_id', [25, 29] ) // Factura y Doc. Equivalente
                                        ->whereBetween( 'fecha', [ $fecha_desde, $fecha_hasta] )
                                        ->get();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            ProcesoController::recontabilizar_documento( $un_documento->id );
            echo $i.'  ';
            $i++;            
        }

        echo '<br>Se Recontabilizaron '.($i-1).' documentos de compras con sus repectivas entradas de almacén.';
    }


    // Recontabilizar una nota_credito dada su ID
    public static function recontabilizar_nota_credito( $documento_id )
    {
        $documento = ComprasDocEncabezado::find( $documento_id );

        // Recontabilizar la devolución
        /* PENDIENTE: pueden haber varias devoluciones.
        if ( $documento->entrada_almacen_id != 0)
        {
            InvProcesoController::recontabilizar_documento( $documento->entrada_almacen_id );
        }
        */

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id',$documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$documento->core_tipo_doc_app_id)
                        ->where('consecutivo',$documento->consecutivo)
                        ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = ComprasDocRegistro::where( 'compras_doc_encabezado_id', $documento->id )->get();

        $total_documento = 0;
        $n = 1;
        foreach ($registros_documento as $linea)
        {
            $detalle_operacion = 'Recontabilizado. '.$linea->descripcion;
            NotaCreditoController::contabilizar_movimiento_credito( $documento->toArray() + $linea->toArray(), $detalle_operacion );
            $total_documento += $linea->precio_total;
            $n++;
        }

        $forma_pago = 'credito';
        NotaCreditoController::contabilizar_movimiento_debito( $forma_pago, $documento->toArray(), $total_documento, $detalle_operacion );
    }



    // PROCESOS
    public function actualizar_valor_total_compras_encabezados_doc()
    {
        $documentos = ComprasDocEncabezado::all();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            $valor_total = ComprasDocRegistro::where('compras_doc_encabezado_id',$un_documento->id)->sum('precio_total');
            $un_documento->valor_total = $valor_total;
            $un_documento->save();
            echo $i.'  ';
            $i++;
        }

        echo '<br>Se actualizaron '.($i-1).' documentos.';
    }
}
