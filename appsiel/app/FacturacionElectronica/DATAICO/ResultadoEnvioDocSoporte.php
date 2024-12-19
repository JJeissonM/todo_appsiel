<?php

namespace App\FacturacionElectronica\DATAICO;

use App\Compras\ComprasDocEncabezado;
use App\FacturacionElectronica\ResultadoEnvioDocumentoSoporte;

class ResultadoEnvioDocSoporte
{
	public function almacenar_resultado( $resultado_original, $obj_documento_enviado, $encabezado_factura_id )
	{
        $resultado_almacenar = $this->formatear_resultado( $resultado_original, $obj_documento_enviado, $encabezado_factura_id );

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

        return (new ResultadoEnvio())->get_mensaje( $resultado_original );
	}

	/**
	 * 
	 */
	public function formatear_resultado( $resultado, $obj_documento_enviado, $encabezado_factura_id )
    {
        $encabezado_factura = ComprasDocEncabezado::find($encabezado_factura_id);

        $resultado_almacenar['core_empresa_id'] = $encabezado_factura->core_empresa_id;
        $resultado_almacenar['core_tipo_transaccion_id'] = $encabezado_factura->core_tipo_transaccion_id;
        $resultado_almacenar['core_tipo_doc_app_id'] = $encabezado_factura->core_tipo_doc_app_id;
        $resultado_almacenar['consecutivo'] = $encabezado_factura->consecutivo;
        $resultado_almacenar['fecha'] = $encabezado_factura->fecha;
        $resultado_almacenar['core_tercero_id'] = $encabezado_factura->core_tercero_id;

        $resultado_almacenar['nombre'] = json_encode( $obj_documento_enviado ); // CONVERTIR A STRING

        $resultado_almacenar["codigo"] = $resultado['codigo'];

        $resultado_almacenar["fechaRespuesta"] = '';
        $resultado_almacenar["reglasNotificacionDIAN"] = '';

    	$resultado_almacenar['cufe'] = '';
    	$resultado_almacenar['consecutivoDocumento'] = $resultado['number'];

    	$resultado_almacenar['esValidoDian'] = '';
    	$resultado_almacenar['fechaAceptacionDIAN'] = '';
    	$resultado_almacenar['esValidoDian'] = 0;
    	$resultado_almacenar['fechaAceptacionDIAN'] = '';

    	$resultado_almacenar['hash'] = '';
    	$resultado_almacenar['qr'] = '';
    	$resultado_almacenar['xml'] = '';
    	$resultado_almacenar['resultado'] = 'Error';      

        if ( $resultado["codigo"] == 200 ) {
            $resultado["codigo"] = 201;
        }

        switch ( $resultado["codigo"] ) {
    		case '201':
                $resultado_almacenar['cufe'] = $resultado['cufe'];

                $resultado_almacenar['fechaRespuesta'] = $resultado['issue_date'];

                $resultado_almacenar['esValidoDian'] = 1;
                $resultado_almacenar['fechaAceptacionDIAN'] = $resultado['issue_date'];
                $resultado_almacenar['resultado'] = 'Procesado';

                if ( $resultado['dian_status'] != 'DIAN_ACEPTADO' )
                {
                    $resultado_almacenar['esValidoDian'] = 0;
                    $resultado_almacenar['fechaAceptacionDIAN'] = '';
                    $resultado_almacenar["reglasNotificacionDIAN"] = 'Por favor ingrese a la plataforma del proveedor tecnológico para validar los errores de envío. También puede anular la nota y crear otra nuevamente.';
                    $resultado_almacenar['resultado'] = 'Rechazado por la DIAN';
                }

                $resultado_almacenar['hash'] = $resultado['uuid'];
                $resultado_almacenar['qr'] = $resultado['qrcode'];
                $resultado_almacenar['xml'] = $resultado['xml'];

            break;
            default:
                
                $errores = '';
                if ( isset($resultado['errors']) ) {
                    if ( !empty($resultado['errors']) ) {
                        $arr_errors = $resultado['errors'];

                        if( gettype($arr_errors) == 'string' )
                        {
                            $errores .= '<br>' . $arr_errors;
                        }else{
                            foreach ($arr_errors as $key => $arr_error) {
                                $label_path = '';
                                if (isset($arr_error['path'])) {
                                    $label_path = 'path: ' . $arr_error['path'][0] . '. ';
                                }
                                $errores .= $label_path . $arr_error['error'];
                            }
                        }                        
                    }
                }

    			$resultado_almacenar["reglasNotificacionDIAN"] = '<br>Notificaciones DIAN<br>' . $errores;

            break;
        }

		return $resultado_almacenar;
    }
}
