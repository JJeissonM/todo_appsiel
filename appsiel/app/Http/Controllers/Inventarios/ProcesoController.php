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

use App\Compras\ComprasMovimiento;
use App\Contabilidad\ContabMovimiento;
use App\Inventarios\Services\AccountingServices;
use App\Inventarios\Services\RecosteoService;
use Illuminate\Support\Facades\Input;

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
        $acco_service = new AccountingServices();
        $acco_service->recontabilizar_documento($documento_id);
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
            echo $un_documento->id.'  ';
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

        $recosteo_serv = new RecosteoService();
        $response = $recosteo_serv->recostear($operador1,$inv_producto_id,$fecha_desde,$fecha_hasta, Input::get('modo_recosteo'),Input::get('tener_en_cuenta_movimientos_anteriores'));

        return redirect( 'inv_recosteo_form?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with('flash_message',$response->message);
            
    }

    // Pendiente
    public function anulacion_masiva($lista_ids)
    {
        $lista = explode(',',$lista_ids);
        foreach ($lista as $key => $remision_id) {
            # code...
        }
    }
}
