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
        $clase_cliente_id = (int)$request->clase_cliente_id;
        $fecha_corte = $request->fecha_corte;

        $servicio = new DocumentosPendientesCxC();

        $movimiento = $servicio->get_movimiento_documentos_pendientes_fecha_corte( $fecha_corte, $core_tercero_id);
        
        foreach ( $movimiento as $linea_movimiento )
        {
            if ($clase_cliente_id != '') {
                if ($linea_movimiento->tercero == null ) {
                    continue;
                }

                if($linea_movimiento->tercero->cliente() == null)
                {
                    continue;
                }

                if($linea_movimiento->tercero->cliente()->clase_cliente_id != $clase_cliente_id)
                {
                    continue;
                }
            }                

            $linea_movimiento->show = 1;
            // Para NO mostrar saldos con saldo pendientes cero
            if ( $linea_movimiento->saldo_pendiente >= -0.1 && $linea_movimiento->saldo_pendiente <= 0.1 )
            {
                $linea_movimiento->show = 0;
            }

            $linea_movimiento->lbl_estudiante = $this->get_estudiante_relacionado( $linea_movimiento->core_tipo_transaccion_id, $linea_movimiento->core_tipo_doc_app_id, $linea_movimiento->consecutivo, $linea_movimiento->core_tercero_id );
        }

        $mostrar_columna_tercero = 1;

        $vista = '<h3 style="width: 100%; text-align: center;"> Documentos pendientes de Cuentas por Cobrar </h3>
<hr>' . View::make('cxc.reportes.documentos_pendientes_cxc', compact('movimiento','mostrar_columna_tercero'))->render();

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
                    $lbl_estudiante = $factura_estudiante->matricula->estudiante->tercero->descripcion;
                }
            }                
        }

        return $lbl_estudiante;
    }

    public function estado_de_cuenta(Request $request)
    {

        $core_tercero_id = $request->core_tercero_id;
        $fecha_corte = $request->fecha_corte;
        $clase_cliente_id = '';

        if ( $core_tercero_id == '' )
        {
            return '<h4 style="color:red;">Debe seleccionar un cliente.</h4>';
        }

        $servicio = new DocumentosPendientesCxC();

        $movimiento = $servicio->get_movimiento_documentos_pendientes_fecha_corte( $fecha_corte, $core_tercero_id, $clase_cliente_id );

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

        $empresa = Auth::user()->empresa;
        $cliente = Cliente::where('core_tercero_id',$core_tercero_id)->get()->first();

        $mostrar_columna_tercero = 0;
        $vista = View::make('cxc.incluir.estado_de_cuenta', compact('movimiento','empresa','cliente','mostrar_columna_tercero'))->render();

        Cache::forever('pdf_reporte_' . json_decode($request->reporte_instancia)->id, $vista);

        return $vista;
    }
}