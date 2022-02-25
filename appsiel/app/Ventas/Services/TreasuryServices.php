<?php 

namespace App\Ventas\Services;

use App\CxC\CxcMovimiento;
use App\Ventas\VtasDocEncabezado;

use App\Tesoreria\RegistrosMediosPago;

use App\Core\Transactions\TransactionDocument;

class TreasuryServices
{
    public function create_account_receivable_payment_from_invoice(VtasDocEncabezado $account_receivable_document_header,$payment,$payment_methods_lines)
    {
        $account_receivable_record = CxcMovimiento::where( [
            'core_empresa_id'=>$account_receivable_document_header->core_empresa_id, 
            'core_tipo_transaccion_id' => $account_receivable_document_header->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $account_receivable_document_header->core_tipo_doc_app_id,
            'consecutivo' => $account_receivable_document_header->consecutivo
        ] )
        ->get()->first();
        $data = $account_receivable_document_header->toArray();

        // Lines of records of account_receivable
        $data['account_receivable_lines'] = '[{"id_doc":"' . $account_receivable_record->id . '","Cliente":"NA","Documento interno":"NA","Fecha":"NA","Fecha vencimiento":"NA","Valor Documento":"$0","Valor pagado":"$0","Saldo pendiente":"$0","abono":"' . $payment . '"},{"id_doc":"","Cliente":"","Documento interno":"$0","Fecha":"","Fecha vencimiento":"","Valor Documento":"","Valor pagado":"","Saldo pendiente":""}]';

        // Lines of records of treasury document
        $data['document_lines'] = $this->get_json_string_document_lines($payment_methods_lines, $payment);

        $transaction_name = 32; // Recaudos de CxC. Por ahora se maneja el ID
        $transaction_doc = new TransactionDocument($transaction_name,$this->complete_data($data));
        $transaction_doc->create( $this->complete_data($data) );
    }

    public function complete_data($data)
    {
        // Algunos de estos campos deben desaparecer en el encabezado de documentos de Tesoreria (deprecated)
        $data['codigo_referencia_tercero'] = 0;
        $data['teso_tipo_motivo'] = 0;
        $data['documento_soporte'] = 0;
        $data['teso_medio_recaudo_id'] = 0;
        $data['teso_caja_id'] = 0;
        $data['teso_cuenta_bancaria_id'] = 0;
        $data['modificado_por'] = 0;
        
        return $data;
    }

    public function get_json_string_document_lines($payment_methods_lines, $payment)
    {
        /* 
            Por ahora se esta enviando asi desde la creacion de las facturas:
            
            UN SOLO registro de medio de pago
            
            "lineas_registros_medios_recaudo" => "[{"teso_medio_recaudo_id":"4-Banco (Consignación)","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"0-","teso_cuenta_bancaria_id":"10-Banco Davivienda SA - 3534453","valor":"$2300"},{"teso_medio_recaudo_id":"","teso_motivo_id":"$2300.00","teso_caja_id":"","teso_cuenta_bancaria_id":""}]"
        */
        $registros_medio_pago = new RegistrosMediosPago;
        $campo_lineas_recaudos = $registros_medio_pago->depurar_tabla_registros_medios_recaudos( $payment_methods_lines, $payment );        
        $registros_medio_pago = $registros_medio_pago->get_datos_ids( $campo_lineas_recaudos );
        
        // Estos deberian enviarse desde el request
        $registros_medio_pago['valor'] = $registros_medio_pago['valor_recaudo'];
        $registros_medio_pago['core_tercero_id'] = '';
        $registros_medio_pago['detalle_operacion'] = '';

        $records[] = $registros_medio_pago; // Se incluye en una collection, no un solo registro

        return json_encode($records);
    }
}