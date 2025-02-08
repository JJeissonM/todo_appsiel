<?php

namespace App\Http\Controllers\Inventarios;

use App\Compras\ComprasMovimiento;
use App\Compras\Proveedor;
use App\Core\Tercero;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

use App\Inventarios\InvBodega;
use App\Inventarios\InvProducto;
use App\Inventarios\InvGrupo;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\MinStock;

use App\Inventarios\Services\FiltroMovimientos;

use App\Inventarios\RecetaCocina;
use App\Inventarios\Services\MovementService;
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

        $movimientos = InvMovimiento::get_movimiento_corte( $fecha_corte, '=', $id, 'LIKE', '%%');
      
        $bodega = InvBodega::find($id);

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>'Existencias']
            ];
        
        return view('inventarios.existencias_una_bodega',compact('movimientos','bodega','miga_pan','fecha_corte'));
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

        $bodegas = InvBodega::all();
        $items = InvProducto::all();
        
        $stock_serv = new StockAmountService();
        
        $productos = [];
        $i = 0;
        foreach ( $lista_items as $key => $item_id )
        {
            $item = $items->where( 'id',$item_id )->first();

            $total_cantidad_item = 0;
            $total_costo_item = 0;
            $aux = [];
            $cantidad_bodegas = 0;
            foreach ( $lista_bodegas as $key2 => $bodega_id )
            {
                $productos[$i]['id'] = $item_id;
                $productos[$i]['descripcion'] = $item->get_value_to_show(true);
                $productos[$i]['unidad_medida1'] = $item->unidad_medida1;
                $productos[$i]['unidad_medida2'] = $item->unidad_medida2;
                $productos[$i]['referencia'] = $item->referencia;

                $bodega = $bodegas->where( 'id',$bodega_id )->first();
                $descripcion_bodega = '';
                if ($bodega != null) {
                    $descripcion_bodega = $bodega->descripcion;
                }
                $productos[$i]['bodega'] = $descripcion_bodega;

                //$productos[$i]['Cantidad'] = $movin_filtrado->where('inv_bodega_id', $bodega_id)->where('inv_producto_id', $item_id)->sum('cantidad');
                $productos[$i]['Cantidad'] = $stock_serv->get_stock_amount_item($bodega_id, $item_id, $fecha_corte);
                
                $productos[$i]['CostoPromedio'] = 0;

                if ($productos[$i]['Cantidad'] == 0) {
                    continue;
                }

                $productos[$i]['Costo'] = $stock_serv->get_total_cost_amount_item($bodega_id, $item_id, $fecha_corte);

                //$productos[$i]['Costo'] = $movin_filtrado->where('inv_bodega_id', $bodega_id)->where('inv_producto_id', $item_id)->sum('costo_total');

                $total_cantidad_item += $productos[$i]['Cantidad'];
                $total_costo_item += $productos[$i]['Costo'];
            
                $i++;
                $cantidad_bodegas++;
            }

            $costo_promedio = 0;
            if ($total_cantidad_item != 0) {
                $costo_promedio = $total_costo_item / $total_cantidad_item;
                for ($i2=$cantidad_bodegas; $i2 > 0; $i2--) {
                    if (isset($productos[$i - $i2]['CostoPromedio'])) {
                        $productos[$i - $i2]['CostoPromedio'] = $costo_promedio;
                    }                    
                }                    
            }            

            $productos[$i]['id'] = 0;
            $productos[$i]['descripcion'] = '';
            $productos[$i]['unidad_medida1'] = '';
            $productos[$i]['unidad_medida2'] = '';
            $productos[$i]['bodega'] = '';

            $productos[$i]['Cantidad'] = $total_cantidad_item;
            $productos[$i]['CostoPromedio'] = $costo_promedio;

            $productos[$i]['Costo'] = $total_costo_item;

            $i++;
        }

        switch( count($lista_bodegas) )
        {
            case '0':
                $bodega = "NINGUNA";
                break;
            case '1':
                $bodega = $descripcion_bodega;
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

    public function get_total_cost_amount_item($movin_filtrado, $inv_bodega_id, $inv_producto_id)
    {
        
        $filtered1 = $movin_filtrado->where('inv_bodega_id', $inv_bodega_id)->where('inv_producto_id', $inv_producto_id);

        /*
        $filtered2 = $filtered1->filter(function ($item) use ($deadline_date) { 
            return $item->fecha <= $deadline_date;
        });
        */
        
        $costo_total = $filtered1->sum('costo_total');

        if ($costo_total == null) {
            return 0;
        }

        return $costo_total;
    }

    // FORMULARIO PARA GENERAR MOVIMIENTOS
    public function inv_movimiento()
    {
        $bodegas = InvBodega::opciones_campo_select();

        $productos = InvProducto::opciones_campo_select();

        $terceros = Tercero::opciones_campo_select();

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>'Movimientos']
            ];

        return view('inventarios.movimientos',compact('productos', 'bodegas', 'terceros', 'miga_pan'));
    }

    //  CONSULTA DE MOVIMIENTOS
    public function ajax_movimiento(Request $request)
    {
        $id_producto = $request->mov_producto_id;
        $bodega_id = $request->mov_bodega_id;
        $fecha_inicial = $request->fecha_inicial;
        $fecha_final = $request->fecha_final;
        $tercero_id = (int)$request->mov_tercero_id;

        $saldo_inicial = InvMovimiento::get_saldo_inicial($id_producto, $bodega_id, $fecha_inicial, $tercero_id );

        $sql_productos = InvMovimiento::get_movimiento2($id_producto, $bodega_id, $fecha_inicial, $fecha_final, $tercero_id );
        
        $cantidad_saldo = 0;
        $costo_total_saldo = 0;  
        $costo_unit_saldo = 0;          
        if ( round($saldo_inicial['mCantidad'], 2) != 0 )
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

            if (!isset($sql_datos_encabezado_doc[0])) {
                dd('Error en la línea del movimiento. No se pudo obtener los datos del encabezado: ','tipo_transaccion_id: ' . $fila->core_tipo_transaccion_id, 'tipo_doc_app_id: ' . $fila->core_tipo_doc_app_id, 'consecutivo: ' . $fila->consecutivo,$sql_datos_encabezado_doc);
            }

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
            $costo_total_saldo += $fila->cantidad * $fila->costo_unitario;
                        
            if (  round($cantidad_saldo, 2) != 0 )
            {
                $costo_unit_saldo = $costo_total_saldo / $cantidad_saldo; 
            }else{
                $costo_total_saldo = 0;
            }

            $productos[$i]['cantidad_saldo'] = $cantidad_saldo;
            $productos[$i]['costo_unit_saldo'] = $costo_unit_saldo;
            $productos[$i]['costo_total_saldo'] = $costo_total_saldo;

            $productos[$i]['core_tipo_transaccion_id'] = $fila->core_tipo_transaccion_id;

            $i++;
        }

        $bodega = InvBodega::find($bodega_id);

        $mensaje_advertencia = '';
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 0 ) {
            $mensaje_advertencia = 'El sistema maneja un solo costo para todas las bodegas. El costo VISUALIZADO en el Saldo total puede parecer incoherente. Debe revisar el costo promedio en otro reporte.';
        }

        if ( $tercero_id != 0 ) {
            $mensaje_advertencia .= '<br><br><b>Nota: </b> Al seleccionar un TERCERO, los saldos y el costo promedio pueden ser diferentes a las Existencias totales del producto.';
        }

        $view = View::make('inventarios.incluir.movim_productos',compact('productos','bodega','mensaje_advertencia'));

        return $view;
    }

    

    // FORMULARIO STOCK MINIMO
    public function form_stock_minimo()
    {
        $bodegas = InvBodega::opciones_campo_select();

        $productos = InvProducto::opciones_campo_select();

        $proveedores = Proveedor::opciones_campo_select();

        $miga_pan = [
            ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
            ['url'=>'NO','etiqueta'=>'Reporte de Stock Mínimo']
        ];

        $tabla = '';

        if ( Input::get('show_table') == 'true' ) {
            $tabla = $this->get_tabla_stock_minimo();
        }

        return view('inventarios.reportes.stock_minimo.form',compact('bodegas', 'proveedores', 'miga_pan', 'tabla'));
    }
    
    // REPORTE STOCK MINIMO
    public function get_tabla_stock_minimo()
    {
        $fecha_corte = date('Y-m-d');

        $bodega_id = Input::get('bodega_id');
        $proveedor_id = Input::get('proveedor_id');
        $detalla_proveedor = Input::get('detalla_proveedor');

        $array_wheres = [
            ['inv_min_stocks.id', '>', 0]
        ]; // Todos

        $bodega = 'Todas';
        if ( $bodega_id != null || $bodega_id != '' && !$detalla_proveedor) {

            $bodega = InvBodega::find( $bodega_id )->descripcion;

            $array_wheres = array_merge( $array_wheres, [
                ['inv_min_stocks.inv_bodega_id', '=', $bodega_id]
            ]);
        }

        if ( $proveedor_id != null || $proveedor_id != '') {
            $array_wheres = array_merge( $array_wheres, [
                ['inv_productos.categoria_id', '=', $proveedor_id]
            ]);
        }

        if ($detalla_proveedor) {
            $productos = $this->get_productos_proveedor_detallado($array_wheres, $bodega_id, $fecha_corte);
        }else{
            $productos = $this->get_productos_agrupados($array_wheres, $fecha_corte);
        }

        return View::make('inventarios.reportes.stock_minimo.content', compact( 'productos', 'bodega', 'fecha_corte') );
    }

    public function get_productos_agrupados($array_wheres, $fecha_corte)
    {
        $registros_min_stock = MinStock::leftJoin('inv_productos','inv_productos.id','=','inv_min_stocks.inv_producto_id')
                            ->where($array_wheres)
                            ->get()
                            ->groupBy('descripcion');

        $productos = [];
        foreach ($registros_min_stock as $item_descripcion => $grupo) 
        {

            $aux = [
                'item_id' => '',
                'item_descripcion' => $item_descripcion,
                'bodega_descripcion' => ''
            ];

            $stock_minimo = 0;
            $cantidad = 0;
            $arr_inv_producto_id_contados = [];
            foreach ($grupo as $linea) {
                
                if (in_array( $linea->inv_producto_id, $arr_inv_producto_id_contados)) {
                    continue;
                }

                $stock_minimo += $linea->stock_minimo;

                $cantidad += InvMovimiento::get_existencia_producto($linea->inv_producto_id, '', $fecha_corte )->Cantidad;

                $arr_inv_producto_id_contados[] = $linea->inv_producto_id;
            }
            
            $aux['stock_minimo'] = $stock_minimo;
            $aux['cantidad'] = $cantidad;

            $productos[] = (object)$aux;
        }

        return $productos;
    }

    public function get_productos_proveedor_detallado($array_wheres, $bodega_id, $fecha_corte)
    {
        $registros_min_stock = MinStock::leftJoin('inv_productos','inv_productos.id','=','inv_min_stocks.inv_producto_id')
                            ->where($array_wheres)
                            ->get();

        $productos = [];
        foreach ($registros_min_stock as $registro) 
        {
            $bodega = $registro->bodega;
            $bodega_descripcion = '';
            if ( $bodega != null ) {
                $bodega_descripcion = $bodega->descripcion;
                $bodega_id = $bodega->id;
            }

            $productos[] = (object)[
                'item_id' => $registro->inv_producto_id,
                'item_descripcion' => $registro->item->get_value_to_show( true ),
                'bodega_descripcion' => $bodega_descripcion,
                'stock_minimo' => $registro->stock_minimo,
                'cantidad' => InvMovimiento::get_existencia_producto($registro->inv_producto_id, $bodega_id, $fecha_corte )->Cantidad
            ];
        }

        return $productos;
    }

    public function inv_etiquetas_codigos_barra(Request $request)
    {
        $grupo_inventario_id = $request->grupo_inventario_id;
        $inv_producto_id = $request->inv_producto_id;
        $mostrar_descripcion = $request->mostrar_descripcion;
        $numero_columnas = $request->numero_columnas;
        $estado = $request->estado;
        $etiqueta = $request->etiqueta;
        $items_a_mostrar = $request->items_a_mostrar;
        $cantidad_etiquetas_x_item = $request->cantidad_etiquetas_x_item;
        $cantidad_etiquetas_fijas = $request->cantidad_etiquetas_fijas;
        $ancho = $request->ancho;
        $alto = $request->alto;
        $tamanio_letra = $request->tamanio_letra;
                
        $items = $this->get_etiquetas_items( $grupo_inventario_id, $estado, $items_a_mostrar, $cantidad_etiquetas_x_item, $cantidad_etiquetas_fijas, $inv_producto_id );

        $route = 'inventarios.reportes.etiquetas_codigos_barra';
        if ( $request->tam_hoja == 'pos_80mm' ) {
            $route = 'inventarios.reportes.pos_80mm.etiquetas_codigos_barra';
        }

        $vista = View::make( $route, compact('items', 'numero_columnas', 'mostrar_descripcion', 'etiqueta', 'items_a_mostrar','cantidad_etiquetas_x_item','ancho','alto', 'tamanio_letra') )->render();

        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;

    }

    public function get_etiquetas_items($grupo_inventario_id, $estado, $items_a_mostrar, $cantidad_etiquetas_x_item, $cantidad_etiquetas_fijas, $inv_producto_id)
    {
        $items = InvProducto::get_datos_basicos_ordenados( $grupo_inventario_id, $estado, $items_a_mostrar,null,'inv_productos.id', $inv_producto_id);

        $listado = collect([]);
        foreach ($items as $item) {

            switch ($cantidad_etiquetas_x_item) {
                case 'una':
                    $cantidad_etiquetas = 1;
                    break;
                
                case 'segun_existencias':
                    $movim = InvMovimiento::get_existencia_corte( [ 
                        ['inv_movimientos.fecha' ,'<=', date('Y-m-d')],
                        ['inv_movimientos.inv_producto_id', '=', $item->id]
                    ] );
                    
                    $cantidad_etiquetas = 1;

                    if ($movim->first() != null) {
                        $cantidad_etiquetas = $movim->first()->suma_cantidad;
                    }
                    
                    break;
                
                case 'segun_ultima_factura_compras':
                    $ultima_compra = ComprasMovimiento::get_ultimo_precio_producto(null, $item->id, null);
                    $cantidad_etiquetas = 1;
                    if ( $ultima_compra->core_tipo_transaccion_id != null ) {
                        $cantidad_etiquetas = $ultima_compra->cantidad;
                    }
                    
                    break;
            
                case 'cantidad_fija':
                    $cantidad_etiquetas = $cantidad_etiquetas_fijas;                    
                    break;
                            
                default:
                    # code...
                    break;
            }



            for ($i=0; $i < $cantidad_etiquetas; $i++) { 
                $listado->push($item);
            }
        }

        return $listado;
    }

    public function inv_etiquetas_referencias(Request $request)
    {
        $grupo_inventario_id = $request->grupo_inventario_id;
        $mostrar_descripcion = $request->mostrar_descripcion;
        $numero_columnas = $request->numero_columnas;
        $estado = 'Activo';
        $etiqueta = $request->etiqueta;
        $items_a_mostrar = $request->items_a_mostrar;

        $mostrar_precio_ventas = $request->mostrar_precio_ventas;

        $array_wheres = [ ['id' ,'>', 0] ];

        if ( $grupo_inventario_id != '' &&  $grupo_inventario_id != null)
        {
            $array_wheres = array_merge( $array_wheres, [['inv_grupo_id','=', $grupo_inventario_id]] );
        }

        $items = InvProducto::where( $array_wheres )->get();

        $vista = View::make( 'inventarios.reportes.etiquetas_referencias', compact('items', 'numero_columnas', 'mostrar_descripcion', 'etiqueta', 'items_a_mostrar', 'mostrar_precio_ventas') )->render();

        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;

    }

    public function tabla_etiquetas_referencias($prefijo_referencia,$tipo_prenda,$tipo_material,$cantidad_items)
    {
        $referencia = $prefijo_referencia . $tipo_prenda . '%' . $tipo_material . '%';

        $array_wheres = [ ['id' ,'>', 0] ];

        $array_wheres = array_merge( $array_wheres, [['referencia','LIKE', $referencia]] );

        $items = InvProducto::where( $array_wheres )->get()->take($cantidad_items);

        $vista = View::make( 'inventarios.reportes.tabla_etiquetas_referencias', compact('items') )->render();
   
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

        if ( $inv_bodega_id == '' )
        {
            $title = 'Advertencia';
            $message = 'Debe selecciona una Bodega.';
            $vista = View::make( 'common.error_message', compact('title','message') )->render();    
            return $vista;
        }

        $array_wheres = [ 
            ['inv_movimientos.fecha', '<=', $fecha_corte],
            ['inv_productos.estado', '=', 'Activo']
        ];

        if ( $grupo_inventario_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, [['inv_grupos.id','=', $grupo_inventario_id]] );
        }

        if ( $talla != '' )
        {
            $array_wheres = array_merge( $array_wheres, [['inv_productos.unidad_medida2','=', $talla]] );
        }
        
        $array_wheres = array_merge( $array_wheres, [['inv_movimientos.inv_bodega_id','=', $inv_bodega_id]] );

        $movimientos = InvMovimiento::get_existencia_corte( $array_wheres );
      
        $vista = View::make( 'inventarios.incluir.existencias_tabla_con_talla', compact('movimientos') )->render();
        
        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;
    }

    public function movements_by_purpose(Request $request)
    {
        $init_date = $request->fecha_desde;
        $end_date = $request->fecha_hasta;
        $transaction_type_id = $request->transaction_type_id;
        $purpose_id = $request->purpose_id;

        $movements = (new MovementService())->movements_by_purpose($init_date,$end_date,$transaction_type_id,$purpose_id);
        //dd($movements);
        $vista = View::make( 'inventarios.incluir.movements_by_purposes', compact('movements') )->render();
        
        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );

        return $vista;
   
    }

    public function listado_recetas_e_ingredientes(Request $request)
    {        
		$platillos = RecetaCocina::groupBy('item_platillo_id')
                                ->get();

        $vista = '<div class="container-fluid"> <div class="container"> <br><br><br> <table class="table table-bordered table-striped" id="myTable"> <tr> <td>';
        foreach ($platillos as $platillo) {
            $ingredientes = $platillo->ingredientes();
            $vista .= View::make( 'inventarios.recetas.show_tabla_receta', compact('platillo','ingredientes') )->render();
        }
        
        $vista .= '</td> </tr> </table></div> </div>';

        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );

        return $vista;
   
    }
    
}
