<?php

namespace App\Core;

use App\Sistema\Modelo;

use App\Core\TipoDocApp;
use App\Core\Services\DocumentSequenceTransaction;

class EncabezadoDocumentoTransaccion
{
	/* Toda transaccion tiene un modelo asociado */
	protected $modelo;

	protected $encabezado_documento;

	function __construct( $modelo_id )
	{
		$this->modelo = Modelo::find( $modelo_id );
	}

	public function crear_nuevo( array $datos )
	{
		return DocumentSequenceTransaction::run(function () use ($datos) {
			$datos['updated_at'] = NULL;
			$datos['consecutivo'] = TipoDocApp::reservar_consecutivo($datos['core_empresa_id'], $datos['core_tipo_doc_app_id']);
			$this->almacenar($datos);
			return $this->encabezado_documento;
		});
	}

	public function almacenar( $datos )
	{
        // Crear el nuevo registro
        $this->encabezado_documento = app( $this->modelo->name_space )->create( $datos );
	}

}
