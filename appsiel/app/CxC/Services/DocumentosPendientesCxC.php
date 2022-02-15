<?php

namespace App\CxC\Services;

use DB;
use Auth;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

class DocumentosPendientesCxC
{
    
    public function get_movimiento_documentos_pendientes_fecha_corte( $fecha_corte, $core_tercero_id, $clase_cliente_id ) 
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

        if( $clase_cliente_id != '' )
        {
            $array_wheres = array_merge($array_wheres, [ [ 'vtas_clientes.clase_cliente_id', '=', $clase_cliente_id ] ] );
        }
        $movimiento = CxcMovimiento::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_movimientos.core_tercero_id')
                                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
                                    ->leftJoin('vtas_clientes', 'vtas_clientes.core_tercero_id', '=', 'cxc_movimientos.core_tercero_id')
                                    ->where( $array_wheres )
                                    ->select(
                                                'cxc_movimientos.id',
                                                'cxc_movimientos.core_tipo_transaccion_id',
                                                'cxc_movimientos.core_tipo_doc_app_id',
                                                'cxc_movimientos.consecutivo',
                                                'core_terceros.descripcion AS tercero',
                                                'core_terceros.numero_identificacion',
                                                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_movimientos.consecutivo) AS documento'),
                                                'cxc_movimientos.fecha',
                                                'cxc_movimientos.fecha_vencimiento',
                                                'cxc_movimientos.valor_documento',
                                                'cxc_movimientos.valor_pagado',
                                                'cxc_movimientos.saldo_pendiente',
                                                'cxc_movimientos.estado',
                                                'vtas_clientes.clase_cliente_id',
                                                'cxc_movimientos.core_tercero_id')
                                    ->orderBy('cxc_movimientos.core_tercero_id')
                                    ->orderBy('cxc_movimientos.fecha')
                                    ->get();
        
        // En el movimiento hay documentos de anticipo (Ej, Recaudos) y documentos de cartera (Ej, Facturas de ventas)
        foreach( $movimiento as $linea_movimiento )
        {
            if ( $linea_movimiento->valor_documento < 0 )
            {
                // ANTICIPO
                $array_wheres2 = [
                                    ['core_tipo_transaccion_id', '=', $linea_movimiento->core_tipo_transaccion_id ],
                                    ['core_tipo_doc_app_id', '=', $linea_movimiento->core_tipo_doc_app_id ],
                                    ['consecutivo', '=', (int)$linea_movimiento->consecutivo ],
                                    ['core_tercero_id', '=', $linea_movimiento->core_tercero_id ]
                                ];
            }else{
                // DOCUMENTO DE CXC (FACTURA)
                $array_wheres2 = [
                                    ['doc_cxc_transacc_id', '=', $linea_movimiento->core_tipo_transaccion_id ],
                                    ['doc_cxc_tipo_doc_id', '=', $linea_movimiento->core_tipo_doc_app_id ],
                                    ['doc_cxc_consecutivo', '=', (int)$linea_movimiento->consecutivo ],
                                    ['core_tercero_id', '=', $linea_movimiento->core_tercero_id ]
                                ];
            }

            if( $fecha_corte != '' )
            {
                $fecha_corte = \Carbon\Carbon::parse( $fecha_corte )->format('Y-m-d');
                $array_wheres2 = array_merge( $array_wheres2, [ ['fecha', '<=', $fecha_corte ] ] );
            }

            // Sumar los abonos hechos al documento del movimiento para restarlos al valor del documento y mostrarlo en el saldo pendiente
            $abonos = CxcAbono::where( $array_wheres2 )->sum('abono'); // Siempre positivo
            
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
