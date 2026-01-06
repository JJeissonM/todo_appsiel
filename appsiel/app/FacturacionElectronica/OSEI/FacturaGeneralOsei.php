<?php

namespace App\FacturacionElectronica\OSEI;

use App\FacturacionElectronica\Services\DocumentHeaderService;
use App\Ventas\Services\PrintServices;
use Illuminate\Support\Facades\Log;

class FacturaGeneralOsei
{
    protected $doc_encabezado;
    protected $url_emision;
    protected $invoice_type_code;
    protected $cantidadDecimales;
    protected $tipo_transaccion;

    public $env;

    function __construct($doc_encabezado, $tipo_transaccion)
    {
        $this->doc_encabezado = $doc_encabezado;
        $this->tipo_transaccion = $tipo_transaccion;

        switch ($tipo_transaccion) {
            case 'factura':
                $this->url_emision = config('facturacion_electronica.WSDL');
                $this->invoice_type_code = 'FACTURA_VENTA';
                break;

            case 'nota_credito':
                $this->url_emision = config('facturacion_electronica.url_notas_credito');
                $this->invoice_type_code = 'NOTA_CREDITO';
                break;

            case 'nota_debito':
                $this->url_emision = config('facturacion_electronica.url_notas_debito');
                $this->invoice_type_code = 'NOTA_DEBITO';
                break;

            default:
                // code...
                break;
        }

        $this->cantidadDecimales = config('facturacion_electronica.cantidadDecimales');
        $this->env = config('facturacion_electronica.fe_ambiente'); //'PRUEBAS' || 'PRODUCCION'
    }

    public function procesar_envio_factura($factura_doc_encabezado)
    {
        $auth_token = config('facturacion_electronica.tokenEmpresa');
        switch ($this->tipo_transaccion) {
            case 'factura':
                $json_doc_electronico_enviado = $this->preparar_cadena_json_factura($auth_token);
                break;

            case 'nota_credito':
                $json_doc_electronico_enviado = $this->preparar_cadena_json_nota_credito($auth_token, $factura_doc_encabezado);
                break;

            case 'nota_debito':
                $json_doc_electronico_enviado = [];
                break;

            default:
                break;
        }

        return $this->enviar_documento_electronico($auth_token, $json_doc_electronico_enviado, $factura_doc_encabezado->get_label_documento());
    }
    public function enviar_documento_electronico($auth_token, $json_doc_electronico_enviado)
    {
        // solo en nota credito si retorna error, validar que exista el campo tipo si no continue normal 
        if (isset($json_doc_electronico_enviado['tipo']) && $json_doc_electronico_enviado['tipo'] == 'mensaje_error') {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => $json_doc_electronico_enviado['contenido']
            ];
        }

        if (!is_array($json_doc_electronico_enviado)) {
            $json_doc_electronico_enviado = json_decode($json_doc_electronico_enviado, true);

            if (preg_match('/[^a-zA-Z0-9\s.]/', isset($json_doc_electronico_enviado['invoice']['customer']['legal_name'])) || preg_match('/[^a-zA-Z0-9\s.]/', isset($json_doc_electronico_enviado['credit_note']['customer']['legal_name']))) {
                return (object)[
                    'tipo' => 'mensaje_error',
                    'contenido' => "Error de cliente: El nombre de la empresa no puede contener caracteres no alfanuméricos"
                ];
            }
        }
        if (json_last_error() !== JSON_ERROR_NONE) {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'El JSON está mal formado: ' . json_last_error_msg()
            ];
        }

        // Primera petición: enviar documento
        try {
            $client = new \GuzzleHttp\Client(['base_uri' => $this->url_emision]);

            $response = $client->post($this->url_emision, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'auth-token' => $auth_token,
                ],
                'json' => $json_doc_electronico_enviado,
            ]);

            // $array_respuesta = json_decode((string) $response->getBody(), true);
            $responseBody = (string) $response->getBody();

            Log::info('Cuerpo de respuesta cruda de OSEI: ' . $responseBody);

            $array_respuesta = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON de OSEI: ' . json_last_error_msg() . ' Cuerpo recibido: ' . $responseBody);
                return (object)[
                    'tipo' => 'mensaje_error',
                    'contenido' => "Error interno: OSEI envió JSON inválido. Mensaje: " . json_last_error_msg()
                ];
            }
            //Validar que exista el campo is_valid
            if (isset($array_respuesta['is_valid'])) {
                $obj_resultado = new ResultadoEnvioOsei();
                $mensaje = $obj_resultado->almacenar_resultado(
                    $array_respuesta,
                    $json_doc_electronico_enviado,
                    $this->doc_encabezado->id
                );
                return json_decode(json_encode($mensaje));
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Esto captura errores 4xx
            $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'No response body';
            $responseBody = json_decode($responseBody, true);
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => "Error de Empresa: " . $responseBody['message']
            ];
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            // Esto captura errores 5xx
            return (object)[

                'tipo' => 'mensaje_error',
                'contenido' => "Error de servidor: Este es un error de conexión intente nuevamente."
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // Esto captura errores de red, DNS, timeouts, etc.
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => "Error de red/petición: " . $e->getMessage()
            ];
        } catch (\Exception $e) {
            // Esto captura cualquier otra excepción general
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => "Error inesperado: " . $e->getMessage() . " Línea: " . $e->getLine() . " Archivo: " . $e->getFile()
            ];
        }

        if (!isset($array_respuesta['zip_key'], $array_respuesta['CompanyNIT'])) {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'La respuesta del servidor no contiene zip_key o CompanyNIT.'
            ];
        }

        $zip_key = $array_respuesta['zip_key'];
        $company_nit = $array_respuesta['CompanyNIT'];

        if ($json_doc_electronico_enviado['invoice']['env'] == 'PRUEBAS' ?? $json_doc_electronico_enviado['support_doc']['env'] == 'PRUEBAS') {
            $env = 'testing';
            $endpointGetStatusZip = "https://osei.com.co/api/v1/invoices/get_status_zip/{$zip_key}/{$company_nit}/{$env}";
            try {
                $client = new \GuzzleHttp\Client();
                $response = $client->get($endpointGetStatusZip, [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'auth-token' => $auth_token,
                    ]
                ]);
                $bodyContent = (string) $response->getBody();
                $response_dian = json_decode($bodyContent, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception("Error al decodificar JSON: " . json_last_error_msg());
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
                return [
                    'tipo' => 'mensaje_error',
                    'contenido' => "Error HTTP $statusCode al consultar el Documento, batch en proceso de validacion por la DIAN"
                ];
            } catch (\Exception $e) {
                return [
                    'tipo' => 'mensaje_error',
                    'contenido' => "Excepción: " . $e->getMessage(),
                ];
            }

            $obj_resultado = new ResultadoEnvioOsei();
            $mensaje = $obj_resultado->almacenar_resultado(
                $response_dian,
                $json_doc_electronico_enviado,
                $this->doc_encabezado->id
            );
        }

        return json_decode(json_encode($mensaje));
    }


    public function preparar_cadena_json_factura($auth_token)
    {
        $send_dian = 'true';
        $send_email = config('facturacion_electronica.enviar_email_clientes');

        $lista_emails = $this->doc_encabezado->cliente->tercero->email;
        if (config('facturacion_electronica.email_copia_factura') != '') {
            $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
        }

        return '{ "actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": "' . $lista_emails . '"},"invoice": {' . $this->get_encabezado_factura($auth_token) . ',"items": ' . $this->get_lineas_registros() . ',"charges": []},"aditional_info": ' . $this->get_aditional_info() . '}';
    }

    public function preparar_cadena_json_nota_credito($auth_token, $factura_doc_encabezado)
    {
        $send_dian = 'true';
        $send_email = config('facturacion_electronica.enviar_email_clientes');

        $lista_emails = $this->doc_encabezado->cliente->tercero->email;
        if (config('facturacion_electronica.email_copia_factura') != '') {
            $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
        }

        $prefixFE = $factura_doc_encabezado->tipo_documento_app->prefijo;;
        $numberFE = (string) $factura_doc_encabezado->consecutivo;

        $endpointGetCufe = "https://osei.com.co/api/v1/invoices/get_cufe/{$prefixFE}/{$numberFE}/{$auth_token}";
        $cURL = curl_init();
        curl_setopt($cURL, CURLOPT_URL, $endpointGetCufe);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        $response_dian = curl_exec($cURL);
        curl_close($cURL);
        $response_dian = json_decode($response_dian, true);

        if ($response_dian['tipo'] == 'mensaje_error') {
            return [
                'tipo' => 'mensaje_error',
                'contenido' => $response_dian['contenido']
            ];
        } else {
            $invoice_id = $response_dian['contenido']['cufe'];
            $billing_reference_issue_date = $response_dian['contenido']['issue_date'];
        }

        return '{"actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": "' . $lista_emails . '"},"credit_note": {' . $this->get_encabezado_nota_credito($auth_token, $invoice_id, $prefixFE, $numberFE, $billing_reference_issue_date) . ',"customer": ' . $this->get_datos_cliente() . ',"items": ' . $this->get_lineas_registros() . ',"charges": []},"aditional_info": ' . $this->get_aditional_info() . '}';
    }

    public function get_aditional_info()
    {
        return (new PrintServices())->get_etiquetas_for_osei();
    }

    public function get_encabezado_factura($auth_token)
    {
        $payment_means_type = 'DEBITO'; // Contado
        if ($this->doc_encabezado->forma_pago == 'credito') {
            $payment_means_type = 'CREDITO';
        }

        $payment_means = 'MUTUAL_AGREEMENT'; //  Medio de pago

        $resolucion = (object)['prefijo' => 'FEE', 'numero_resolucion' => 18760000001];
        if ($this->env == 'PRODUCCION') {
            $resolucion = $this->doc_encabezado->resolucion_facturacion();
        }

        if ($resolucion == null) {
            $resolucion = (object)['prefijo' => $this->doc_encabezado->tipo_documento_app->prefijo, 'numero_resolucion' => 18760000001];
        }

        $flexible = 'true';

        $notes = '-';
        $notes2 = '-';
        if ($this->doc_encabezado->descripcion != null || $this->doc_encabezado->descripcion != '') {
            $notes = trim(str_replace('"', '\"', $this->doc_encabezado->descripcion));

            $arr_notes = explode(' ', $notes);
            $el_primero = true;
            foreach ($arr_notes as $key => $value) {
                if ($el_primero) {
                    $notes2 = $value;
                    $el_primero = false;
                } else {
                    $notes2 .= ' ' . $value;
                }
            }
        }

        $fecha_vencimiento = $this->doc_encabezado->fecha_vencimiento;
        if (explode('-', $fecha_vencimiento)[0] == '0000') {
            $fecha_vencimiento = $this->doc_encabezado->fecha;
        }
        $currency = 'COP';
        $resolucionFactura = $this->doc_encabezado->resolucion_facturacion();
        $start_date = date_format(date_create($resolucionFactura->fecha_expedicion), 'd/m/Y');
        $end_date = date_format(date_create($resolucionFactura->fecha_expiracion), 'd/m/Y');
        $from = $resolucionFactura->numero_fact_inicial;
        $to = $resolucionFactura->numero_fact_final;

        return '"env": "' . $this->env . '","authorization_token": "' . $auth_token . '","number":' . $this->doc_encabezado->consecutivo . ',"issue_date": "' . date_format(date_create($this->doc_encabezado->fecha), 'd/m/Y') . '","payment_date": "' . date_format(date_create($fecha_vencimiento), 'd/m/Y') . '","invoice_type_code": "' . $this->invoice_type_code . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","currency":"' . $currency . '","resolution":{"number":"' . $resolucion->numero_resolucion . '","prefix":"' . $resolucion->prefijo . '","flexible":"' . $flexible . '","start_date": "' . $start_date . '","end_date": "' . $end_date . '","from": ' . $from . ',"to": ' . $to . '},"anotation":"' . $notes2 . '", "customer": ' . $this->get_datos_cliente();
    }

    public function get_encabezado_nota_credito($auth_token, $invoice_id, $prefixFE, $numberFE, $billing_reference_issue_date)
    {

        if (isset($json_dataico->invoice)) {
            $invoice_id = $json_dataico->invoice->uuid;
        }

        $payment_means_type = 'CREDITO';

        $payment_means = 'CREDITO'; //  Medio de pago

        $purpose_code = 1;

        $reason = 'DEVOLUCION';
        /**********  List [ "DEVOLUCION", "ANULACION", "REBAJA", "DESCUENTO", "RECISION", "OTROS" ]    PENDIENTE    ******/
        $issue_date = date_format(date_create($this->doc_encabezado->fecha), 'd/m/Y');
        $fecha_vencimiento = date_create($this->doc_encabezado->fecha_vencimiento);
        $payment_date = date_format(date_add($fecha_vencimiento, date_interval_create_from_date_string("1 month")), 'd/m/Y');


        $currency = 'COP';
        $type = 'NOTA_CREDITO';
        $notes = '-';
        $notes2 = '-';
        if ($this->doc_encabezado->descripcion != null || $this->doc_encabezado->descripcion != '') {
            $notes = trim(str_replace('"', '\"', $this->doc_encabezado->descripcion));

            $arr_notes = explode(' ', $notes);
            $el_primero = true;
            foreach ($arr_notes as $key => $value) {
                if ($el_primero) {
                    $notes2 = $value;
                    $el_primero = false;
                } else {
                    $notes2 .= ' ' . $value;
                }
            }
        }

        return '"env": "' . $this->env . '","anotation":"' . $notes2 . '","type":"' . $type . '","authorization_token": "' . $auth_token . '","issue_date": "' . $issue_date . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","payment_date": "' . $payment_date . '","reason": "' . $reason . '","purpose_code": "' . $purpose_code . '","number":' . $this->doc_encabezado->consecutivo . ',"currency":"' . $currency . '","related_invoice":{"cufe":"' . $invoice_id . '","number":"' . $numberFE . '","prefix":"' . $prefixFE . '","issue_date":"' . $billing_reference_issue_date . '"}' . ',"resolution":{"prefix":"' . $this->doc_encabezado->tipo_documento_app->prefijo . '"}';
    }
    public function get_lineas_registros()
    {
        $string_items = '[';

        $lineas_registros = $this->doc_encabezado->lineas_registros;
        $es_primera_linea = true;
        foreach ($lineas_registros as $linea) {

            if (!$es_primera_linea) {
                $string_items .= ',';
            }

            /**
             * Se envia el precio unitario sin haber quitado el decuento. DATAICO hace el calculo de descuento en su plataforma con base en discount_rate.
             */
            $price = $linea->precio_unitario / (1 + $linea->tasa_impuesto / 100);
            $original_price = 0;
            if ($price == 0) { // Obsequio o Precio cero
                $price = $linea->item->get_costo_promedio() * (1 + 10 / 100);

                $original_price = $price;

                $linea->tasa_descuento = 100;
            }
            $unidad_medida = $linea->item->unidad_medida1;

            $string_items .= '{"sku": "' . $linea->item->id . '","u.m": "' . $unidad_medida . '","description": "' . str_replace('"', '\"', $linea->item->descripcion) . '","quantity": ' . abs(number_format($linea->cantidad, $this->cantidadDecimales, '.', '')) . ',"price": ' . abs(number_format($price, $this->cantidadDecimales, '.', ''));

            if ($original_price != 0) {
                $string_items .= ',"original_price": ' . $original_price;
            }

            if ($linea->tasa_descuento != 0) {
                $string_items .= ',"discount_rate": ' . $linea->tasa_descuento;
            }

            $tax_category = config('ventas.etiqueta_impuesto_principal');
            
            $impuesto = $linea->impuesto;

            if ( $impuesto == null ) {
                $impuesto = $linea->item->impuesto;
            }
            if ( $impuesto->tax_category != null && $impuesto->tax_category != '' ) {
                $tax_category = $impuesto->tax_category;
            }
            $vlor_total_desc = $linea->valor_total_descuento;

            $string_items .= ',"taxes": [  {    "tax_rate": ' . $linea->tasa_impuesto . ',"total_discount": ' . $vlor_total_desc . ',"tax_category": "' . $tax_category . '"}]}';
            $es_primera_linea = false;
        }

        $string_items .= ']';

        return $string_items;
    }
    public function get_datos_cliente()
    {
        $cliente = (new DocumentHeaderService())->get_cliente($this->doc_encabezado);

        $party_type = 'PERSONA_JURIDICA';
        $legal_name = $cliente->tercero->descripcion;
        $trade_name = '';
        $tax_level_code = $cliente->tercero->tax_level_code;

        if ($cliente->tercero->razon_social != '') {
            $legal_name = $cliente->tercero->razon_social;
            $trade_name = $cliente->tercero->descripcion;
        }

        $tax_scheme_id = '01'; // 01 = IVA
        if ($cliente->tercero->numero_identificacion == '222222222222' || $cliente->tercero->numero_identificacion == '222222222') {
            $tax_scheme_id = 'ZZ';
        }
        $department_id = substr($cliente->tercero->ciudad->id, 3, 2);
        $city_id = substr($cliente->tercero->ciudad->id, 5, strlen($cliente->tercero->ciudad->id) - 1);

        $address_line = $cliente->tercero->ciudad->descripcion;
        if ($cliente->tercero->direccion1 != '') {
            $address_line = $cliente->tercero->direccion1;
        }
        $verification_digit = $cliente->tercero->digito_verificacion;

        $party_identification_type = $cliente->tercero->id_tipo_documento_id;

        return '{"email": "' . $cliente->tercero->email . '","phone": "' . $cliente->tercero->telefono1 . '","type": "' . $party_type . '","legal_name": "' . $legal_name . '","trade_name":"' . $trade_name . '","identification_number": "' . $cliente->tercero->numero_identificacion . '","identification_type": "' . $party_identification_type . '","verification_digit": "' . $verification_digit . '","tax_level_code": "' . $tax_level_code . '","tax_scheme_id": "' . $tax_scheme_id . '","department": "' . $department_id . '","city": "' . $city_id . '","address_line": "' . $address_line . '"}';
    }
}
