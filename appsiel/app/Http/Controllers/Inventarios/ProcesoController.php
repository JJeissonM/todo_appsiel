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
use App\Inventarios\RecetaCocina;
use App\Inventarios\Services\AccountingServices;
use App\Inventarios\Services\CodigoBarras;
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
        $response = $recosteo_serv->recostear($operador1, $inv_producto_id, $fecha_desde, $fecha_hasta, Input::get('recontabilizar_contabilizar_movimientos'));

        return redirect( 'inv_recosteo_form?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') )->with($response->status,$response->message);
            
    }

    public function recontabilizar_costos_movimientos_un_item()
    {
        $fecha_desde = Input::get('fecha_desde');//'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta');//'2019-10-28';

        if ( is_null( $fecha_desde ) || is_null( $fecha_hasta) )
        {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> inv_recontabilizar_costos_movimientos_un_item?fecha_desde=2019-10-28&fecha_hasta=2019-10-28&inv_producto_id=1';
            dd('Operación cancelada.');
        }

        $inv_producto_id = '%%';
        $operador1 = 'LIKE';

        if ( Input::get('inv_producto_id') != '' )
        {
            $inv_producto_id = Input::get('inv_producto_id');
            $operador1 = '=';
        }

        $acco_serv = new AccountingServices();
        $response = $acco_serv->recontabilizar_costos_movimientos($operador1, $inv_producto_id, $fecha_desde, $fecha_hasta );

        dd($response);
            
    }

    public function actualizar_costo_promedio_platilllo($item_platillo_id, $costo_promedio)
    {
        $registro_costo_prom = InvCostoPromProducto::where([
            ['inv_producto_id','=',$item_platillo_id]
        ])
            ->get()
            ->first();
        
        if ($registro_costo_prom == null) {
            InvCostoPromProducto::create( [
                'inv_producto_id' => $item_platillo_id,
                'costo_promedio' => $costo_promedio
                ]
            );
        }else{
            $registro_costo_prom->costo_promedio = $costo_promedio;
            $registro_costo_prom->save();
        }

        $item_platillo = InvProducto::find($item_platillo_id);
        if ($item_platillo != null) {
            $item_platillo->precio_compra = $costo_promedio;
            $item_platillo->save();
        }        
        
        $registro_receta = RecetaCocina::where([
            ['item_platillo_id','=',$item_platillo_id]
        ])
            ->get()
            ->first();

        return redirect('web/' . $registro_receta->id . '?id=8&id_modelo=321&id_transaccion=')->with('flash_message','Costo promedio del platillo actualizado correctamente.');
    }
    
    public function asignar_codigos_barras_desde_id()
    {
        $items = InvProducto::get_datos_basicos( '', 'Activo', 'sin_codigo_barras');
        $i = 0;
        foreach ($items as $item) {
            unset($item->costo_promedio);
            unset($item->existencia_actual);
            unset($item->tasa_impuesto);
            $item->codigo_barras = (new CodigoBarras($item->id, 0, 0, 0))->barcode;
            $item->save();

            $i++;
        }

        echo 'Se actualizaron ' . $i . ' ítems';
    }

    public function unificar_ids_items_repetidos_en_lineas_registros($inv_document_header_id)
    {
        $documento = InvDocEncabezado::find($inv_document_header_id);

        $lineas_agrupadas = $documento->lineas_registros->groupBy('inv_producto_id');

        $i = 0;
        foreach ($lineas_agrupadas as $grupo) {
            $is_the_first = true;
            foreach ($grupo as $linea_registro) {
                if ($is_the_first) {
                    
                    $linea_registro->cantidad = $grupo->sum('cantidad');
                    
                    $linea_registro->costo_total = $linea_registro->cantidad * $linea_registro->costo_unitario;

                    $linea_registro->save();

                    $is_the_first = false;
                    
                    $i++;
                }else{
                    $linea_registro->delete();
                }
            }
        }

        echo 'Fueron unificadas ' . $i . ' líneas.';
    }
}
