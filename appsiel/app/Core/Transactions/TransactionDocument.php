<?php

namespace App\Core\Transactions;

use App\Sistema\TipoTransaccion;

use Exception;

class TransactionDocument
{
	/* Each transaction has a SysModel associated */
	protected $transaction;

	function __construct( string $transaction_name )
	{
        // Replace "id" por "name"
		$this->transaction = TipoTransaccion::where( 'id', $transaction_name )->get()->first();
        if ( $this->transaction == null ) {
            throw new Exception('Transaccion <' . $transaction_name . '> no existe.');
        }
	}

	public function create( array $data )
	{
		if ( $this->transaction->model == null ) {
            throw new Exception('La Transaccion <' . $this->transaction->name . '> no tiene un modelo relacionado.');
        }
        app( $this->transaction->model->name_space )->create_transaction_document( $this->transaction->model, $data );
	}
}