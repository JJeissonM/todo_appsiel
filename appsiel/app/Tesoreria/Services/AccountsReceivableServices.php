<?php 

namespace App\Tesoreria\Services;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;
use App\CxC\AccountsReceivablePayment;
use App\CxC\Services\AccountingServices;

use App\Ventas\VtasDocEncabezado;

use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoDocEncabezado;

use App\Core\Transactions\TransactionDocument;

class AccountsReceivableServices
{
    public function create_record_payment_accounts_receivable( TesoDocEncabezado $payment_document_header,string $account_receivable_lines)
    {
        $arr_account_receivable_lines = json_decode($account_receivable_lines);

        array_pop($arr_account_receivable_lines);
        
        $cantidad = count($arr_account_receivable_lines);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $abono = (float)$arr_account_receivable_lines[$i]->abono;
            $accounts_receivable_record = CxcMovimiento::find( (int)$arr_account_receivable_lines[$i]->id_doc );

            // Almacenar Abono
            $obj_ar_payment = new AccountsReceivablePayment();
            $model = new CxcAbono();
            $data = $this->set_data($payment_document_header,$accounts_receivable_record,$abono);
            $account_receivable_payment = $obj_ar_payment->store($model,$data);

            // Contabilizar Abono
            $obj_ar_accou_serv = new AccountingServices();
            $obj_ar_accou_serv->create_accounting_movement( $account_receivable_payment );

            // Actualizar Saldo
            $accounts_receivable_record->actualizar_saldos($abono);
        }
    }

    public function set_data($payment_document_header,$accounts_receivable_record,$payment_value)
    {
        // Almacenar registro de abono
        $datos = ['core_tipo_transaccion_id' => $payment_document_header->core_tipo_transaccion_id]+
        ['core_tipo_doc_app_id' => $payment_document_header->core_tipo_doc_app_id]+
        ['consecutivo' => $payment_document_header->consecutivo]+
        ['core_empresa_id' => $payment_document_header->core_empresa_id]+
        ['core_tercero_id' => $payment_document_header->core_tercero_id]+
        ['modelo_referencia_tercero_index' => $accounts_receivable_record->modelo_referencia_tercero_index]+
        ['referencia_tercero_id' => $accounts_receivable_record->referencia_tercero_id]+
        ['fecha' => $payment_document_header->fecha]+
        ['doc_cxc_transacc_id' => $accounts_receivable_record->core_tipo_transaccion_id]+
        ['doc_cxc_tipo_doc_id' => $accounts_receivable_record->core_tipo_doc_app_id]+
        ['doc_cxc_consecutivo' => $accounts_receivable_record->consecutivo]+
        ['abono' => $payment_value]+
        ['doc_cruce_transacc_id' => 0]+
        ['doc_cruce_tipo_doc_id' => 0]+
        ['doc_cruce_consecutivo' => 0]+
        ['creado_por' => $payment_document_header->creado_por]+
        ['modificado_por' => ''];

        return $datos;
    }
}