<?php

namespace App\Http\Controllers\Inventarios;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use Form;
use Cache;
use View;
use DB;

use App\Inventarios\InvBodega;
use App\Inventarios\InvProducto;
use App\Inventarios\InvGrupo;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\MinStock;

use App\Inventarios\Services\FiltroMovimientos;

use App\Compras\ComprasMovimiento;
use App\Inventarios\Services\StockAmountService;

class ReporteController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar las EXISTENCIAS de una bodega ($id). LLAMADO DESDE EL INDEX
     *
     */
    public function inv_consultar_existencias($id)
    {
        $fecha_corte = Input::get('fecha_corte');

        $productos = InvMovimiento::get_movimiento_corte( $fecha_corte, '=', $id, 'LIKE', '%%');
      
        $bodega = InvBodega::find($id);

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>'Existencias']
            ];
        
        return view('inventarios.existencias_una_bodega',compact('productos','bodega','miga_pan','fecha_corte'));
    }


    // MOSTAR FORMULARIO REPORTE DE EXISTENCIAS
    public function inv_existencias()
    {
        $bodegas = InvBodega::opciones_campo_select();
        $items = InvProducto::opciones_campo_select();
        $grupo_inventario = InvGrupo::opciones_campo_select();

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>'Existencias']
            ];

        return view('inventarios.existencias',compact('bodegas','items','grupo_inventario','miga_pan'));
    }

    public function ajax_existencias(Request $request)
    {
        $obj = new FiltroMovimientos();
        
        $movin_filtrado = $obj->aplicar_filtros( null, $request->fecha_corte, $request->mov_bodega_id, $request->grupo_inventario_id, $request->item_id );

        $view = $this->get_vista_inv_movimiento_corte( $movin_filtrado, $request->mostrar_costo, $request->mostrar_cantidad, $request->fecha_corte );

        Cache::put( 'pdf_reporte_inv_existencias_corte', $view, 720 ); // 720 minutos = 12 horas
        
        return $view;
    }

    public function get_vista_inv_movimiento_corte( $movin_filtrado, $mostrar_costo, $mostrar_cantidad, $fecha_corte )
    {
        $lista_items = array_keys($movin_filtrado->groupBy('inv_producto_id')->toArray() );
        $lista_bodegas = array_keys($movin_filtrado->groupBy('inv_bodega_id')->toArray() );
        
        $productos = [];
        $i = 0;
        foreach ( $lista_items as $key => $item_id )
        {
            $item = InvProducto::find( $item_id );

            $stock_serv = new StockAmountService();

            $total_cantidad_item = 0;
            $total_costo_item = 0;
            foreach ( $lista_bodegas as $key2 => $bodega_id )
            {
                $productos[$i]['id'] = $item_id;
                $productos[$i]['descripcion'] = $item->descripcion;
                $productos[$i]['unidad_medida1'] = $item->unidad_medida1;
                $productos[$i]['unidad_medida2'] = $item->unidad_medida2;
                $bodega = InvBodega::find( $bodega_id );
                $productos[$i]['bodega'] = $bodega->descripcion;

                $productos[$i]['Cantidad'] = $stock_serv->get_stock_amount_item($bodega_id, $item_id, $fecha_corte);

                $productos[$i]['Costo'] = $stock_serv->get_total_cost_amount_item($bodega_id, $item_id, $fecha_corte);

                if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 0)
                {
                    $productos[$i]['Costo'] = $item->get_costo_promedio( 0 ) * $productos[$i]['Cantidad'];
                }

                $total_cantidad_item += $productos[$i]['Cantidad'];
                $total_costo_item += $productos[$i]['Costo'];
            
                $i++;
            }

            $productos[$i]['id'] = 0;
            $productos[$i]['descripcion'] = '';
            $productos[$i]['unidad_medida1'] = '';
            $productos[$i]['unidad_medida2'] = '';
            $productos[$i]['bodega'] = '';

            $productos[$i]['Cantidad'] = $total_cantidad_item;

            $productos[$i]['Costo'] = $total_costo_item;
            $i++;
        }

        switch( count($lista_bodegas) )
        {
            case '0':
                $bodega = "NINGUNA";
                break;
            case '1':
                $bodega = $bodega->descripcion;
                break;
            default:
                $bodega = "VARIAS";
                break;
        }

        $view_1 = View::make('inventarios.incluir.existencias_encabezado',compact('bodega','fecha_corte'));

        if ( $mostrar_costo ) {
            $view_2 = View::make('inventarios.incluir.existencias_tabla_con_costos',compact('bodega','productos'));
        }else{
            $view_2 = View::make('inventarios.incluir.existencias_tabla_sin_costos',compact('bodega','productos', 'mostrar_cantidad' ));
        }
            
        return $view_1.$view_2;
    }


    // FORMULARIO PARA GENERAR MOVIMIENTOS
    public function inv_movimiento()
    {
        $bodegas = InvBodega::opciones_campo_select();

        $productos = InvProducto::opciones_campo_select();

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>'Movimientos']
            ];

        return view('inventarios.movimientos',compact('productos','bodegas','miga_pan'));
    }

    //  CONSULTA DE MOVIMIENTOS
    public function ajax_movimiento(Request $request)
    {
        $id_producto = $request->mov_producto_id;
        $bodega_id = $request->mov_bodega_id;
        $fecha_inicial = $request->fecha_inicial;
        $fecha_final = $request->fecha_final;

        $saldo_inicial = InvMovimiento::get_saldo_inicial($id_producto, $bodega_id, $fecha_inicial );

        $sql_productos = InvMovimiento::get_movimiento2($id_producto, $bodega_id, $fecha_inicial, $fecha_final );

        $cantidad_saldo = 0;
        $costo_total_saldo = 0;  
        $costo_unit_saldo = 0;          
        if ( $saldo_inicial['mCantidad'] != 0 )
        {
            $cantidad_saldo = $saldo_inicial['mCantidad'];
            $costo_total_saldo = $saldo_inicial['mCosto'];
            $costo_unit_saldo = $saldo_inicial['mCosto'] / $saldo_inicial['mCantidad'];
        }

        $productos[0]['fecha'] = $fecha_inicial;
        $productos[0]['documento_id'] = '';
        $productos[0]['documento'] = '';
        $productos[0]['tercero'] = '';
        $productos[0]['cantidad_in'] = '';
        $productos[0]['costo_unit_in'] = '';
        $productos[0]['costo_total_in'] = '';
        $productos[0]['cantidad_out'] = '';
        $productos[0]['costo_unit_out'] = '';
        $productos[0]['costo_total_out'] = '';
        $productos[0]['cantidad_saldo'] = $cantidad_saldo;
        $productos[0]['costo_unit_saldo'] = $costo_unit_saldo;
        $productos[0]['costo_total_saldo'] = $costo_total_saldo;
        $productos[0]['core_tipo_transaccion_id'] = '';

        $i=1;
        foreach ($sql_productos as $fila)
        {
            $productos[$i]['fecha'] = $fila->fecha;
            // Se obtinen las descripciones de los datos del encabezado
            $sql_datos_encabezado_doc = InvDocEncabezado::get_registro2($fila->core_tipo_transaccion_id,$fila->core_tipo_doc_app_id,$fila->consecutivo);

            $datos_encabezado_doc =  $sql_datos_encabezado_doc[0];
            $productos[$i]['documento_id'] = $datos_encabezado_doc['campo9'];
            $productos[$i]['documento'] = $datos_encabezado_doc['campo2'];
            $productos[$i]['tercero'] = $datos_encabezado_doc['campo3'];

            switch ($fila->movimiento) {
                case 'entrada':
                    $productos[$i]['cantidad_in'] = $fila->cantidad;
                    $productos[$i]['costo_unit_in'] = $fila->costo_unitario;
                    $productos[$i]['costo_total_in'] = $fila->costo_total;
                    $productos[$i]['cantidad_out'] = '';
                    $productos[$i]['costo_unit_out'] = '';
                    $productos[$i]['costo_total_out'] = '';
                    break;
                case 'salida':
                    $productos[$i]['cantidad_in'] = '';
                    $productos[$i]['costo_unit_in'] = '';
                    $productos[$i]['costo_total_in'] = '';
                    $productos[$i]['cantidad_out'] = $fila->cantidad * -1;
                    $productos[$i]['costo_unit_out'] = $fila->costo_unitario;
                    $productos[$i]['costo_total_out'] = $fila->costo_total * -1;
                    break;
                
                default:
                    # code...
                    break;
            }

            $cantidad_saldo += $fila->cantidad;
            $costo_unit_saldo = $fila->costo_unitario;
            $costo_total_saldo += $fila->costo_total;
            /*if ( $cantidad_saldo == 0 )
            {
                $costo_unit_saldo = 0;
            }*/
            if ( $cantidad_saldo != 0 )
            {
                $costo_unit_saldo = $costo_total_saldo / $cantidad_saldo; 
            }


            $productos[$i]['cantidad_saldo'] = $cantidad_saldo;
            $productos[$i]['costo_unit_saldo'] = $costo_unit_saldo;
            $productos[$i]['costo_total_saldo'] = $costo_total_saldo;


            $productos[$i]['core_tipo_transaccion_id'] = $fila->core_tipo_transaccion_id;

            $i++;
        }

        $bodega = InvBodega::find($bodega_id);

        $view = View::make('inventarios.incluir.movim_productos',compact('productos','bodega'));

        return $view;
    }


    
    // REPORTE STOCK MINIMO
    public function inv_stock_minimo()
    {
        $fecha_corte = date('Y-m-d');
        $bodega_id = 1;

        $bodegas = InvBodega::opciones_campo_select();

        $productos = MinStock::leftJoin('inv_productos','inv_productos.id','=','inv_min_stocks.inv_producto_id')->where('inv_bodega_id', $bodega_id)->select('inv_productos.id','inv_productos.descripcion','inv_productos.unidad_medida1','inv_min_stocks.inv_bodega_id','inv_min_stocks.stock_minimo')->orderBy('inv_productos.descripcion')->get();

        foreach ($productos as $producto) 
        {
            $producto->cantidad = InvMovimiento::get_existencia_producto($producto->id, $producto->inv_bodega_id, $fecha_corte )->Cantidad;
        }

        $tabla = View::make('inventarios.incluir.stock_minimo_tabla',compact( 'productos'));

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>'Reporte de Stock Mínimo']
            ];

        return view('inventarios.repo_stock_minimo',compact('bodegas','tabla','miga_pan'));
    }


    public function inv_etiquetas_codigos_barra(Request $request)
    {
        $grupo_inventario_id = $request->grupo_inventario_id;
        $mostrar_descripcion = $request->mostrar_descripcion;
        $numero_columnas = $request->numero_columnas;
        $estado = $request->estado;
        $etiqueta = $request->etiqueta;
        $items_a_mostrar = $request->items_a_mostrar;
                
        $items = InvProducto::get_datos_basicos( $grupo_inventario_id, $estado, $items_a_mostrar);

        $vista = View::make( 'inventarios.reportes.etiquetas_codigos_barra', compact('items', 'numero_columnas', 'mostrar_descripcion', 'etiqueta', 'items_a_mostrar') )->render();

        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;

    }


    public function balance_inventarios(Request $request)
    {
        $grupo_inventario_id = $request->grupo_inventario_id;
        $inv_bodega_id = $request->inv_bodega_id;
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta = $request->fecha_hasta;
        $mostrar_items_sin_movimiento = $request->mostrar_items_sin_movimiento;
                
        $items = InvProducto::get_datos_basicos( $grupo_inventario_id, 'Activo' );

        $saldos_items = InvMovimiento::get_saldos_iniciales_items( $grupo_inventario_id, $inv_bodega_id, $fecha_desde );

        $movimientos_entradas = InvMovimiento::get_suma_movimientos( $grupo_inventario_id, $inv_bodega_id, $fecha_desde, $fecha_hasta, 'entrada' );

        $movimientos_salidas = InvMovimiento::get_suma_movimientos( $grupo_inventario_id, $inv_bodega_id, $fecha_desde, $fecha_hasta, 'salida' );

        $vista = View::make( 'inventarios.reportes.balance_inventarios', compact('items', 'mostrar_items_sin_movimiento', 'saldos_items', 'movimientos_entradas', 'movimientos_salidas', 'fecha_desde', 'fecha_hasta', 'inv_bodega_id' ) )->render();

        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;

    }


    public function inv_existencias_corte(Request $request)
    {
        $fecha_corte = $request->fecha;
        $grupo_inventario_id = $request->grupo_inventario_id;
        $talla = $request->unidad_medida2;
        $inv_bodega_id = $request->inv_bodega_id;

        $array_wheres = [ ['inv_movimientos.fecha' ,'<=', $fecha_corte] ];

        if ( $grupo_inventario_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_grupos.id' => $grupo_inventario_id] );
        }

        if ( $talla != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_productos.unidad_medida2' => $talla] );
        }

        if ( $inv_bodega_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_movimientos.inv_bodega_id' => $inv_bodega_id] );
        }

        $productos = InvMovimiento::get_existencia_corte( $array_wheres );
      
        $vista = View::make( 'inventarios.incluir.existencias_tabla_con_talla', compact('productos') )->render();
        
        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;

    }

    
}
