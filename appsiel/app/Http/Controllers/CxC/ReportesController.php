<?php

namespace App\Http\Controllers\CxC;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use Auth;
use Form;
use View;
use Cache;

use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;
use App\Core\Empresa;

use App\CxC\DocumentosPendientes;


class ReportesController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function documentos_pendientes(Request $request) {
        //dd($request->all());
        $operador = '=';
        $cadena = $request->core_tercero_id;

        if ($request->core_tercero_id == '') {
            $operador = 'LIKE';
            $cadena = '%' . $request->core_tercero_id . '%';
            $movimiento = DocumentosPendientes::get_documentos_pendientes_clase_cliente($request->clase_cliente_id);
        }else{
            $movimiento = DocumentosPendientes::get_documentos_referencia_tercero( $operador, $cadena );
        }
        if (count($movimiento) > 0) {
            $movimiento = collect($movimiento);
            $group = $movimiento->groupBy('core_tercero_id');
            $collection = null;
            $collection = collect($collection);
            foreach ($group as $key => $item) {
                $aux = $item->pluck('saldo_pendiente');
                $sum = $aux->sum();
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
                    "core_tercero_id" => ''
                ];
                $collection[]=$obj;
            }
            $movimiento = $collection;
        }
        $vista = View::make('cxc.incluir.documentos_pendientes', compact('movimiento'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }
}