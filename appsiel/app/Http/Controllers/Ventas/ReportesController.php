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

class ReportesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public static function grafica_ventas_diarias($fecha_inicial, $fecha_final)
    {
        $registros = VtasMovimiento::whereBetween('fecha',[$fecha_inicial, $fecha_final])
                                        ->select(DB::raw('SUM(base_impuesto_total) as total_ventas'),'fecha')
                                        ->groupBy('fecha')
                                        ->orderBy('fecha')
                                        ->get();

        // Gráfica de rendimiento académico
        $stocksTable1 = Lava::DataTable();
      
        $stocksTable1->addStringColumn('Ventas')
                    ->addNumberColumn('Fecha');

        $i = 0;
        $tabla = [];
        foreach ($registros as $linea) 
        {
            $stocksTable1->addRow( [ $linea->fecha, (float)$linea->total_ventas ]);

            $tabla[$i]['fecha'] = $linea->fecha;
            $tabla[$i]['valor'] = (float)$linea->total_ventas;
            $i++;
        }

        // Se almacena la gráfica en ventas_diarias, luego se llama en la vista [ como mágia :) ]
        Lava::BarChart('ventas_diarias', $stocksTable1,[
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

        if ( $request->inv_producto_id == '' )
        {
            $operador1 = 'LIKE';
            $inv_producto_id = '%'.$request->inv_producto_id.'%';
        }

        if ( $request->cliente_id == '' )
        {
            $operador2 = 'LIKE';
            $cliente_id = '%'.$request->cliente_id.'%';
        }

        $movimiento = VtasMovimiento::get_precios_ventas( $fecha_desde, $fecha_hasta, $inv_producto_id, $operador1, $cliente_id, $operador2 );

        //dd( $fecha_desde . ' * ' .  $fecha_hasta . ' * ' .  $inv_producto_id . ' * ' .  $operador1 . ' * ' .  $cliente_id . ' * ' .  $operador2 );

        //dd( $movimiento );

        $vista = View::make('ventas.reportes.precio_venta', compact('movimiento') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }
}
