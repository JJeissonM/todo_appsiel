<?php

namespace App\Core\Transactions\Services;

use App\Sistema\Modelo;

use App\Core\TipoDocApp;

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
		$this->almacenar( $datos );
		$this->asignar_consecutivo();
		$this->incrementar_consecutivo_tipo_documento();

		return $this->encabezado_documento;
	}

	public function almacenar( $datos )
	{
        // Crear el nuevo registro
        $this->encabezado_documento = app( $this->model->name_space )->create( $datos );
	}

	public function asignar_consecutivo()
	{
        $this->encabezado_documento->consecutivo = TipoDocApp::get_consecutivo_actual( $this->encabezado_documento->core_empresa_id, $this->encabezado_documento->core_tipo_doc_app_id ) + 1;
        $this->encabezado_documento->save();
	}

	public function incrementar_consecutivo_tipo_documento()
	{
		// Se incementa el consecutivo para ese tipo de documento y la empresa
        TipoDocApp::aumentar_consecutivo( $this->encabezado_documento->core_empresa_id, $this->encabezado_documento->core_tipo_doc_app_id );
	}
}