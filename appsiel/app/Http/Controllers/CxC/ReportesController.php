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

use App\CxC\Services\DocumentosPendientesCxC;

use App\Ventas\Cliente;
use App\Ventas\VtasDocEncabezado;

use App\Matriculas\FacturaAuxEstudiante;
use App\Matriculas\Responsableestudiante;

class ReportesController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function documentos_pendientes(Request $request)
    {
        $core_tercero_id = $request->core_tercero_id;
        $clase_cliente_id = $request->clase_cliente_id;
        $fecha_corte = $request->fecha_corte;

        $servicio = new DocumentosPendientesCxC();

        $movimiento = $servicio->get_movimiento_documentos_pendientes_fecha_corte( $fecha_corte, $core_tercero_id, $clase_cliente_id );
        
        //dd($movimiento);

        foreach ( $movimiento as $linea_movimiento )
        {
            $linea_movimiento->show = 1;
            // Para NO mostrar saldos con decimales pequeños. Esto se debe corregir al hacer los abonos
            if ( $linea_movimiento->saldo_pendiente > -0.1 && $linea_movimiento->saldo_pendiente < 0.1 && $linea_movimiento->id != 0 )
            {
                $linea_movimiento->show = 0;
            }

            $linea_movimiento->lbl_estudiante = $this->get_estudiante_relacionado( $linea_movimiento->core_tipo_transaccion_id, $linea_movimiento->core_tipo_doc_app_id, $linea_movimiento->consecutivo, $linea_movimiento->core_tercero_id );
        }

        $vista = View::make('cxc.reportes.documentos_pendientes_cxc', compact('movimiento'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }

    public function get_estudiante_relacionado( $doc_vta_core_tipo_transaccion_id, $doc_vta_core_tipo_doc_app_id, $doc_vta_consecutivo, $core_tercero_id )
    {
        $array_wheres = [
                            [ 'core_tipo_transaccion_id', '=', $doc_vta_core_tipo_transaccion_id ],
                            [ 'core_tipo_doc_app_id', '=', $doc_vta_core_tipo_doc_app_id ],
                            [ 'consecutivo', '=', $doc_vta_consecutivo ]
                        ];

        $factura_ventas = VtasDocEncabezado::where( $array_wheres )
                                            ->get()
                                            ->first();
        

        $lbl_estudiante = '';
        // Para colegios
        if ( !is_null($factura_ventas) )
        {
            $factura_estudiante = FacturaAuxEstudiante::where( 'vtas_doc_encabezado_id', $factura_ventas->id )->get()->first();

            if ( !is_null( $factura_estudiante) )
            {                
                if ( !is_null($factura_estudiante->matricula) )
                {
                    $reponsable_estudiante = Responsableestudiante::where( 'tercero_id', $core_tercero_id )
                                                                ->where('estudiante_id', $factura_estudiante->matricula->id_estudiante)
                                                                ->get()
                                                                ->first();

                    if( !is_null( $reponsable_estudiante ) )
                    {
                        $lbl_estudiante = $reponsable_estudiante->estudiante->tercero->descripcion;
                    }
                }                    
            }                
        }

        return $lbl_estudiante;
    }

    public function estado_de_cuenta(Request $request) {
        $operador = '=';
        $cadena = $request->core_tercero_id;

        if ( $request->core_tercero_id == '' )
        {
            return '<h4 style="color:red;">Debe seleccionar un cliente.</h4>';
        }

        $movimiento = DocumentosPendientesCxC::get_documentos_referencia_tercero( $operador, $cadena );
        
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

        $empresa = Auth::user()->empresa;
        $cliente = Cliente::where('core_tercero_id',$request->core_tercero_id)->get()->first();

        $vista = View::make('cxc.incluir.estado_de_cuenta', compact('movimiento','empresa','cliente'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }
}