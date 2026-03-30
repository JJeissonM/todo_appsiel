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
                break;
        }

        $this->cantidadDecimales = config('facturacion_electronica.cantidadDecimales');
        $this->env = config('facturacion_electronica.fe_ambiente');
    }

    protected function sanitizeJsonText($value)
    {
        if ($value === null) {
            return '';
        }

        $value = (string)$value;
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', ' ', $value);
        $value = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    protected function jsonString($value)
    {
        $value = $this->sanitizeJsonText($value);

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json !== false) {
            return $json;
        }

        $json = json_encode(utf8_encode($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json !== false) {
            return $json;
        }

        return '""';
    }

    protected function normalizeDateToDmy($value, $default = '')
    {
        if (empty($value)) {
            return $default;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        $value = trim((string)$value);
        if ($value === '') {
            return $default;
        }

        $formats = ['Y-m-d', 'Y-m-d H:i:s', 'd/m/Y', 'd-m-Y', 'Y/m/d'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date instanceof \DateTime) {
                return $date->format('d/m/Y');
            }
        }

        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('d/m/Y', $timestamp);
        }

        return $default;
    }

    protected function isValidEmail($email)
    {
        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function decodeErrorResponseBody($responseBody)
    {
        $decodedBody = json_decode($responseBody, true);
        $errorMessage = '';

        if (is_array($decodedBody)) {
            if (!empty($decodedBody['message'])) {
                $errorMessage = $decodedBody['message'];
            } elseif (!empty($decodedBody['error'])) {
                $errorMessage = $decodedBody['error'];
            } elseif (!empty($decodedBody['errors'])) {
                $errorMessage = is_array($decodedBody['errors']) ? json_encode($decodedBody['errors'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $decodedBody['errors'];
            } else {
                $errorMessage = json_encode($decodedBody, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        } else {
            $errorMessage = trim($responseBody);
        }

        return $errorMessage;
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
                $json_doc_electronico_enviado = [];
                break;
        }

        return $this->enviar_documento_electronico($auth_token, $json_doc_electronico_enviado, $factura_doc_encabezado->get_label_documento());
    }

    public function enviar_documento_electronico($auth_token, $json_doc_electronico_enviado)
    {
        if (isset($json_doc_electronico_enviado['tipo']) && $json_doc_electronico_enviado['tipo'] == 'mensaje_error') {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => $json_doc_electronico_enviado['contenido']
            ];
        }

        if (!is_array($json_doc_electronico_enviado)) {
            $json_doc_electronico_enviado = json_decode($json_doc_electronico_enviado, true);

            $invoiceLegalName = isset($json_doc_electronico_enviado['invoice']['customer']['legal_name']) ? $json_doc_electronico_enviado['invoice']['customer']['legal_name'] : '';
            $creditNoteLegalName = isset($json_doc_electronico_enviado['credit_note']['customer']['legal_name']) ? $json_doc_electronico_enviado['credit_note']['customer']['legal_name'] : '';

            if (($invoiceLegalName !== '' && preg_match('/[^a-zA-Z0-9\s.]/', $invoiceLegalName)) || ($creditNoteLegalName !== '' && preg_match('/[^a-zA-Z0-9\s.]/', $creditNoteLegalName))) {
                return (object)[
                    'tipo' => 'mensaje_error',
                    'contenido' => 'Error de cliente: El nombre de la empresa no puede contener caracteres no alfanumericos'
                ];
            }
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'El JSON esta mal formado: ' . json_last_error_msg()
            ];
        }

        try {
            $client = new \GuzzleHttp\Client(['base_uri' => $this->url_emision]);

            $response = $client->post($this->url_emision, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'auth-token' => $auth_token,
                ],
                'json' => $json_doc_electronico_enviado,
            ]);

            $responseBody = (string) $response->getBody();

            Log::info('Cuerpo de respuesta cruda de OSEI: ' . $responseBody);

            $array_respuesta = json_decode($responseBody, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error al decodificar JSON de OSEI: ' . json_last_error_msg() . ' Cuerpo recibido: ' . $responseBody);
                return (object)[
                    'tipo' => 'mensaje_error',
                    'contenido' => 'Error interno: OSEI envio JSON invalido. Mensaje: ' . json_last_error_msg()
                ];
            }

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
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
            $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'No response body';
            Log::warning('Error 4xx de OSEI al enviar documento. HTTP ' . $statusCode . '. Respuesta: ' . $responseBody);

            $errorMessage = $this->decodeErrorResponseBody($responseBody);

            if ($errorMessage === '') {
                $errorMessage = 'HTTP ' . $statusCode . ' sin detalle retornado por OSEI.';
            }

            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'Error de Empresa: ' . $errorMessage
            ];
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
            $responseBody = $e->getResponse() ? (string) $e->getResponse()->getBody() : 'No response body';
            Log::error('Error 5xx de OSEI al enviar documento. HTTP ' . $statusCode . '. Respuesta: ' . $responseBody);

            $errorMessage = $this->decodeErrorResponseBody($responseBody);
            if ($errorMessage === '') {
                $errorMessage = 'HTTP ' . $statusCode . ' sin detalle retornado por OSEI.';
            }

            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'Error de servidor OSEI: ' . $errorMessage
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'Error de red/peticion: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return (object)[
                'tipo' => 'mensaje_error',
                'contenido' => 'Error inesperado: ' . $e->getMessage() . ' Linea: ' . $e->getLine() . ' Archivo: ' . $e->getFile()
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
                    throw new \Exception('Error al decodificar JSON: ' . json_last_error_msg());
                }
            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
                return [
                    'tipo' => 'mensaje_error',
                    'contenido' => "Error HTTP $statusCode al consultar el documento, batch en proceso de validacion por la DIAN"
                ];
            } catch (\Exception $e) {
                return [
                    'tipo' => 'mensaje_error',
                    'contenido' => 'Excepcion: ' . $e->getMessage(),
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

        return '{ "actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": ' . $this->jsonString($lista_emails) . '},"invoice": {' . $this->get_encabezado_factura($auth_token) . ',"items": ' . $this->get_lineas_registros() . ',"charges": []},"aditional_info": ' . $this->get_aditional_info() . '}';
    }

    public function preparar_cadena_json_nota_credito($auth_token, $factura_doc_encabezado)
    {
        $send_dian = 'true';
        $send_email = config('facturacion_electronica.enviar_email_clientes');

        $lista_emails = $this->doc_encabezado->cliente->tercero->email;
        if (config('facturacion_electronica.email_copia_factura') != '') {
            $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
        }

        if (!$this->isValidEmail($this->doc_encabezado->cliente->tercero->email)) {
            $send_email = 'false';
            $lista_emails = config('facturacion_electronica.email_copia_factura');
        }

        $prefixFE = $factura_doc_encabezado->tipo_documento_app->prefijo;
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
            $billing_reference_issue_date = $this->normalizeDateToDmy($response_dian['contenido']['issue_date'] ?? '', $this->normalizeDateToDmy($factura_doc_encabezado->fecha));
        }

        return '{"actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": ' . $this->jsonString($lista_emails) . '},"credit_note": {' . $this->get_encabezado_nota_credito($auth_token, $invoice_id, $prefixFE, $numberFE, $billing_reference_issue_date) . ',"customer": ' . $this->get_datos_cliente() . ',"items": ' . $this->get_lineas_registros() . ',"charges": []},"aditional_info": ' . $this->get_aditional_info() . '}';
    }

    public function get_aditional_info()
    {
        return (new PrintServices())->get_etiquetas_for_osei();
    }

    public function get_encabezado_factura($auth_token)
    {
        $payment_means_type = 'DEBITO';
        if ($this->doc_encabezado->forma_pago == 'credito') {
            $payment_means_type = 'CREDITO';
        }

        $payment_means = 'MUTUAL_AGREEMENT';

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
        if (!empty($this->doc_encabezado->descripcion)) {
            $notes = $this->sanitizeJsonText($this->doc_encabezado->descripcion);

            $arr_notes = explode(' ', $notes);
            $el_primero = true;
            foreach ($arr_notes as $value) {
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
        $start_date = '';
        $end_date = '';
        $from = 0;
        $to = 0;

        if ($resolucionFactura != null) {
            $start_date = date_format(date_create($resolucionFactura->fecha_expedicion), 'd/m/Y');
            $end_date = date_format(date_create($resolucionFactura->fecha_expiracion), 'd/m/Y');
            $from = $resolucionFactura->numero_fact_inicial;
            $to = $resolucionFactura->numero_fact_final;
        }

        return '"env": ' . $this->jsonString($this->env) . ',"authorization_token": ' . $this->jsonString($auth_token) . ',"number":' . $this->doc_encabezado->consecutivo . ',"issue_date": ' . $this->jsonString(date_format(date_create($this->doc_encabezado->fecha), 'd/m/Y')) . ',"payment_date": ' . $this->jsonString(date_format(date_create($fecha_vencimiento), 'd/m/Y')) . ',"invoice_type_code": ' . $this->jsonString($this->invoice_type_code) . ',"payment_means_type": ' . $this->jsonString($payment_means_type) . ',"payment_means": ' . $this->jsonString($payment_means) . ',"currency":' . $this->jsonString($currency) . ',"resolution":{"number":' . $this->jsonString($resolucion->numero_resolucion) . ',"prefix":' . $this->jsonString($resolucion->prefijo) . ',"flexible":' . $this->jsonString($flexible) . ',"start_date": ' . $this->jsonString($start_date) . ',"end_date": ' . $this->jsonString($end_date) . ',"from": ' . $from . ',"to": ' . $to . '},"anotation":' . $this->jsonString($notes2) . ', "customer": ' . $this->get_datos_cliente();
    }

    public function get_encabezado_nota_credito($auth_token, $invoice_id, $prefixFE, $numberFE, $billing_reference_issue_date)
    {
        $payment_means_type = $this->doc_encabezado->forma_pago == 'credito' ? 'CREDITO' : 'DEBITO';
        $payment_means = 'MUTUAL_AGREEMENT';
        $purpose_code = 1;
        $reason = 'DEVOLUCION';
        $issue_date = $this->normalizeDateToDmy($this->doc_encabezado->fecha, date('d/m/Y'));
        $payment_date = $this->normalizeDateToDmy($this->doc_encabezado->fecha_vencimiento, $issue_date);

        $currency = 'COP';
        $type = 'NOTA_CREDITO';
        $notes = '-';
        $notes2 = '-';
        if (!empty($this->doc_encabezado->descripcion)) {
            $notes = $this->sanitizeJsonText($this->doc_encabezado->descripcion);

            $arr_notes = explode(' ', $notes);
            $el_primero = true;
            foreach ($arr_notes as $value) {
                if ($el_primero) {
                    $notes2 = $value;
                    $el_primero = false;
                } else {
                    $notes2 .= ' ' . $value;
                }
            }
        }

        return '"env": ' . $this->jsonString($this->env) . ',"anotation":' . $this->jsonString($notes2) . ',"type":' . $this->jsonString($type) . ',"authorization_token": ' . $this->jsonString($auth_token) . ',"issue_date": ' . $this->jsonString($issue_date) . ',"payment_means_type": ' . $this->jsonString($payment_means_type) . ',"payment_means": ' . $this->jsonString($payment_means) . ',"payment_date": ' . $this->jsonString($payment_date) . ',"reason": ' . $this->jsonString($reason) . ',"purpose_code": ' . $this->jsonString($purpose_code) . ',"number":' . $this->doc_encabezado->consecutivo . ',"currency":' . $this->jsonString($currency) . ',"related_invoice":{"cufe":' . $this->jsonString($invoice_id) . ',"number":' . $this->jsonString($numberFE) . ',"prefix":' . $this->jsonString($prefixFE) . ',"issue_date":' . $this->jsonString($this->normalizeDateToDmy($billing_reference_issue_date, $issue_date)) . '}' . ',"resolution":{"prefix":' . $this->jsonString($this->doc_encabezado->tipo_documento_app->prefijo) . '}';
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

            $price = $linea->precio_unitario / (1 + $linea->tasa_impuesto / 100);
            $original_price = 0;
            if ($price == 0) {
                $price = $linea->item->get_costo_promedio() * (1 + 10 / 100);
                $original_price = $price;
                $linea->tasa_descuento = 100;
            }

            $unidad_medida = $linea->item->unidad_medida1;

            $quantity = abs((float) $linea->cantidad);
            $priceValue = abs((float) $price);
            $string_items .= '{"sku": ' . $this->jsonString($linea->item->id) . ',"u.m": ' . $this->jsonString($unidad_medida) . ',"description": ' . $this->jsonString($linea->item->descripcion) . ',"quantity": ' . number_format($quantity, $this->cantidadDecimales, '.', '') . ',"price": ' . number_format($priceValue, $this->cantidadDecimales, '.', '');

            if ($original_price != 0) {
                $string_items .= ',"original_price": ' . $original_price;
            }

            if ($linea->tasa_descuento != 0) {
                $string_items .= ',"discount_rate": ' . $linea->tasa_descuento;
            }

            $tax_category = config('ventas.etiqueta_impuesto_principal');
            $impuesto = $linea->impuesto;

            if ($impuesto == null) {
                $impuesto = $linea->item->impuesto;
            }
            if ($impuesto->tax_category != null && $impuesto->tax_category != '') {
                $tax_category = $impuesto->tax_category;
            }
            $vlor_total_desc = abs((float) $linea->valor_total_descuento);
            $taxable_amount = max(($quantity * $priceValue) - $vlor_total_desc, 0);

            $string_items .= ',"taxes": [  {    "tax_rate": ' . number_format((float) $linea->tasa_impuesto, $this->cantidadDecimales, '.', '') . ',"taxable_amount": ' . number_format($taxable_amount, $this->cantidadDecimales, '.', '') . ',"total_discount": ' . number_format($vlor_total_desc, $this->cantidadDecimales, '.', '') . ',"tax_category": ' . $this->jsonString($tax_category) . '}]}';
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

        if ($cliente->tercero->tipo == 'Persona natural') {
            $party_type = 'PERSONA_NATURAL';
        }

        if ($cliente->tercero->razon_social != '') {
            $legal_name = $cliente->tercero->razon_social;
            $trade_name = $cliente->tercero->descripcion;
        }

        if (!is_string($tax_level_code) || trim($tax_level_code) === '') {
            $tax_level_code = 'O-47';
        }

        $tax_scheme_id = '01';
        if ($cliente->tercero->numero_identificacion == '222222222222' || $cliente->tercero->numero_identificacion == '222222222') {
            $tax_scheme_id = 'ZZ';
            $legal_name = 'CONSUMIDOR FINAL';
            $trade_name = '';
        }
        $department_id = substr($cliente->tercero->ciudad->id, 3, 2);
        $city_id = substr($cliente->tercero->ciudad->id, 5, strlen($cliente->tercero->ciudad->id) - 1);

        $address_line = $cliente->tercero->ciudad->descripcion;
        if ($cliente->tercero->direccion1 != '') {
            $address_line = $cliente->tercero->direccion1;
        }
        $verification_digit = $cliente->tercero->digito_verificacion;
        if ($tax_scheme_id == 'ZZ') {
            $verification_digit = '';
        }

        $party_identification_type = $cliente->tercero->id_tipo_documento_id;

        return '{"email": ' . $this->jsonString($cliente->tercero->email) . ',"phone": ' . $this->jsonString($cliente->tercero->telefono1) . ',"type": ' . $this->jsonString($party_type) . ',"legal_name": ' . $this->jsonString($legal_name) . ',"trade_name":' . $this->jsonString($trade_name) . ',"identification_number": ' . $this->jsonString($cliente->tercero->numero_identificacion) . ',"identification_type": ' . $this->jsonString($party_identification_type) . ',"verification_digit": ' . $this->jsonString($verification_digit) . ',"tax_level_code": ' . $this->jsonString($tax_level_code) . ',"tax_scheme_id": ' . $this->jsonString($tax_scheme_id) . ',"department": ' . $this->jsonString($department_id) . ',"city": ' . $this->jsonString($city_id) . ',"address_line": ' . $this->jsonString($address_line) . '}';
    }
}
