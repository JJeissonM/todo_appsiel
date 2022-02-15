<?php

namespace App\CxC\Services;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;
use Illuminate\Support\Facades\Auth;

// Outstanding balances accounts receivables
class OutstandingBalancesAcr
{
    
    public function get_outstanding_balance_ofcontact_ondeadline( $deadline, $contact_id ) 
    {
        $array_wheres = [
                            [ 'core_empresa_id', '=', Auth::user()->empresa_id],
                            [ 'fecha', '<=', $deadline],
                            [ 'core_tercero_id', '=', $contact_id ]
                        ];
        
        $movimientos = CxcMovimiento::where( $array_wheres )
                                    ->orderBy('core_tercero_id')
                                    ->orderBy('fecha')
                                    ->get();
        
        // En el movimiento hay saldos de anticipos (Negativos) (Ej, documentos de Recaudos) y saldos de cartera (Positivos) (Ej, documentos de Facturas de ventas)
        foreach( $movimientos as $movimiento )
        {
            if ( $movimiento->valor_documento < 0 )
            {
                // ANTICIPO
                $array_wheres2 = [
                                    ['core_tipo_transaccion_id', '=', $movimiento->core_tipo_transaccion_id ],
                                    ['core_tipo_doc_app_id', '=', $movimiento->core_tipo_doc_app_id ],
                                    ['consecutivo', '=', $movimiento->consecutivo ],
                                    ['core_tercero_id', '=', $movimiento->core_tercero_id ],
                                    ['fecha', '<=', $deadline ]
                                ];
            }else{
                // DOCUMENTO DE CXC (FACTURA)
                $array_wheres2 = [
                                    ['doc_cxc_transacc_id', '=', $movimiento->core_tipo_transaccion_id ],
                                    ['doc_cxc_tipo_doc_id', '=', $movimiento->core_tipo_doc_app_id ],
                                    ['doc_cxc_consecutivo', '=', $movimiento->consecutivo ],
                                    ['core_tercero_id', '=', $movimiento->core_tercero_id ],
                                    ['fecha', '<=', $deadline ]
                                ];
            }

            // Sumar los abonos hechos al documento del movimiento para restarlos al valor del documento y mostrarlo en el saldo pendiente
            $abonos = CxcAbono::where( $array_wheres2 )->sum('abono'); // Siempre positivo
            
            if ( $movimiento->valor_documento < 0 )
            {
                // ANTICIPO
                $movimiento->valor_pagado = $abonos * -1;
                $movimiento->saldo_pendiente = $movimiento->valor_documento + $abonos;
            }else{
                // DOCUMENTO DE CXC (FACTURA)
                $movimiento->valor_pagado = $abonos;
                $movimiento->saldo_pendiente = $movimiento->valor_documento - $abonos;
            }
        }

        return $movimientos;
    }

    // The payment can be applied in an Invoice or in an Advance
    public function get_acr_movement_payments($row, $deadline)
    {
        if ( $row->valor_documento < 0 )
        {
            // ANTICIPO
            $array_wheres2 = [
                            ['core_tipo_transaccion_id', '=', $row->core_tipo_transaccion_id ],
                            ['core_tipo_doc_app_id', '=', $row->core_tipo_doc_app_id ],
                            ['consecutivo', '=', $row->consecutivo ],
                            ['core_tercero_id', '=', $row->core_tercero_id ],
                            ['fecha', '<=', $deadline ]
                        ];
        }else{
            // DOCUMENTO DE CXC (FACTURA)
            $array_wheres2 = [
                            ['doc_cxc_transacc_id', '=', $row->core_tipo_transaccion_id ],
                            ['doc_cxc_tipo_doc_id', '=', $row->core_tipo_doc_app_id ],
                            ['doc_cxc_consecutivo', '=', $row->consecutivo ],
                            ['core_tercero_id', '=', $row->core_tercero_id ],
                            ['fecha', '<=', $deadline ]
                        ];
        }
        
        // Abonos hechos al documento del movimiento
        $payments = CxcAbono::where( $array_wheres2 )->get();

        $records = [];
        foreach ($payments as $payment) {
            $records[] = [
                'payment_document_header' => $payment,
                'account_receivable_document_header' => $payment->account_receivable_document_header(),
                'accross_document_header' => $payment->accross_document_header()
            ];
        }

        return $records;
    }

}
