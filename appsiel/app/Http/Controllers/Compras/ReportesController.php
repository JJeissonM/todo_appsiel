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

use App\CxP\DocumentosPendientes;


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
        
        $inv_producto_id = $request->inv_producto_id;
        $operador1 = '=';
        
        $proveedor_id = $request->proveedor_id;
        $operador2 = '=';

        if ( $request->inv_producto_id == '' )
        {
            $operador1 = 'LIKE';
            $inv_producto_id = '%'.$request->inv_producto_id.'%';
        }

        if ( $request->proveedor_id == '' )
        {
            $operador2 = 'LIKE';
            $proveedor_id = '%'.$request->proveedor_id.'%';
        }

        $movimiento = ComprasMovimiento::get_precios_compras( $fecha_desde, $fecha_hasta, $inv_producto_id, $operador1, $proveedor_id, $operador2 );

        //dd( $fecha_desde . ' * ' .  $fecha_hasta . ' * ' .  $inv_producto_id . ' * ' .  $operador1 . ' * ' .  $proveedor_id . ' * ' .  $operador2 );

        //dd( $movimiento );

        $vista = View::make('compras.reportes.precio_compra', compact('movimiento') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );

        return $vista;
    }

}