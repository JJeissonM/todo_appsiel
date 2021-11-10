<?php

namespace App\FacturacionElectronica;

use App\FacturacionElectronica\ResultadoEnvioDocumento;

class ResultadoEnvio
{
	public function almacenar_resultado( $resultado_original, $obj_documento_enviado, $encabezado_factura_id )
	{

        $resultado_almacenar = $this->formatear_resultado( $resultado_original );
        $resultado_almacenar['vtas_doc_encabezado_id'] = $encabezado_factura_id;
        $resultado_almacenar['nombre'] = json_encode( $obj_documento_enviado );
        ResultadoEnvioDocumento::create( $resultado_almacenar );

        return $this->get_mensaje( $resultado_original );
	}


    public function formatear_resultado( $resultado )
    {
    	$mensaje = '';
    	if ( !is_null( $resultado['mensajesValidacion'] ) )
		{
			if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
			{
				$mensaje .= $resultado["mensajesValidacion"]->string . '\n';
			}else{
				foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
				{
					$mensaje .= $value . '\n';
				}
			}				
		}
		$resultado["mensajesValidacion"] = $mensaje;

		
    	$mensaje = '';
    	if ( !is_null( $resultado['reglasNotificacionDIAN'] ) )
		{
			$mensaje = '<br>Notificaciones DIAN<br>';
			if ( gettype( $resultado["reglasNotificacionDIAN"]->string ) == 'string' )
			{
				$mensaje .= $resultado["reglasNotificacionDIAN"]->string . '\n';
			}else{
				foreach ($resultado["reglasNotificacionDIAN"]->string as $key => $value) 
				{
					$mensaje .= $value . '\n';
				}
			}
		}
		$resultado["reglasNotificacionDIAN"] = $mensaje;
		
    	$mensaje = '';
    	if ( !is_null( $resultado['reglasValidacionDIAN'] ) )
		{
			$mensaje = '<br>Validaciones DIAN<br>';
			if ( gettype( $resultado["reglasValidacionDIAN"]->string ) == 'string' )
			{
				$mensaje .= $resultado["reglasValidacionDIAN"]->string . '\n';
			}else{
				foreach ($resultado["reglasValidacionDIAN"]->string as $key => $value) 
				{
					$mensaje .= $value . '\n';
				}
			}
		}
		$resultado["reglasValidacionDIAN"] = $mensaje;

		return $resultado;
    }



    public function get_mensaje( $resultado )
    {
    	$mensaje = (object)['tipo'=>'','contenido'=>''];

    	switch ( $resultado["codigo"] ) {
    		case '200':
    			$mensaje->tipo = 'flash_message';
    			$mensaje->contenido = '<h3>Documento enviado correctamente hacia el proveedor tecnológico</h3>';
    			$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Consecutivo:  " .$resultado["consecutivoDocumento"] ."</br>CUFE:  " .$resultado["cufe"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Hash:  " .$resultado["hash"] ."</br>Reglas de validación DIAN:  " .$resultado["reglasValidacionDIAN"] ."</br>Resultado:  " .$resultado["resultado"] ."</br>Tipo de CUFE:  " .$resultado["tipoCufe"] ."</br>Mensaje Validación:  ";

    			if ( !is_null( $resultado['mensajesValidacion'] ) )
				{
					if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
					{
						$mensaje->contenido .= "</br>" .$resultado["mensajesValidacion"]->string;
					}else{
						foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
						{
							$mensaje->contenido .= "</br>" . $value;
						}
					}				
				}
    			break;

    		case '201':
    			$mensaje->tipo = 'flash_message';
    			$mensaje->contenido = '<h3>Documento enviado correctamente hacia la DIAN</h3>';
    			$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Consecutivo:  " .$resultado["consecutivoDocumento"] ."</br>CUFE:  " .$resultado["cufe"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Hash:  " .$resultado["hash"] ."</br>Reglas de validación DIAN:  " .$resultado["reglasValidacionDIAN"] ."</br>Resultado:  " .$resultado["resultado"] ."</br>Tipo de CUFE:  " .$resultado["tipoCufe"] ."</br>Mensaje Validación:  ";

    			if ( !is_null( $resultado['mensajesValidacion'] ) )
				{
					if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
					{
						$mensaje->contenido .= "</br>" .$resultado["mensajesValidacion"]->string;
					}else{
						foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
						{
							$mensaje->contenido .= "</br>" . $value;
						}
					}				
				}
    			break;

    		case '208':
    			$mensaje->tipo = 'flash_message';
    			$mensaje->contenido = '<h3>Documento enviado correctamente hacia la DIAN</h3>';
    			$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Consecutivo:  " .$resultado["consecutivoDocumento"] ."</br>CUFE:  " .$resultado["cufe"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Hash:  " .$resultado["hash"] ."</br>Reglas de validación DIAN:  " .$resultado["reglasValidacionDIAN"] ."</br>Resultado:  " .$resultado["resultado"] ."</br>Tipo de CUFE:  " .$resultado["tipoCufe"] ."</br>Mensaje Validación:  ";

    			if ( !is_null( $resultado['mensajesValidacion'] ) )
				{
					if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
					{
						$mensaje->contenido .= "</br>" .$resultado["mensajesValidacion"]->string;
					}else{
						foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
						{
							$mensaje->contenido .= "</br>" . $value;
						}
					}				
				}
    			break;
    		
    		default:
    			$mensaje->tipo = 'mensaje_error';

    			$mensaje->contenido = '<h3>Documento enviado correctamente hacia el proveedor tecnológico. Presenta errores de validación.</h3>';
				$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Mensaje Validación:  ";

				if ( !is_null( $resultado['mensajesValidacion'] ) )
				{
					if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
					{
						$mensaje->contenido .= "</br>" . $resultado["mensajesValidacion"]->string;
					}else{
						foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
						{
							$mensaje->contenido .= "</br>" . $value;
						}
					}				
				}
    			break;
    	}

    	return $mensaje;
    }
}
