<?php 

namespace App\Tesoreria\Services;

use App\Sistema\ValueObjects\TransactionPrimaryKeyVO;

use App\Contabilidad\Services\AccountingMovingService;
use App\Contabilidad\Services\AccountingMovement;
use App\Contabilidad\ContabMovimiento;

class AccountingServices
{
    public function create_accounting_movement( $treasury_movement )
    {
        $obj_acco_move = new AccountingMovement();
        $model = new ContabMovimiento();

        foreach ($treasury_movement as $movement) {
            
            $data = $this->set_data($movement);
            
            // Accounting Safe or Bank
            $data['contab_cuenta_id'] = $this->get_main_account($movement);
            $obj_acco_move->store($model,$data);

            // Accounting Entry Contra
            if ($movement->motivo->teso_tipo_motivo == 'recaudo-cartera' || $movement->motivo->teso_tipo_motivo == 'pago-proveedores') {
                continue;
            }
            $obj_acco_move->store($model,$this->set_data_contra($movement,$data));
        }
    }

    public function get_main_account($movement)
    {
        if ( $movement->caja != null ) {
            $contab_cuenta_id = $movement->caja->contab_cuenta_id;
        }

        if ( $movement->cuenta_bancaria != null ) {
            $contab_cuenta_id = $movement->cuenta_bancaria->contab_cuenta_id;
        }

        return $contab_cuenta_id;
    }

    public function set_data($movement)
    {
        $data = $movement->toArray();
        $data['id_registro_doc_tipo_transaccion'] = $movement->id;
        $data['valor_operacion'] = 0;

        $valor_debito = 0;
        $valor_credito = 0;
        if ($movement->valor_movimiento>0) {
            // Mov. Entrada
            $valor_debito = $movement->valor_movimiento;
        }else{
            // Mov. Salida
            $valor_credito = $movement->valor_movimiento;
        }

        $data['valor_debito'] = $valor_debito;
        $data['valor_credito'] = $valor_credito;
        $data['valor_saldo'] = $valor_debito + $valor_credito;
        $data['detalle_operacion'] = $movement->descripcion;
        $data['tipo_transaccion'] = $movement->motivo->teso_tipo_motivo; // Deberia ser el mismo modo de operacion
        $data['inv_producto_id'] = 0;
        $data['impuesto_id'] = 0;
        $data['cantidad'] = 0;
        $data['tasa_impuesto'] = 0;
        $data['base_impuesto'] = 0;
        $data['valor_impuesto'] = 0;
        $data['fecha_vencimiento'] = '0000-00-00';
        $data['inv_bodega_id'] = 0;

        return $data;
    }

    public function set_data_contra($movement,$data)
    {
        $data['contab_cuenta_id'] = $movement->motivo->contab_cuenta_id;
        // Se invierten los valores
        $valor_debito = $data['valor_debito'];
        $valor_credito = $data['valor_credito'];
        $data['valor_debito'] = $valor_credito * -1;
        $data['valor_credito'] = $valor_debito * -1;
        $data['valor_saldo'] = $data['valor_saldo'] * -1;

        $data['teso_caja_id'] = 0;
        $data['teso_cuenta_bancaria_id'] = 0;

        return $data;
    }

    public function delete_accounting_move( $core_empresa_id, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo )
    {
        $obj_accou_movin_serv = new AccountingMovingService();
        $obj_accou_movin_serv->delete_move( new TransactionPrimaryKeyVO( $core_empresa_id, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo ) );
    }
}
