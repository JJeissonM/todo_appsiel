<?php

namespace App\Core\Transactions\Services;

use App\Sistema\Modelo;

use App\Core\TipoDocApp;
use App\Core\Services\DocumentSequenceTransaction;

class DocumentsService
{
	/* Toda transaccion tiene un modelo asociado */
	protected $model;

	protected $encabezado_documento;

	function __construct( string $model_name )
	{
		$this->model = Modelo::where( 'modelo', $model_name )->get()->first();
	}

	public function store_document_header( array $datos )
	{
		return DocumentSequenceTransaction::run(function () use ($datos) {
			$datos['consecutivo'] = TipoDocApp::reservar_consecutivo($datos['core_empresa_id'], $datos['core_tipo_doc_app_id']);
			$this->almacenar($datos);
			return $this->encabezado_documento;
		});
	}

	public function almacenar( $datos )
	{
        // Crear el nuevo registro
        $this->encabezado_documento = app( $this->model->name_space )->create( $datos );
	}

}
