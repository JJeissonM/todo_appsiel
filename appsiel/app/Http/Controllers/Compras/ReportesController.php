<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Lava;

use App\Compras\ComprasMovimiento;
use App\Compras\OrdenCompra;
use App\Compras\Proveedor;
use App\Compras\Services\TesoreriaService;
use App\Core\Tercero;
use App\Core\TipoDocApp;
use App\CxP\DocumentosPendientes;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvProducto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Khill\Lavacharts\Laravel\LavachartsFacade;

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
        $clase_proveedor_id = (int)$request->clase_proveedor_id;

        if ( $request->core_tercero_id == '' )
        {
            $operador = 'LIKE';
            $cadena = '%'.$request->core_tercero_id.'%';
        }
    
        $movimiento = DocumentosPendientes::get_documentos_referencia_tercero( $operador, $cadena );

        if (count($movimiento) > 0) {
            $movimiento = collect($movimiento);
            $group = $movimiento->groupBy('core_tercero_id');
            $collection = null;
            $collection = collect($collection);
            foreach ($group as $key => $item) {
                $aux = $item->pluck('saldo_pendiente');
                $sum = $aux->sum();
                
                // Filtrar clase de proveedor
                if ($clase_proveedor_id != '') {
                    $proveedor = Proveedor::where([
                        ['core_tercero_id','=',$item[0]['core_tercero_id']]
                    ])->get()->first();
                    if ($proveedor == null) {
                        continue;
                    }

                    if ($proveedor->clase_proveedor_id != $clase_proveedor_id) {
                        continue;
                    }                    
                }
                
                foreach ($item as $value){


                    $collection[] = $value;
                }
                $obj = ["id" => 0,
                    "core_tipo_transaccion_id" => '',
                    "core_tipo_doc_app_id" => '',
                    "consecutivo" => '',
                    "tercero" => '',
                    "documento" => '',
                    "fecha" => '',
                    "fecha_vencimiento" => '',
                    "valor_documento" => 0,
                    "valor_pagado" => 0.0,
                    "saldo_pendiente" => 0.0,
                    "sub_total" => $sum,
                    "clase_cliente_id" => '',
                    "core_tercero_id" => '',
                    "estado" => ''
                ];
                $collection[]=$obj;
            }
            $movimiento = $collection;
        }

        $vista = View::make( 'compras.incluir.ctas_por_pagar', compact('movimiento') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
   
        return $vista;
    }

    public static function grafica_compras_diarias($fecha_inicial, $fecha_final)
    {
        $registros = ComprasMovimiento::mov_compras_totales_por_fecha( $fecha_inicial, $fecha_final );

        $stocksTable1 = LavachartsFacade::DataTable();
      
        $stocksTable1->addStringColumn('Compras')
                    ->addNumberColumn('Fecha');

        $i = 0;
        $tabla = [];
        foreach ($registros as $linea) 
        {
            $fecha  = date("d-m-Y", strtotime("$linea->fecha"));

            $stocksTable1->addRow( [ $linea->fecha, (float)$linea->total_compras ]);

            $tabla[$i]['fecha'] = $linea->fecha;
            $tabla[$i]['valor'] = (float)$linea->total_compras;
            $i++;
        }

        // Se almacena la gráfica en compras_diarias, luego se llama en la vista [ como mágia :) ]
        LavachartsFacade::BarChart('compras_diarias', $stocksTable1,[
            'is3D' => True,
            'colors' => ['#574696'],
            'orientation' => 'horizontal',
            'vAxis'=> ['title'=>'Monto Total','format'=> '$ #,###.##'],
            'hAxis'=> ['title'=>'Fecha'],
            'height'=> '400',
            'legend'=> ['position'=>'none'],
            'tooltip'=>null
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

        $proveedor_id = $request->proveedor_id;
        $operador2 = '=';

        $grupo_inventario_id = $request->grupo_inventario_id;
        $operador3 = '=';

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

        if ( $grupo_inventario_id == '' )
        {
            $operador3 = 'LIKE';
            $grupo_inventario_id = '%'.$grupo_inventario_id.'%';
        }

        $movimiento = ComprasMovimiento::get_precios_compras( $fecha_desde, $fecha_hasta, $inv_producto_id, $operador1, $proveedor_id, $operador2, $grupo_inventario_id, $operador3 );

        // En el movimiento se trae el precio_total con IVA incluido
        $mensaje = 'IVA Incluido en precio.';
        
        if ( !$iva_incluido )
        {
            $mensaje = 'IVA <b>NO</b> incluido en precio.';
        }

        $vista = View::make('compras.reportes.precio_compra', compact('movimiento','detalla_proveedores', 'mensaje', 'porcentaje_proyeccion_1', 'porcentaje_proyeccion_2', 'porcentaje_proyeccion_3', 'porcentaje_proyeccion_4', 'iva_incluido' ) )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    public function ultimos_precios_por_producto(Request $request)
    {
        $iva_incluido  = (int)$request->iva_incluido;        
        $inv_producto_id = (int)$request->inv_producto_id;
        $proveedor_id = (int)$request->proveedor_id;
        $grupo_inventario_id = (int)$request->grupo_inventario_id;
        
        $array_wheres = [
            ['core_empresa_id','=', Auth::user()->empresa_id]
        ];

        if ( $inv_producto_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [[ 'id', '=', $inv_producto_id]]);
        }

        if ( $grupo_inventario_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [[ 'inv_grupo_id', '=', $grupo_inventario_id]]);
        }

        $proveedor = null;
        if ( $proveedor_id != 0 )
        {
            $proveedor = Proveedor::find($proveedor_id);
        }else{
            $proveedor_id = null;
        }

        $listado = InvProducto::where( $array_wheres )->get();

        $mensaje = 'IVA <b>NO</b> incluido en precio.';
        if ( $iva_incluido )
        {
            $mensaje = 'IVA Incluido en precio.';
        }

        //$listado = $this->get_listado_ordenado_ultimo_precio_compra( $listado, $proveedor_id, $iva_incluido );

        $vista = View::make('compras.reportes.ultimo_precio_compra', compact('listado','proveedor_id', 'mensaje', 'iva_incluido' ) )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

    public function get_listado_ordenado_ultimo_precio_compra( $lista_items, $proveedor_id, $iva_incluido )
    {        
        $listado = collect([]);
        foreach( $lista_items as $item )
        {
            $ultima_compra = ComprasMovimiento::get_ultimo_precio_producto($proveedor_id, $item->id, $item->inv_grupo_id);

            if ( $ultima_compra->core_tipo_transaccion_id == null ) {
                continue;
            }

            $precio_unitario = $ultima_compra->precio_unitario;
            $precio_total = $ultima_compra->precio_total;
            if ( !$iva_incluido )
            {
                $precio_unitario = $ultima_compra->base_impuesto / $ultima_compra->cantidad;
                $precio_total = $ultima_compra->base_impuesto;
            }
        
            $linea['categoria'] = $item->grupo_inventario->descripcion;
            $linea['item'] = $item->get_value_to_show();
            if( $item->estado == 'Inactivo')
            {
                $linea['item'] = $item->get_value_to_show() . '(Inactivo)';
            }

            $linea['fecha'] = $ultima_compra->fecha;
            $linea['precio_unitario'] = '$' . number_format( $precio_unitario, 2, ',', '.');
            $linea['cantidad'] = '$' . number_format( $ultima_compra->cantidad, 2, ',', '.');
            $linea['precio_total'] = '$' . number_format( $precio_total, 2, ',', '.');
            $linea['documento'] = $ultima_compra->get_label_documento();
            $linea['proveedor_id'] = $ultima_compra->proveedor->id;
            $linea['proveedor'] = $ultima_compra->proveedor->tercero->numero_identificacion . '-' . $ultima_compra->proveedor->tercero->descripcion;

            $listado->push( $linea );
        }

        return $listado;
    }

    /*
    Reporte de ordenes de compra vencidas
    */
    public static function ordenes_vencidas()
    {
        $parametros = config('compras');
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $ordenes_db = OrdenCompra::where('core_empresa_id', Auth::user()->empresa_id)
                                ->where([['core_tipo_doc_app_id', $parametros['oc_tipo_doc_app_id']], ['fecha_recepcion', '<', $fecha], ['estado', 'Pendiente']])
                                ->get();
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
        $ordenes_db = OrdenCompra::where('core_empresa_id', Auth::user()->empresa_id)
                                ->where([['core_tipo_doc_app_id', $parametros['oc_tipo_doc_app_id']], ['fecha_recepcion', '>', $fecha], ['estado', 'Pendiente']])->get();
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
                'fecha' => date_format(date_create($f), 'd-m-Y'),
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
        if ($proveedor == "") {
            $proveedor = $tercero->descripcion;
        }
        $orden = [
            'id' => $o->id,
            'documento' => TipoDocApp::find($o->core_tipo_doc_app_id)->prefijo . " - " . $o->consecutivo,
            'proveedor' => $proveedor,
            'fecha_recepcion' => date_format(date_create($o->fecha_recepcion), 'd-m-Y'),
            'fecha' => date_format(date_create($o->fecha), 'd-m-Y'),
        ];
        return $orden;
    }

    public static function entradas_pendientes_por_facturar()
    {
        return InvDocEncabezado::where([
                                        ['estado','Pendiente'],
                                        ['core_tipo_transaccion_id',35]
                                    ])
                                ->get();
    }


    public function reporte_compras(Request $request)
    {
        $user = Auth::user();

        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $agrupar_por = $request->agrupar_por;
        $detalla_productos  = (int)$request->detalla_productos;
        $detalla_clientes  = (int)$request->detalla_clientes;
        $iva_incluido  = (int)$request->iva_incluido;

        $movimiento = ComprasMovimiento::get_movimiento_compras($fecha_desde, $fecha_hasta, $agrupar_por, null, $request->proveedor_id);

        // En el movimiento se trae el precio_total con IVA incluido
        $mensaje = 'IVA Incluido en precio';
        if ( !$iva_incluido )
        {
            $mensaje = 'IVA <b>NO</b> incluido en precio';
        }

        $vista = View::make('compras.reportes.reporte_compras_ordenado', compact('movimiento','agrupar_por','mensaje','iva_incluido','detalla_productos'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function descuentos_por_pronto_pago(Request $request)
    {
        $user = Auth::user();

        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;  
        $detalla_documentos  = (int)$request->detalla_documentos;        

        $items_con_descuento = (new TesoreriaService())->get_lineas_items_compras_con_descuentos_por_pronto_pago($fecha_desde, $fecha_hasta, $detalla_documentos);

        if ($detalla_documentos) {
            $vista = View::make('compras.reportes.reporte_descuentos_pronto_pago_detallado', compact('items_con_descuento'))->render();
        }else{
            $vista = View::make('compras.reportes.reporte_descuentos_pronto_pago', compact('items_con_descuento'))->render();
        }        

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

}