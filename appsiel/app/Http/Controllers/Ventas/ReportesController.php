<?php

namespace App\Http\Controllers\Ventas;

use App\Core\Tercero;
use App\Core\TipoDocApp;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Ventas\Cliente;
use Input;
use DB;
use Auth;
use Form;
use Lava;

use App\Ventas\VtasMovimiento;
use App\Ventas\VtasPedido;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvDocEncabezado;

use App\Contabilidad\Impuesto;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class ReportesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function grafica_ventas_diarias($fecha_inicial, $fecha_final)
    {
        $registros = VtasMovimiento::mov_ventas_totales_por_fecha( $fecha_inicial, $fecha_final );

        // Gráfica de rendimiento académico
        $stocksTable1 = Lava::DataTable();
      
        $stocksTable1->addStringColumn('Ventas')
                    ->addNumberColumn('$');

        $i = 0;
        $tabla = [];
        foreach ($registros as $linea) 
        {
            $fecha  = date("d-m-Y", strtotime("$linea->fecha"));

            $stocksTable1->addRow( [ $linea->fecha, (float)$linea->total_ventas_netas ]);

            $tabla[$i]['fecha'] = $linea->fecha;
            $tabla[$i]['valor'] = (float)$linea->total_ventas_netas;
            $i++;
        }

        // Se almacena la gráfica en ventas_diarias, luego se llama en la vista [ como mágia :) ]
        Lava::BarChart('ventas_diarias', $stocksTable1, [
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

    public function precio_venta_por_producto(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $inv_producto_id = $request->inv_producto_id;
        $operador1 = '=';

        $cliente_id = $request->cliente_id;
        $operador2 = '=';

        if ($request->inv_producto_id == '') {
            $operador1 = 'LIKE';
            $inv_producto_id = '%' . $request->inv_producto_id . '%';
        }

        if ($request->cliente_id == '') {
            $operador2 = 'LIKE';
            $cliente_id = '%' . $request->cliente_id . '%';
        }

        $movimiento = VtasMovimiento::get_precios_ventas($fecha_desde, $fecha_hasta, $inv_producto_id, $operador1, $cliente_id, $operador2);

        $vista = View::make('ventas.reportes.precio_venta', compact('movimiento'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }


    public function vtas_reporte_ventas(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $agrupar_por = $request->agrupar_por;
        $detalla_productos  = (int)$request->detalla_productos;
        $detalla_clientes  = (int)$request->detalla_clientes;
        $iva_incluido  = (int)$request->iva_incluido;

        $movimiento = VtasMovimiento::get_movimiento_ventas($fecha_desde, $fecha_hasta, $agrupar_por);

        //dd( $movimiento->toArray() );

        // En el movimiento se trae el precio_total con IVA incluido
        $mensaje = 'IVA Incluido en precio';
        if ( !$iva_incluido )
        {
            $mensaje = 'IVA <b>NO</b> incluido en precio';
        }

        $vista = View::make('ventas.reportes.reporte_ventas_ordenado', compact('movimiento','agrupar_por','mensaje','iva_incluido','detalla_productos'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }


    public function vtas_reporte_rentabilidad(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $agrupar_por = $request->agrupar_por;

        $movimiento = VtasMovimiento::get_movimiento_ventas($fecha_desde, $fecha_hasta, $agrupar_por);
        
        $movimiento_inventarios = InvMovimiento::get_movimiento_transacciones_ventas( $fecha_desde, $fecha_hasta );

        $mensaje = 'IVA <b>NO</b> incluido en precio';

        $vista = View::make('ventas.reportes.reporte_rentabilidad_ordenado', compact( 'movimiento', 'movimiento_inventarios', 'agrupar_por', 'mensaje') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    /*
    Reporte de pedidos de venta vencidos
    */
    public static function pedidos_vencidos()
    {
        $parametros = config('ventas');
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $pedidos_db = VtasPedido::where('core_empresa_id', Auth::user()->empresa_id)
                                ->where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', '<', $fecha], ['estado', 'Pendiente']])->get();
        $pedidos = null;
        if (count($pedidos_db) > 0) {
            foreach ($pedidos_db as $o) {
                $pedidos[] = ReportesController::prepara_datos($o);
            }
        }
        return $pedidos;
    }

    /*
    Reporte de pedidos de ventas futuros
    */
    public static function pedidos_futuros()
    {
        $parametros = config('ventas');
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $inicio = date("Y-m-d",strtotime($fecha.'sunday this week'));
        $pedidos_db = VtasPedido::where('core_empresa_id', Auth::user()->empresa_id)
                                ->where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', '>', $inicio], ['estado', 'Pendiente']])->get();
        $pedidos = null;
        if (count($pedidos_db) > 0) {
            foreach ($pedidos_db as $o) {
                $pedidos[] = ReportesController::prepara_datos($o);
            }
        }
        return $pedidos;
    }

    /*
    Reporte de pedidos de ventas futuros
    */
    public static function pedidos_anulados()
    {
        $parametros = config('ventas');
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $inicio = date("Y-m-d",strtotime($fecha."- 7 days")); 
        $pedidos_db = VtasPedido::where('core_empresa_id', Auth::user()->empresa_id)
                                ->where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', '>', $inicio], ['estado', 'Anulado']])->take(10)->get();
        $pedidos = null;
        if (count($pedidos_db) > 0) {
            foreach ($pedidos_db as $o) {
                $pedidos[] = ReportesController::prepara_datos($o);
            }
        }
        return $pedidos;
    }

    /*
    Reporte de pendientes de la semana
    */
    public static function pedidos_semana()
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
        $parametros = config('ventas');

        foreach ($fechas as $f) {

            $diff_fecha = date_create($fecha) > date_create($f);
            
            if(!$diff_fecha){                
                $pedidos_db = VtasPedido::where('core_empresa_id', Auth::user()->empresa_id)
                                ->where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', 'like','%'.$f.'%'],['estado', 'Pendiente']])->orWhere([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', 'like','%'.$f.'%'],['estado', 'Cumplido']])->get();
            }else{
                $pedidos_db = VtasPedido::where('core_empresa_id', Auth::user()->empresa_id)
                                ->where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', 'like','%'.$f.'%'],['estado', 'Cumplido']])->get();
            }
            $pedidos = null;            

            if (count($pedidos_db) > 0) {
                foreach ($pedidos_db as $o) {
                    $pedidos[] = ReportesController::prepara_datos($o); 
                }
            }
            $data[] = [
                'fecha' => date_format(date_create($f), 'd-m-Y'),
                'data' => $pedidos
            ];
        }
        return $data;
    }
    
    //Prepara los datos a mostrar del pedido de venta
    public static function prepara_datos($o)
    {
        $p = Cliente::find($o->cliente_id);
        $tercero = Tercero::find($p->core_tercero_id);
        $cliente = $tercero->razon_social;
        if ($cliente == "") {
            $cliente = $tercero->descripcion;
        }
        $orden = [
            'id' => $o->id,
            'documento' => TipoDocApp::find($o->core_tipo_doc_app_id)->prefijo . " " . $o->consecutivo,
            'cliente' => $cliente,
            'fecha' => date_format(date_create($o->fecha), 'd-m-Y'),
            'fecha_entrega' => date_format(date_create($o->fecha_entrega), 'd-m-Y'),
            'estado' => $o->estado
        ];
        return $orden;
    }

    /*
    Reporte de pendientes de la semana
    */
    public static function pedidos_hoy()
    {
        $hoy = getdate();
        $fecha = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $date2 = strtotime($fecha);
        $inicio = date('Y-m-d', $date2);
        $fechahoy  = date("Y-m-d", strtotime("$inicio"));

        $data = null;
        $parametros = config('ventas');

        $pedidos_db = VtasPedido::where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', 'like','%'.$fechahoy.'%'], ['estado', 'Pendiente']])->get();
        //dd($pedidos_db);
        $pedidos = null;
        if (count($pedidos_db) > 0) {
            foreach ($pedidos_db as $o) {
                $pedidos[] = ReportesController::prepara_datos($o);
            }
        }

        return $pedidos;
    }


    public static function remisiones_pendientes_por_facturar()
    {
        return InvDocEncabezado::where([
                                        ['estado','Pendiente'],
                                        ['core_tipo_transaccion_id',24]
                                    ])
                                ->get();
    }

    public function remisiones_estado_facturadas_sin_factura_real(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $records = InvDocEncabezado::where([
                                        ['estado','Facturada'],
                                        ['core_tipo_transaccion_id',24]
                                    ])
                                ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                                ->get();
        
        $remisiones = [];                    
        foreach ($records as $record) {
            $fact_vta = VtasDocEncabezado::where([['remision_doc_encabezado_id','LIKE','%'.$record->id.'%']])->get()->first();
            
            if ($fact_vta == null ) {
                $fact_pos = FacturaPos::where([['remision_doc_encabezado_id','LIKE','%'.$record->id.'%']])->get()->first();
                if ($fact_pos == null ) {
                    $remisiones[] = $record;
                }
            }            
        }

        $titulo = 'Remisiones en estado Facturada, pero SIN factura relacionada';

        $vista = View::make('ventas.incluir.lista_remisiones', compact('titulo','remisiones'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;     
    }

    public function ventas_por_vendedor(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $agrupar_por = 'vendedor_id';
        $detalla_productos  = (int)$request->detalla_productos;
        $iva_incluido  = (int)$request->iva_incluido;

        $movimiento = VtasMovimiento::get_movimiento_ventas($fecha_desde, $fecha_hasta, $agrupar_por);

        // En el movimiento se trae el precio_total con IVA incluido
        $mensaje = 'IVA Incluido en precio';
        if ( !$iva_incluido )
        {
            $mensaje = 'IVA <b>NO</b> incluido en precio';
        }

        $vista = View::make('ventas.reportes.ventas_por_vendedor', compact('movimiento','agrupar_por','mensaje','iva_incluido','detalla_productos'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }


    public function lineas_de_movimiento_repetidas(Request $request)
    {
        $fecha_desde = $request->fecha_desde;
        $fecha_hasta  = $request->fecha_hasta;

        $movimientos_ventas = VtasMovimiento::get_movimiento_entre_fechas($fecha_desde, $fecha_hasta);

        $movimiento_inventarios = InvMovimiento::get_movimiento_transacciones_ventas( $fecha_desde, $fecha_hasta );

        $resumen_ventas = collect([]);
        $arr_registros_unicos = [];
        foreach ($movimientos_ventas as $linea_movimiento) {
            $llave = $linea_movimiento->remision_doc_encabezado_id . $linea_movimiento->inv_producto_id;
            if (in_array($llave,$arr_registros_unicos)) {
                continue;
            }

            $cant_venta = abs( $linea_movimiento->where( 'remision_doc_encabezado_id', $linea_movimiento->remision_doc_encabezado_id )
                    ->where( 'inv_producto_id', $linea_movimiento->inv_producto_id )
                    ->sum('cantidad') );

            $cant_inventario = abs( $movimiento_inventarios->where( 'inv_doc_encabezado_id', $linea_movimiento->remision_doc_encabezado_id )
                    ->where( 'inv_producto_id', $linea_movimiento->inv_producto_id )
                    ->sum('cantidad') );

            $resumen_ventas->push([
                'fecha' => $linea_movimiento->fecha,
                'doc_ventas' => $linea_movimiento->get_label_documento(),
                'item' => $linea_movimiento->producto->descripcion,
                'cant_venta' => $cant_venta,
                'cant_inventario' => $cant_inventario,
                'diferencia' => $cant_venta - $cant_inventario
            ]);

            $arr_registros_unicos[] = $llave;
        }
        
        $mensaje = 'IVA <b>NO</b> incluido en precio';

        $vista = View::make('ventas.reportes.lineas_de_movimiento_repetidas', compact( 'resumen_ventas',  'mensaje') )->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

}
