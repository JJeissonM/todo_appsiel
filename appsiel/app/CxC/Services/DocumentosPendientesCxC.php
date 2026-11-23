<?php

namespace App\CxC\Services;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentosPendientesCxC
{
    
    public function get_movimiento_documentos_pendientes_fecha_corte( $fecha_corte, $core_tercero_id ) 
    {
        $array_wheres = [
                            [ 'cxc_movimientos.core_empresa_id', '=', Auth::user()->empresa_id]
                        ];

        if( $fecha_corte != '' )
        {
            $fecha_corte = \Carbon\Carbon::parse( $fecha_corte )->format('Y-m-d');
            $array_wheres = array_merge($array_wheres, [ [ 'cxc_movimientos.fecha', '<=', $fecha_corte] ] );
        }

        if( $core_tercero_id != '' )
        {
            $array_wheres = array_merge($array_wheres, [ [ 'cxc_movimientos.core_tercero_id', '=', (int)$core_tercero_id ] ] );
        }

        $movimiento = CxcMovimiento::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_movimientos.core_tercero_id')
                                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
                                    ->where( $array_wheres )
                                    ->select(
                                                'cxc_movimientos.id',
                                                'cxc_movimientos.core_empresa_id',
                                                'cxc_movimientos.core_tipo_transaccion_id',
                                                'cxc_movimientos.core_tipo_doc_app_id',
                                                'cxc_movimientos.consecutivo',
                                                'core_terceros.descripcion AS descripcion_tercero',
                                                'core_terceros.numero_identificacion',
                                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_movimientos.consecutivo) AS documento'),
                                                'cxc_movimientos.fecha',
                                                'cxc_movimientos.fecha_vencimiento',
                                                'cxc_movimientos.valor_documento',
                                                'cxc_movimientos.valor_pagado',
                                                'cxc_movimientos.saldo_pendiente',
                                                'cxc_movimientos.estado',
                                                'cxc_movimientos.core_tercero_id')
                                    ->orderBy('cxc_movimientos.core_tercero_id')
                                    ->orderBy('cxc_movimientos.fecha')
                                    ->get();
        
        // En el movimiento hay documentos de anticipo (Ej, Recaudos) y documentos de cartera (Ej, Facturas de ventas)
        foreach( $movimiento as $linea_movimiento )
        {
            $consulta_abonos = CxcAbono::queryDelMovimientoCxc($linea_movimiento);
            if ($fecha_corte != '') {
                $consulta_abonos->where('fecha', '<=', $fecha_corte);
            }
            $abonos = $consulta_abonos->sum('abono');

            if ($abonos > abs($linea_movimiento->valor_documento)) {
                $abonos = abs($linea_movimiento->valor_documento);
            }
            
            if ( $linea_movimiento->valor_documento < 0 )
            {
                // ANTICIPO
                $linea_movimiento->valor_pagado = $abonos * -1;
                $linea_movimiento->saldo_pendiente = $linea_movimiento->valor_documento + $abonos;
            }else{
                // DOCUMENTO DE CXC (FACTURA)
                $linea_movimiento->valor_pagado = $abonos;
                $linea_movimiento->saldo_pendiente = $linea_movimiento->valor_documento - $abonos;
            }
        }

        return $movimiento;
    }

}
