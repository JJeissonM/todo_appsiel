<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use Auth;
use Form;
use View;
use Cache;
use Lava;

use App\Compras\ComprasMovimiento;
use App\Compras\OrdenCompra;
use App\Compras\Proveedor;
use App\Core\Tercero;
use App\Core\TipoDocApp;
use App\CxP\DocumentosPendientes;

use App\Contabilidad\Impuesto;


class ReportesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function ctas_por_pagar(Request $request)
    {
                
        $operador = '=';
        $cadena = $request->core_tercero_id;

        if ( $request->core_tercero_id == '' )
        {
            $operador = 'LIKE';
            $cadena = '%'.$request->core_tercero_id.'%';
        }
    
        $movimiento = DocumentosPendientes::get_documentos_referencia_tercero( $operador, $cadena );

        $vista = View::make( 'compras.incluir.ctas_por_pagar', compact('movimiento') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
   
        return $vista;
    }

    public static function grafica_compras_diarias($fecha_inicial, $fecha_final)
    {
        $registros = ComprasMovimiento::whereBetween('fecha',[$fecha_inicial, $fecha_final])
                                        ->select(DB::raw('SUM(base_impuesto) as total_compras'),'fecha')
                                        ->groupBy('fecha')
                                        ->orderBy('fecha')
                                        ->get();

        $stocksTable1 = Lava::DataTable();
      
        $stocksTable1->addStringColumn('Compras')
                    ->addNumberColumn('Fecha');

        $i = 0;
        $tabla = [];
        foreach ($registros as $linea) 
        {
            $stocksTable1->addRow( [ $linea->fecha, (float)$linea->total_compras ]);

            $tabla[$i]['fecha'] = $linea->fecha;
            $tabla[$i]['valor'] = (float)$linea->total_compras;
            $i++;
        }

        // Se almacena la gráfica en compras_diarias, luego se llama en la vista [ como mágia :) ]
        Lava::BarChart('compras_diarias', $stocksTable1,[
                                                          'is3D' => True,
                                                          'orientation' => 'horizontal',
                                                      ]);

        return $tabla;
    }

    public function precio_compra_por_producto(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $detalla_proveedores  = (int)$request->detalla_proveedores;
        $iva_incluido  = (int)$request->iva_incluido;
        
        $inv_producto_id = $request->inv_producto_id;
        $operador1 = '=';
        //dd($inv_producto_id);
        $proveedor_id = $request->proveedor_id;
        $operador2 = '=';


        $porcentaje_proyeccion_1 = (float)$request->porcentaje_proyeccion_1;
        $porcentaje_proyeccion_2 = (float)$request->porcentaje_proyeccion_2;
        $porcentaje_proyeccion_3 = (float)$request->porcentaje_proyeccion_3;
        $porcentaje_proyeccion_4 = (float)$request->porcentaje_proyeccion_4;



        if ( $inv_producto_id == '' )
        {
            $operador1 = 'LIKE';
            $inv_producto_id = '%'.$inv_producto_id.'%';
        }

        if ( $proveedor_id == '' )
        {
            $operador2 = 'LIKE';
            $proveedor_id = '%'.$proveedor_id.'%';
        }

        $movimiento = ComprasMovimiento::get_precios_compras( $fecha_desde, $fecha_hasta, $inv_producto_id, $operador1, $proveedor_id, $operador2 );

        // En el movimiento se trae el precio_total con IVA incluido
        $mensaje = 'IVA Incluido en precio.';
        
        if ( !$iva_incluido )
        {
            $mensaje = 'IVA <b>NO</b> incluido en precio.';            
            foreach ($movimiento as $linea )
            {
                $tasa_impuesto = Impuesto::get_tasa( $linea->inv_producto_id, 0, 0 );
                $precio_unitario = $linea->precio_total / ( 1 + $tasa_impuesto  / 100 );
                $linea->precio_total = $precio_unitario;
            }
        }

        $vista = View::make('compras.reportes.precio_compra', compact('movimiento','detalla_proveedores', 'mensaje', 'porcentaje_proyeccion_1', 'porcentaje_proyeccion_2', 'porcentaje_proyeccion_3', 'porcentaje_proyeccion_4' ) )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    /*
    Reporte de ordenes de compra vencidas
    */
    public static function ordenes_vencidas()
    {
        $parametros = config('compras');
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $ordenes_db = OrdenCompra::where([['core_tipo_doc_app_id', $parametros['oc_tipo_doc_app_id']], ['fecha_recepcion', '<', $fecha], ['estado', 'Pendiente']])->get();
        $ordenes = null;
        if (count($ordenes_db) > 0) {
            foreach ($ordenes_db as $o) {
                $ordenes[] = ReportesController::prepara_datos($o);
            }
        }
        return $ordenes;
    }

    /*
    Reporte de ordenes de compra futuras
    */
    public static function ordenes_futuras()
    {
        $parametros = config('compras');
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $ordenes_db = OrdenCompra::where([['core_tipo_doc_app_id', $parametros['oc_tipo_doc_app_id']], ['fecha_recepcion', '>', $fecha], ['estado', 'Pendiente']])->get();
        $ordenes = null;
        if (count($ordenes_db) > 0) {
            foreach ($ordenes_db as $o) {
                $ordenes[] = ReportesController::prepara_datos($o);
            }
        }
        return $ordenes;
    }

    /*
    Reporte de pendientes de la semana
    */
    public static function ordenes_semana()
    {
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $date2 = strtotime($fecha);
        $inicio0 = strtotime('sunday this week -1 week', $date2);
        $inicio = date('Y-m-d', $inicio0);
        $fechas = null;
        for ($i = 1; $i <= 7; $i++) {
            $fechas[] = date("Y-m-d", strtotime("$inicio +$i day"));
        }
        $data = null;
        $parametros = config('compras');
        foreach ($fechas as $f) {
            $ordenes_db = OrdenCompra::where([['core_tipo_doc_app_id', $parametros['oc_tipo_doc_app_id']], ['fecha_recepcion', '=', $f], ['estado', 'Pendiente']])->get();
            $ordenes = null;
            if (count($ordenes_db) > 0) {
                foreach ($ordenes_db as $o) {
                    $ordenes[] = ReportesController::prepara_datos($o);
                }
            }
            $data[] = [
                'fecha' => $f,
                'data' => $ordenes
            ];
        }
        return $data;
    }

    //Prepara los datos a mostrar de la orden de compra
    public static function prepara_datos($o)
    {
        $p = Proveedor::find($o->proveedor_id);
        $tercero = Tercero::find($p->core_tercero_id);
        $proveedor = $tercero->razon_social;
        if ($proveedor == "") {
            $proveedor = $tercero->nombre1 . " " . $tercero->otros_nombres . " " . $tercero->apellido1 . " " . $tercero->apellido2;
        }
        $orden = [
            'id' => $o->id,
            'documento' => TipoDocApp::find($o->core_tipo_doc_app_id)->prefijo . " - " . $o->consecutivo,
            'proveedor' => $proveedor,
            'fecha_recepcion' => $o->fecha_recepcion
        ];
        return $orden;
    }

}