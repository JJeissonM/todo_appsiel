<?php

namespace App\Core;

use App\Sistema\Modelo;

use App\Core\TipoDocApp;

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
		$datos['updated_at'] = NULL;
		$this->almacenar( $datos );
		$this->asignar_consecutivo();
		$this->incrementar_consecutivo_tipo_documento();

		return $this->encabezado_documento;
	}

	public function almacenar( $datos )
	{
        // Crear el nuevo registro
        $this->encabezado_documento = app( $this->modelo->name_space )->create( $datos );
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