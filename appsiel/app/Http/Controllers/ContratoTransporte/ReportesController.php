<?php

namespace App\Http\Controllers\ContratoTransporte;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Compras\Proveedor;

use App\CxP\CxpMovimiento;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

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
        $movimiento_a_mostrar = $request->movimiento_a_mostrar;

        if ( $request->core_tercero_id == '' )
        {
            $operador = 'LIKE';
            $cadena = '%'.$request->core_tercero_id.'%';
        }

        
    
        $movimiento = CxpMovimiento::get_documentos_referencia_tercero( $operador, $cadena );

        if (count($movimiento) > 0) {
            $movimiento = collect($movimiento);
            $group = $movimiento->groupBy('core_tercero_id');
            $collection = null;
            $collection = collect($collection);
            foreach ($group as $key => $item) {
                $aux = $item->pluck('saldo_pendiente');
                
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
                
                $sum = 0;
                foreach ($item as $value)
                {
                    if ( $movimiento_a_mostrar == 'cartera' && $value['valor_documento'] < 0 ) {
                        continue;
                    }
                
                    if ( $movimiento_a_mostrar == 'anticipos' && $value['valor_documento'] > 0 ) {
                        continue;
                    }

                    $sum += $value['saldo_pendiente'];

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

        $vista = View::make( 'compras.incluir.ctas_por_pagar', compact('movimiento', 'movimiento_a_mostrar') )->render();

        Cache::forever( 'pdf_reporte_'.json_decode( $request->reporte_instancia )->id, $vista );
   
        return $vista;
    }
}