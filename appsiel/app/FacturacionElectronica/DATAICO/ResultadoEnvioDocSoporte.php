<?php

namespace App\FacturacionElectronica\DATAICO;

use App\Compras\ComprasDocEncabezado;
use App\FacturacionElectronica\ResultadoEnvioDocumentoSoporte;

class ResultadoEnvioDocSoporte
{
	public function almacenar_resultado( $resultado_original, $obj_documento_enviado, $encabezado_factura_id )
	{

        $resultado_envio_service = new ResultadoEnvio();

        $resultado_almacenar = $resultado_envio_service->formatear_resultado( $resultado_original, $obj_documento_enviado, $encabezado_factura_id );

        ResultadoEnvioDocumentoSoporte::create( $resultado_almacenar );
        
        $resultado_original['reglasNotificacionDIAN'] = $resultado_almacenar['reglasNotificacionDIAN'];
        $resultado_original['fechaRespuesta'] = $resultado_almacenar['fechaRespuesta'];

        $resultado_original['cufe'] = $resultado_almacenar['cufe'];
    	$resultado_original['consecutivoDocumento'] = $resultado_almacenar['consecutivoDocumento'];

    	$resultado_original['esValidoDian'] = $resultado_almacenar['esValidoDian'];
    	$resultado_original['fechaAceptacionDIAN'] = $resultado_almacenar['fechaAceptacionDIAN'];

    	$resultado_original['hash'] = $resultado_almacenar['hash'];
    	$resultado_original['qr'] = $resultado_almacenar['qr'];
    	$resultado_original['xml'] = $resultado_almacenar['xml'];
    	$resultado_original['resultado'] = $resultado_almacenar['resultado'];

        return $resultado_envio_service->get_mensaje( $resultado_original );
	}
}
