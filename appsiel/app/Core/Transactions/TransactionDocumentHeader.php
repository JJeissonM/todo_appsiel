<?php

namespace App\Core\Transactions;

use App\Sistema\Modelo;

use App\Core\TipoDocApp;

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
		$this->store( $data );
		$this->assign_sequence();
		$this->increment_sequence_document_type();
	}

	public function store( $data )
	{
        $this->document_header = app( $this->model->name_space )->create( $data );
	}

	public function assign_sequence()
	{
        $this->document_header->consecutivo = TipoDocApp::get_consecutivo_actual( $this->document_header->core_empresa_id, $this->document_header->core_tipo_doc_app_id ) + 1;
        $this->document_header->save();
	}

	public function increment_sequence_document_type()
	{
		// Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo( $this->document_header->core_empresa_id, $this->document_header->core_tipo_doc_app_id );
	}
}