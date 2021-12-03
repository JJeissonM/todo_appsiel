<?php 

namespace App\Sistema\ValueObjects;

// Siempre "Vo" al final para indicar que es un Value Object
class TransactionPrimaryKeyVO
{
    public $tpk; // transaction_primary_key

    public function __construct( $core_empresa_id, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $consecutivo)
    {
        $this->tpk = [
                        'core_empresa_id' => $core_empresa_id, 
                        'core_tipo_transaccion_id' => $core_tipo_transaccion_id,
                        'core_tipo_doc_app_id' => $core_tipo_doc_app_id,
                        'consecutivo' => $consecutivo
                    ];
    }
}
