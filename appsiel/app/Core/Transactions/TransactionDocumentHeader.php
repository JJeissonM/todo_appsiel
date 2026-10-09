<?php

namespace App\Core\Transactions;

use App\Sistema\Modelo;

use App\Core\TipoDocApp;
use App\Core\Services\DocumentSequenceTransaction;

class TransactionDocumentHeader
{
	/* Toda transaccion tiene un modelo asociado */
	protected $model;

	public $document_header;

	function __construct( Modelo $model )
	{
		$this->model = $model;
	}

	public function create( array $data )
	{
		DocumentSequenceTransaction::run(function () use ($data) {
			$data['consecutivo'] = TipoDocApp::reservar_consecutivo($data['core_empresa_id'], $data['core_tipo_doc_app_id']);
			$this->store($data);
		});
	}

	public function store( $data )
	{
        $this->document_header = app( $this->model->name_space )->create( $data );
	}

}
