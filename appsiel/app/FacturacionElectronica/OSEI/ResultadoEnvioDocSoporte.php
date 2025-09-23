<?php

namespace App\FacturacionElectronica\OSEI;

use App\Compras\ComprasDocEncabezado;
use App\FacturacionElectronica\ResultadoEnvioDocumentoSoporte;

class ResultadoEnvioDocSoporte
{
    public function almacenar_resultado($resultado_original, $obj_documento_enviado, $encabezado_factura_id)
    {
        $resultado_almacenar = $this->formatear_resultado($resultado_original, $obj_documento_enviado, $encabezado_factura_id);

        ResultadoEnvioDocumentoSoporte::create($resultado_almacenar);

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

        return $this->get_mensaje($resultado_original);
    }

    public function formatear_resultado($resultado, $obj_documento_enviado, $encabezado_factura_id)
    {
        $encabezado_factura = ComprasDocEncabezado::find($encabezado_factura_id);

        $resultado_almacenar['core_empresa_id'] = $encabezado_factura->core_empresa_id;
        $resultado_almacenar['core_tipo_transaccion_id'] = $encabezado_factura->core_tipo_transaccion_id;
        $resultado_almacenar['core_tipo_doc_app_id'] = $encabezado_factura->core_tipo_doc_app_id;
        $resultado_almacenar['consecutivo'] = $encabezado_factura->consecutivo;
        $resultado_almacenar['core_tercero_id'] = $encabezado_factura->core_tercero_id;
        $resultado_almacenar['fecha'] = $encabezado_factura->fecha;

        $resultado_almacenar['nombre'] = json_encode($obj_documento_enviado); // CONVERTIR A STRING

        $resultado_almacenar["codigo"] = $resultado['status_code'];

        $resultado_almacenar["fechaRespuesta"] = $resultado['createdDate'];
        $resultado_almacenar["reglasNotificacionDIAN"] = '';

        $resultado_almacenar['cufe'] = $resultado['xml_document_key'];
        $resultado_almacenar['consecutivoDocumento'] = $resultado['xml_file_name'];


        if ($resultado['is_valid'] == 'true') {
            $resultado_almacenar['fechaAceptacionDIAN'] = $resultado['createdDate'];
            $resultado_almacenar['esValidoDian'] = 1;
        } else {
            $resultado_almacenar['fechaAceptacionDIAN'] = '';
            $resultado_almacenar['esValidoDian'] = 0;
        }

        $resultado_almacenar['hash'] = $resultado['xml_document_key'];
        $resultado_almacenar['qr'] = '';
        $resultado_almacenar['xml'] = $resultado['xml_base64'];
        $resultado_almacenar['resultado'] = $resultado['status_message'];


        if ($resultado['is_valid'] == 'false') {

            if (($resultado['error_message']) != '') {
                $resultado_almacenar['esValidoDian'] = 0;
                $resultado_almacenar['fechaAceptacionDIAN'] = '';
                $resultado_almacenar["reglasNotificacionDIAN"] = $resultado['error_message'];
                $resultado_almacenar['resultado'] = 'Rechazado por la DIAN';
            }
        }

        return $resultado_almacenar;
    }

    public function get_mensaje($resultado)
    {
        $mensaje = (object)[
            'tipo' => '',
            'contenido' => ''
        ];

        switch ($resultado['is_valid']) {
            case 'true':
                // ✅ Aceptado por la DIAN
                $mensaje->tipo = 'flash_message';
                $mensaje->contenido = '<h3><i class="fa fa-check"></i> Documento enviado correctamente hacia la DIAN</h3>';
                $mensaje->contenido .= "Código: " . $resultado["status_code"] . "</br>Consecutivo:  " . $resultado["xml_file_name"] . "</br>CUFE:  " . $resultado["xml_document_key"] . "</br>Fecha de Respuesta:  " . $resultado["createdDate"] . "</br>Reglas de validación DIAN:  " . $resultado["reglasNotificacionDIAN"] . "</br>Resultado:  " . $resultado["status_description"];
                break;

            default:
                $mensaje->tipo = 'mensaje_error';
                $mensaje->contenido = '<h3><i class="fa fa-warning"></i> Documento fue enviado a la DIAN. Presenta errores de validación.</h3>';
                $mensaje->contenido .= "Código: " . $resultado["status_code"] . "</br>Fecha de Respuesta:  " . $resultado["createdDate"] . "</br>Mensaje Validación:  " . $resultado["status_description"];
                $mensaje->contenido .= "</br>" . $resultado["reglasNotificacionDIAN"];
                break;
        }

        return $mensaje;
    }
}
