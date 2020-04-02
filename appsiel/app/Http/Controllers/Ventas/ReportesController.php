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
use View;
use Cache;
use Lava;

use App\Ventas\VtasMovimiento;
use App\Ventas\VtasPedido;

use App\Contabilidad\Impuesto;

class ReportesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function grafica_ventas_diarias($fecha_inicial, $fecha_final)
    {
        $registros = VtasMovimiento::whereBetween('fecha', [$fecha_inicial, $fecha_final])
            ->select(DB::raw('SUM(base_impuesto_total) as total_ventas'), 'fecha')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Gráfica de rendimiento académico
        $stocksTable1 = Lava::DataTable();

        $stocksTable1->addStringColumn('Ventas')
            ->addNumberColumn('Fecha');

        $i = 0;
        $tabla = [];
        foreach ($registros as $linea) {
            $stocksTable1->addRow([$linea->fecha, (float) $linea->total_ventas]);

            $tabla[$i]['fecha'] = $linea->fecha;
            $tabla[$i]['valor'] = (float) $linea->total_ventas;
            $i++;
        }

        // Se almacena la gráfica en ventas_diarias, luego se llama en la vista [ como mágia :) ]
        Lava::BarChart('ventas_diarias', $stocksTable1, [
            'is3D' => True,
            'orientation' => 'horizontal',
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

        //dd( $fecha_desde . ' * ' .  $fecha_hasta . ' * ' .  $inv_producto_id . ' * ' .  $operador1 . ' * ' .  $cliente_id . ' * ' .  $operador2 );

        //dd( $movimiento );

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

        $vista = View::make('ventas.reportes.reporte_ventas', compact('movimiento','agrupar_por','mensaje'))->render();

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
        $pedidos_db = VtasPedido::where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', '<', $fecha], ['estado', 'Pendiente']])->get();
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
        $pedidos_db = VtasPedido::where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', '>', $fecha], ['estado', 'Pendiente']])->get();
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
            $pedidos_db = VtasPedido::where([['core_tipo_doc_app_id', $parametros['pv_tipo_doc_app_id']], ['fecha_entrega', '=', $f], ['estado', 'Pendiente']])->get();
            $pedidos = null;
            if (count($pedidos_db) > 0) {
                foreach ($pedidos_db as $o) {
                    $pedidos[] = ReportesController::prepara_datos($o);
                }
            }
            $data[] = [
                'fecha' => $f,
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
            $cliente = $tercero->nombre1 . " " . $tercero->otros_nombres . " " . $tercero->apellido1 . " " . $tercero->apellido2;
        }
        $orden = [
            'id' => $o->id,
            'documento' => TipoDocApp::find($o->core_tipo_doc_app_id)->prefijo . " - " . $o->consecutivo,
            'cliente' => $cliente,
            'fecha_entrega' => $o->fecha_entrega
        ];
        return $orden;
    }
}
