<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Inventarios\InvMovimiento;

// Modelos
use App\VentasPos\Pdv;
use App\VentasPos\FacturaPos;


use App\Tesoreria\TesoCaja;

use App\Tesoreria\TesoMovimiento;
use App\Ventas\VtasMovimiento;
use App\Ventas\VtasPedido;
use App\VentasPos\Movimiento;
use App\VentasPos\Services\ReportsServices;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class ReporteController extends Controller
{

    public function get_saldos_caja_pdv( $pdv_id, $fecha_desde, $fecha_hasta )
    {
        $pdv = Pdv::find( $pdv_id );

        $encabezados_documentos = FacturaPos::where('pdv_id',$pdv_id)->where('estado','Pendiente')->get();

        $total_contado = $encabezados_documentos->where('forma_pago','contado')->sum('valor_total');
        $total_credito = $encabezados_documentos->where('forma_pago','credito')->sum('valor_total');

        $resumen_ventas = View::make( 'ventas_pos.resumen_ventas', compact( 'total_contado', 'total_credito' ) )->render();
        
        $vista_movimiento = $this->teso_movimiento_caja_pdv( $fecha_desde, $fecha_hasta, $pdv->caja_default_id );

        return $resumen_ventas . '<br><br>' . $vista_movimiento;

    }

    public function consultar_documentos_pendientes( $pdv_id, $fecha_desde, $fecha_hasta )
    {
        $pdv = Pdv::find( $pdv_id );

        $encabezados_documentos = FacturaPos::consultar_encabezados_documentos( $pdv_id, $fecha_desde, $fecha_hasta );

        $encabezados_documentos2 = FacturaPos::where( 'pdv_id', $pdv_id)->where( 'estado', 'Pendiente')->whereBetween( 'fecha', [$fecha_desde, $fecha_hasta] )->get();

        $view = Input::get('view');

        $tabla_encabezados_documentos = View::make( 'ventas_pos.tabla_encabezados_documentos', compact( 'encabezados_documentos', 'pdv','view' ) )->render();
        
        return $tabla_encabezados_documentos;

    }

    public function resumen_por_medios_recaudos( $encabezados_documentos )
    {
        foreach ( $encabezados_documentos as $documento )
        {
            $array_totales = $this->get_total_por_medios_recaudos( $documento->lineas_registros_medios_recaudos );
            dd( $array_totales[0] );
        }
    }

    public function get_total_por_medios_recaudos( $lineas_registros_medios_recaudos )
    {
        $array_totales = [];
        $lineas_recaudos = json_decode( $lineas_registros_medios_recaudos );

        if ( !is_null( $lineas_recaudos ) )
        {
            $i = 0;
            foreach( $lineas_recaudos as $linea )
            {
                $array_totales[] = collect( ['medio_recaudo' => explode("-", $linea->teso_medio_recaudo_id)[1], 'total' => (float)substr($linea->valor, 1) ] );
            }
        }

        return $array_totales;

    }

    public function teso_movimiento_caja_pdv( $fecha_desde, $fecha_hasta, $teso_caja_id )
    {
        $teso_cuenta_bancaria_id = 0;

        $caja = TesoCaja::find( $teso_caja_id );
        $mensaje = $caja->descripcion;

        $saldo_inicial = TesoMovimiento::get_saldo_inicial( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde );

        $movimiento = TesoMovimiento::get_movimiento( $teso_caja_id, $teso_cuenta_bancaria_id, $fecha_desde, $fecha_hasta );

        $vista = View::make('tesoreria.reportes.movimiento_caja_bancos', compact( 'fecha_desde', 'fecha_hasta', 'saldo_inicial', 'movimiento', 'mensaje'))->render();

        return $vista;
    }

    public function revisar_pedidos_ventas( $pdv_id )
    {
        $pedidos = VtasPedido::where( 'estado', 'Pendiente' )
            ->whereIn( 'core_tipo_transaccion_id', [42, 60])
            ->orderBy('fecha','DESC')
            ->orderBy('created_at','DESC')->get();

        return View::make( 'ventas_pos.lista_pedidos_pendientes_tabla', compact( 'pedidos', 'pdv_id' ) )->render();
    }

    public function movimientos_ventas(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $agrupar_por = $request->agrupar_por;
        $detalla_productos  = (int)$request->detalla_productos;
        $detalla_clientes  = (int)$request->detalla_clientes;
        $iva_incluido  = (int)$request->iva_incluido;
        $pdv_id  = (int)$request->pdv_id;

        $pdv = Pdv::find($pdv_id);
        $user_cajero_pdv = null;
        if ( $pdv != null ) {
            if ($pdv->cajero != null) {
                $user_cajero_pdv = $pdv->cajero->email;
            }
        }

        $estado_facturas = 'Todos';//$request->estado_facturas;

        $movimiento_pos = Movimiento::get_movimiento_ventas($fecha_desde, $fecha_hasta, $agrupar_por, $estado_facturas, null, $pdv_id);
        
        $array_lista = [];
        $array_lista = $this->get_array_lista_registros($array_lista, $movimiento_pos, $agrupar_por, $detalla_productos, $iva_incluido, 'POS', $user_cajero_pdv);

        /**
         * 23 = Factura de venta
         * 38 = Nota crédito ventas
         * 41 = Nota crédito directa
         * 44 = Factura Médica
         * 49 = Factura de estudiantes
         * 50 = Facturación Masiva de estudiantes
         * 52 = Factura Electrónica de Ventas
         * 53 = Nota Crédito Electrónica de Ventas
         * 54 = Nota Débito Electrónica de Ventas
         * 55 = Factura Electrónica de Contingencia de Ventas
         */        
        $movimiento_vtas_no_pos = VtasMovimiento::get_movimiento_ventas_por_transaccion($fecha_desde, $fecha_hasta, $agrupar_por,[23, 38, 41, 44, 49, 50, 52, 53, 54, 55]);

        $array_lista = $this->get_array_lista_registros($array_lista, $movimiento_vtas_no_pos, $agrupar_por, $detalla_productos, $iva_incluido, 'Estandar_FE', $user_cajero_pdv);

        // En el movimiento se trae el precio_total con IVA incluido
        $mensaje = 'IVA Incluido en precio';
        if ( !$iva_incluido )
        {
            $mensaje = 'IVA <b>NO</b> incluido en precio';
        }

        $vista = View::make('ventas_pos.reportes.reporte_ventas_ordenado', compact('array_lista','agrupar_por','mensaje','iva_incluido','detalla_productos','pdv'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function get_array_lista_registros($array_lista, $movimiento, $agrupar_por, $detalla_productos, $iva_incluido, $app_movimiento, $user_cajero_pdv)
    {
        $i = count($array_lista);

        foreach( $movimiento as $campo_agrupado => $coleccion_movimiento)
        {

            $cantidad = $coleccion_movimiento->sum('cantidad');
            $precio_total = $coleccion_movimiento->sum('precio_total');
            $base_impuesto_total = $coleccion_movimiento->sum('base_impuesto_total');

            if ( $user_cajero_pdv != null && $app_movimiento == 'Estandar_FE' ) {
                $cantidad = $coleccion_movimiento->where('creado_por', $user_cajero_pdv)->sum('cantidad');
                $precio_total = $coleccion_movimiento->where('creado_por', $user_cajero_pdv)->sum('precio_total');
                $base_impuesto_total = $coleccion_movimiento->where('creado_por', $user_cajero_pdv)->sum('base_impuesto_total');
            }
            
            $array_lista[$i]['descripcion'] = $campo_agrupado;
            if ( $app_movimiento == 'Estandar_FE' ) {
                $array_lista[$i]['descripcion'] = 'Ventas Estándar/Electrónica/Notas';
            }else{
                if ($agrupar_por=='pdv_id') {
                    $array_lista[$i]['descripcion'] = $coleccion_movimiento->first()->pdv->descripcion;
                }
                if ($agrupar_por=='inv_grupo_id') {
                    if ($coleccion_movimiento->first()->categoria_item()!=null) {
                        $array_lista[$i]['descripcion'] = $coleccion_movimiento->first()->categoria_item()->descripcion;
                    }
                }
            }
            
            $array_lista[$i]['cantidad'] = $cantidad;

            if ( $iva_incluido )
            {
                $precio = $precio_total;
            }else{
                $precio = $base_impuesto_total;
            }

            $precio_promedio = 0; 
            if( $cantidad != 0 )
            { 
                $precio_promedio = $precio / $cantidad; 
            }
            $array_lista[$i]['precio_promedio'] = $precio_promedio;
            $array_lista[$i]['precio'] = $precio;

            $array_lista[$i]['array_detalle_productos'] = [];

            if($detalla_productos)
            {
                $items = $coleccion_movimiento->groupBy('inv_producto_id');
                
                if ( $user_cajero_pdv != null && $app_movimiento == 'Estandar_FE' ) {
                    $items = $coleccion_movimiento->where('creado_por', $user_cajero_pdv)->groupBy('inv_producto_id');
                }
                
                $array_detalle_productos = [];
                $p = 0;

                $items_movim = collect([]);
                foreach( $items AS $item )
                {
                    $items_movim->push([
                        'item' => $item->first()->item,
                        'cantidad' => $item->sum('cantidad'),
                        'precio_total' => $item->sum('precio_total'),
                        'base_impuesto_total' => $item->sum('base_impuesto_total')
                    ]);
                }

                $ordered = $items_movim->sortByDesc('precio_total');

                foreach($ordered  AS $item_movim )
                {
                    $cantidad_item = $item_movim['cantidad'];

                    $array_detalle_productos[$p]['descripcion'] = $item_movim['item']->descripcion;
                    $array_detalle_productos[$p]['cantidad_item'] = $cantidad_item;

                    if ( $iva_incluido )
                    {
                        $precio_item = $item_movim['precio_total'];
                    }else{
                        $precio_item = $item_movim['base_impuesto_total'];
                    }

                    $precio_promedio_item = 0; 
                    if( $cantidad_item != 0 )
                    { 
                        $precio_promedio_item = $precio_item / $cantidad_item; 
                    }
                    
                    $array_detalle_productos[$p]['precio_promedio_item'] = $precio_promedio_item;
                    $array_detalle_productos[$p]['precio_item'] = $precio_item;
                    $p++;
                }
                $array_lista[$i]['array_detalle_productos'] = $array_detalle_productos;
            }

            $i++;
        }

        return $array_lista;
    }

    public function resumen_existencias(Request $request)
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
            ['inv_movimientos.fecha' ,'<=', $fecha_corte],
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
      
        $vista = View::make( 'ventas_pos.reportes.resumen_existencias', compact('movimientos') )->render();
        
        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;
    }

    public function comprobante_informe_diario(Request $request)
    {
        $fecha_corte = $request->fecha_corte;
        $pdv_id = $request->pdv_id;

        if ( $pdv_id == '' )
        {
            $title = 'Advertencia';
            $message = 'Debe selecciona un PDV.';
            $vista = View::make( 'common.error_message', compact('title','message') )->render();    
            return $vista;
        }

        $array_wheres = [ 
            ['vtas_pos_movimientos.fecha' ,'=', $fecha_corte],
            ['vtas_pos_movimientos.pdv_id', '=', $pdv_id]
        ];
        
        $movimientos = Movimiento::leftJoin('inv_productos','inv_productos.id','=','vtas_pos_movimientos.inv_producto_id')
                            ->leftJoin('inv_grupos','inv_grupos.id','=','inv_productos.inv_grupo_id')
                            ->where( $array_wheres )
                            ->select(
                                'vtas_pos_movimientos.*',
                                'inv_grupos.id AS item_category_id'
                            )
                            ->orderBy('vtas_pos_movimientos.consecutivo')
                            ->get();

        foreach ($movimientos as $fila)
        {
            $fila->base_impuesto_total = (float) $fila->precio_total / (1 + (float)$fila->tasa_impuesto / 100 );

            $fila->tasa_impuesto = (string)$fila->tasa_impuesto; // para poder agrupar
        }

        $service = new ReportsServices();

        $ventas_base_impuesto_total = $movimientos->sum('base_impuesto_total');

        $ventas_credito_pdv = $service->get_ventas_credito_pdv($pdv_id, $fecha_corte, $fecha_corte);
        $ventas_credito_sin_iva = $ventas_credito_pdv->sum('base_impuesto_total');
        
        $ventas_contado_sin_iva = $ventas_base_impuesto_total - $ventas_credito_sin_iva;

        $ventas_por_medios_pago_con_iva = $service->get_ventas_por_medios_pago_con_iva($pdv_id, $fecha_corte, $fecha_corte);
      
        $vista = View::make( 'ventas_pos.formatos_impresion.comprobante_informe_diario', compact('movimientos', 'ventas_por_medios_pago_con_iva', 'ventas_contado_sin_iva', 'ventas_credito_sin_iva') )->render();
        
        Cache::put( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista, 720 );
   
        return $vista;
    }

    public function get_facturas_con_lineas_registros_sin_movimiento($fecha_desde, $fecha_hasta)
    {
        $facturas = FacturaPos::whereBetween('fecha',[$fecha_desde,$fecha_hasta])
                                ->where([
                                    ['estado','<>','Anulado']
                                    ])
                                ->get();

        $data = [];
        foreach ($facturas as $factura) {
            $lineas_registros = $factura->lineas_registros;

            $movimientos = Movimiento::where([
                ['core_tipo_transaccion_id', '=', $factura->core_tipo_transaccion_id],
                ['core_tipo_doc_app_id', '=', $factura->core_tipo_doc_app_id],
                ['consecutivo', '=', $factura->consecutivo]
            ])
            ->get();

            if ( $lineas_registros->count() == $movimientos->count() ) {
                continue;
            }

            $data[] = [
                'url' => url('/ventas_pos_reconstruir_mov_ventas_documento' . '/' . $factura->id),
                'factura_id'=>$factura->id
            ];
        }

        return $data;
    }
}
