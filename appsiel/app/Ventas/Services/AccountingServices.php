<?php 

namespace App\Ventas\Services;

use App\Contabilidad\ContabMovimiento;

class AccountingServices
{    
    public function contabilizar_registro( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito, $teso_caja_id = 0, $teso_cta_bancaria_id = 0 )
    {
        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ] + 
                            [ 'teso_caja_id' => $teso_caja_id]  + 
                            [ 'teso_cta_bancaria_id' => $teso_cta_bancaria_id] 
                        );
    }
        
}