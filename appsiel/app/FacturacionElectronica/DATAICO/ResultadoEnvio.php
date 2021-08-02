<?php

namespace App\FacturacionElectronica\DATAICO;

use App\FacturacionElectronica\ResultadoEnvioDocumento;

class ResultadoEnvio
{
	public function almacenar_resultado( $resultado_original, $obj_documento_enviado, $encabezado_factura_id )
	{
        $resultado_almacenar = $this->formatear_resultado( $resultado_original, $obj_documento_enviado, $encabezado_factura_id );
        ResultadoEnvioDocumento::create( $resultado_almacenar );
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

        return $this->get_mensaje( $resultado_original );
	}


    public function formatear_resultado( $resultado, $obj_documento_enviado, $encabezado_factura_id )
    {
        $resultado_almacenar['vtas_doc_encabezado_id'] = $encabezado_factura_id;
        $resultado_almacenar['nombre'] = json_encode( $obj_documento_enviado ); // CONVERTIR A STRING

        $resultado_almacenar["fechaRespuesta"] = '';
        $resultado_almacenar["reglasNotificacionDIAN"] = '';

    	$resultado_almacenar['cufe'] = '';
    	$resultado_almacenar['consecutivoDocumento'] = '';

    	$resultado_almacenar['esValidoDian'] = '';
    	$resultado_almacenar['fechaAceptacionDIAN'] = '';
    	$resultado_almacenar['esValidoDian'] = 0;
    	$resultado_almacenar['fechaAceptacionDIAN'] = '';

    	$resultado_almacenar['hash'] = '';
    	$resultado_almacenar['qr'] = '';
    	$resultado_almacenar['xml'] = '';
    	$resultado_almacenar['resultado'] = 'Error';

        /* 201: La solicitud se ha cumplido y ha dado lugar a la creación de un nuevo recurso, la factura fue creada satisfactoriamente.*/
        if ( $resultado['codigo'] == 201 ) // 
        {
        	$resultado_almacenar['cufe'] = $resultado['cufe'];
        	$resultado_almacenar['consecutivoDocumento'] = $resultado['number'];

        	$resultado_almacenar['fechaRespuesta'] = $resultado['issue_date'];

        	$resultado_almacenar['esValidoDian'] = 1;
        	$resultado_almacenar['fechaAceptacionDIAN'] = $resultado['issue_date'];
        	if ( $resultado['dian_status'] != 'DIAN_ACEPTADO' )
        	{
        		$resultado_almacenar['esValidoDian'] = 0;
        		$resultado_almacenar['fechaAceptacionDIAN'] = $resultado['issue_date'];
        	}

        	$resultado_almacenar['hash'] = $resultado['uuid'];
        	$resultado_almacenar['qr'] = $resultado['qrcode'];
        	$resultado_almacenar['xml'] = $resultado['xml'];
        	$resultado_almacenar['resultado'] = 'Procesado';

        }

        if ( $resultado['codigo'] == 500 ) // 
        {
			$mensaje = '<br>Notificaciones DIAN<br>';
			$errores = $resultado['errors'];
        	foreach ( $errores as $linea_resultado )
        	{
        		//dd($linea_resultado);
				$mensaje .= $linea_resultado['error'] . '\n';
        	}
        	$resultado_almacenar["reglasNotificacionDIAN"] = $mensaje;
        }

		return $resultado_almacenar;
    }



    public function get_mensaje( $resultado )
    {
    	$mensaje = (object)['tipo'=>'','contenido'=>''];

    	switch ( $resultado["codigo"] ) {
    		case '201':
    			$mensaje->tipo = 'flash_message';
    			$mensaje->contenido = '<h3>Documento enviado correctamente hacia el proveedor tecnológico</h3>';
    			$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Consecutivo:  " .$resultado["consecutivoDocumento"] ."</br>CUFE:  " .$resultado["cufe"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Hash:  " .$resultado["hash"] ."</br>Reglas de validación DIAN:  " .$resultado["reglasNotificacionDIAN"] ."</br>Resultado:  " .$resultado["resultado"];
    			break;
    		
    		default:
    			$mensaje->tipo = 'mensaje_error';

    			$mensaje->contenido = '<h3>Documento NO fue enviado hacia el proveedor tecnológico. Presenta errores de validación.</h3>';
				$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Mensaje Validación:  ";

				$mensaje->contenido .= "</br>" . $resultado["reglasNotificacionDIAN"];
    			break;
    	}

    	return $mensaje;
    }
}
