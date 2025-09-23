<?php

namespace App\FacturacionElectronica\OSEI;

use App\Compras\Services\ContabilidadService;
use GuzzleHttp\Client;

// declaramos factura
class DocSoporte
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
         case 'support_doc':
            $this->url_emision = config('facturacion_electronica.url_documento_soporte');
            $this->invoice_type_code = 'FACTURA_VENTA';
            break;

         default:
            // code...
            break;
      }

      $this->cantidadDecimales = config('facturacion_electronica.cantidadDecimales');
      $this->env = config('facturacion_electronica.fe_ambiente'); //'PRUEBAS' || 'PRODUCCION'
   }

   public function procesar_envio_factura()
   {
      switch ($this->tipo_transaccion) {
         case 'support_doc':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_doc_soporte();
            break;

         default:
            // code...
            break;
      }

      $tokenPassword = config('facturacion_electronica.tokenPassword');

      return $this->enviar_documento_electronico($tokenPassword, $json_doc_electronico_enviado, $this->doc_encabezado->get_label_documento());
   }

   public function enviar_documento_electronico($tokenPassword, $json_doc_electronico_enviado)
   {
      if (!is_array($json_doc_electronico_enviado)) {
         $json_doc_electronico_enviado = json_decode($json_doc_electronico_enviado, true);

         if (preg_match('/[^a-zA-Z0-9\s.]/', isset($json_doc_electronico_enviado['support_doc']['supplier']['legal_name']))) {
            return (object)[
               'tipo' => 'mensaje_error',
               'contenido' => "Error de cliente: El nombre de la empresa no puede contener caracteres no alfanuméricos"
            ];
         }
      }

      // verificar que el json este bien formado
      if (json_last_error() !== JSON_ERROR_NONE) {
         return (object)[
            'tipo' => 'mensaje_error',
            'contenido' => "Error interno: JSON inválido. Mensaje: " . json_last_error_msg()
         ];
      }
      try {
         $client = new \GuzzleHttp\Client(['base_uri' => $this->url_emision]);

         $response = $client->post($this->url_emision, [
            'headers' => [
               'Content-Type' => 'application/json',
               'auth-token' => $tokenPassword,
            ],
            'json' => $json_doc_electronico_enviado,
         ]);

         // $array_respuesta = json_decode((string) $response->getBody(), true);
         $responseBody = (string) $response->getBody();

         \Log::info('Cuerpo de respuesta cruda de OSEI: ' . $responseBody);

         $array_respuesta = json_decode($responseBody, true);

         if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('Error al decodificar JSON de OSEI: ' . json_last_error_msg() . ' Cuerpo recibido: ' . $responseBody);
            return (object)[
               'tipo' => 'mensaje_error',
               'contenido' => "Error interno: OSEI envió JSON inválido. Mensaje: " . json_last_error_msg()
            ];
         }
         //Validar que exista el campo is_valid
         if (isset($array_respuesta['is_valid'])) {
            $obj_resultado = new ResultadoEnvioDocSoporte();
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

      if ($json_doc_electronico_enviado['support_doc']['env'] == 'PRUEBAS') {
         $env = 'testing';
         $endpointGetStatusZip = "https://osei.com.co/api/v1/invoices/get_status_zip/{$zip_key}/{$company_nit}/{$env}";
         try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($endpointGetStatusZip, [
               'headers' => [
                  'Content-Type' => 'application/json',
                  'auth-token' => $tokenPassword,
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

         $obj_resultado = new ResultadoEnvioDocSoporte();
         $mensaje = $obj_resultado->almacenar_resultado(
            $response_dian,
            $json_doc_electronico_enviado,
            $this->doc_encabezado->id
         );
      }


      return json_decode(json_encode($mensaje));
   }

   public function preparar_cadena_json_doc_soporte()
   {
      $send_dian = 'true';
      $send_email = config('facturacion_electronica.enviar_email_clientes');

      $lista_emails = $this->doc_encabezado->proveedor->tercero->email;
      if (config('facturacion_electronica.email_copia_factura') != '') {
         $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
      }
      return '{ "actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": "' . $lista_emails . '"},"support_doc": {' . $this->get_encabezado_factura() . '}}';
   }

   public function get_encabezado_factura()
   {

      $payment_means_type = 'DEBITO'; // Contado
      if ($this->doc_encabezado->forma_pago == 'credito') {
         $payment_means_type = 'CREDITO';
      }

      $payment_means = 'MUTUAL_AGREEMENT'; //  Medio de pago

      if ($this->env == 'PRODUCCION') {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      } else {
         $resolucion = (object)['prefijo' => 'CONR', 'numero_resolucion' => 18763647, 'fecha_expedicion' => '2023-01-01', 'fecha_expiracion' => '2024-01-01', 'numero_fact_inicial' => 1, 'numero_fact_final' => 1000];
      }
      $start_date = date_format(date_create($resolucion->fecha_expedicion), 'd/m/Y');
      $end_date = date_format(date_create($resolucion->fecha_expiracion), 'd/m/Y');
      $from = $resolucion->numero_fact_inicial;
      $to = $resolucion->numero_fact_final;

      $flexible = 'true';
      $currency = 'COP';
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

      return '"env": "' . $this->env . '","anotation":"' . $notes2 . '","authorization_token": "' . config('facturacion_electronica.tokenEmpresa') . '","number":' . $this->doc_encabezado->consecutivo . ',"issue_date": "' . date_format(date_create($this->doc_encabezado->fecha), 'd/m/Y') . '","payment_date": "' . date_format(date_create($this->doc_encabezado->fecha_vencimiento), 'd/m/Y') . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","currency":"' . $currency . '","resolution":{"number":"' . $resolucion->numero_resolucion . '","prefix":"' . $resolucion->prefijo . '","flexible": "' . $flexible . '","start_date" : "' . $start_date . '","end_date": "' . $end_date . '","from": "' . $from . '" ,"to": " ' . $to . '"}, "supplier": ' . $this->get_datos_cliente() . ',"items": ' . $this->get_lineas_registros() . ',"retentions": ' . $this->get_document_retentions();
      // . ',"charges": []'
   }

   public function get_einvoice_in_dataico()
   {
      if ($this->env == 'PRODUCCION') {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      } else {
         $resolucion = (object)['prefijo' => 'SETT', 'numero_resolucion' => 18760000001];
      }

      /*
               DATOS APPSIEL SAS - Para Pruebas      
         Dataico Account Id: a2532b03-a8bf-4514-a4e8-5fd7ec0499e9
         Dataico Auth Token: 088c164ef2ff8964cca84f76e8059f18
         $tokenPassword = '088c164ef2ff8964cca84f76e8059f18';
         $prefijo_resolucion = 'DS';
         $consecutivo_doc_encabezado = 14;
         $url_emision = 'https://api.dataico.com/dataico_api/v2/support_docs';
      */
      /*
*/
      $tokenPassword = config('facturacion_electronica.tokenPassword');

      $prefijo_resolucion = $resolucion->prefijo;
      $consecutivo_doc_encabezado = $this->doc_encabezado->consecutivo;
      $url_emision = $this->url_emision;

      try {
         $client = new Client(['base_uri' => $url_emision]);

         $response = $client->get($this->url_emision . '?prefix=' . $prefijo_resolucion . '&number=' . $consecutivo_doc_encabezado, [
            // un array con la data de los headers como tipo de peticion, etc.
            'headers' => [
               'content-type' => 'application/json',
               'auth-token' => $tokenPassword
            ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
         $response = $e->getResponse();
      }


      return json_decode((string) $response->getBody());
   }

   public function get_datos_cliente()
   {
      $cliente = $this->doc_encabezado->proveedor;

      $party_identification_type = $cliente->tercero->id_tipo_documento_id;

      $legal_name = $cliente->tercero->descripcion;

      $party_type = 'PERSONA_JURIDICA';
      $tax_level_code = $cliente->tercero;
      if (gettype($tax_level_code) != 'string') {
         $tax_level_code = 'O-47';
      }
      $tax_scheme_id = '01';
      if ($cliente->tercero->numero_identificacion == '222222222222' || $cliente->tercero->numero_identificacion == '222222222') {
         $tax_scheme_id = 'ZZ';
      }

      if ($cliente->tercero->tipo == 'Persona natural') {
         $party_type = 'PERSONA_NATURAL';
         $tax_level_code = 'SIMPLIFICADO';
      }

      $address_line = $cliente->tercero->ciudad->descripcion;
      if ($cliente->tercero->direccion1 != '') {
         $address_line = $cliente->tercero->direccion1;
      }
      $postal_zone = $cliente->tercero->codigo_postal;
      if (strlen($postal_zone) <= 1) {
         $postal_zone = '200001';
      }
      $origin = 'residente';

      $department_id = substr($cliente->tercero->ciudad->id, 3, 2);
      $city_id = substr($cliente->tercero->ciudad->id, 5, strlen($cliente->tercero->ciudad->id) - 1);

      return '{"email": "' . $cliente->tercero->email . '","origin":"' . $origin . '","phone": "' . (int)$cliente->tercero->telefono1 . '","type": "' . $party_type . '","legal_name": "' . $legal_name . '","identification_type": "' . $party_identification_type . '","identification_number": "' . $cliente->tercero->numero_identificacion . '","tax_level_code": "' . $tax_level_code . '","tax_scheme_id": "' . $tax_scheme_id . '","department": "' . $department_id . '","city": "' . $city_id . '","postal_zone":"' . $postal_zone . '","address_line": "' . $address_line . '"}';
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
         $unidad_medida = $linea->item->unidad_medida1;

         $string_items .= '{"sku": "' . $linea->item->id . '","u.m": "' . $unidad_medida . '","description": "' . $linea->item->descripcion . '","quantity": ' . abs(number_format($linea->cantidad, $this->cantidadDecimales, '.', '')) . ',"price": ' . abs(number_format($linea->base_impuesto / $linea->cantidad, $this->cantidadDecimales, '.', ''));

         if ($linea->tasa_descuento != 0) {
            $string_items .= ',"discount_rate": ' . $linea->tasa_descuento;
         }

         $tax_category = config('ventas.etiqueta_impuesto_principal');
         if ($linea->item->impuesto->tax_category != null && $linea->item->impuesto->tax_category != '') {
            $tax_category = $linea->item->impuesto->tax_category;
         }


         $string_items .= ',"taxes": [  {    "tax_rate": ' . $linea->tasa_impuesto . ',"tax_category": "' . $tax_category . '"}]}';



         $es_primera_linea = false;
      }

      $string_items .= ']';

      return $string_items;
   }

   public function get_document_retentions()
   {
      $registro_retencion = (new ContabilidadService())->get_retenciones($this->doc_encabezado)->first();

      if ($registro_retencion == null) {
         return '[]';
      }

      $categoria_retencion = $registro_retencion->retencion->categoria_retencion;

      return '[{"tax_category": "' . $categoria_retencion->nombre_corto . '","tax_rate": ' . $registro_retencion->tasa_retencion . '}]';
   }

   public function consultar_documento()
   {
      if ($this->env == 'PRODUCCION') {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      } else {
         $resolucion = (object)['prefijo' => 'SETT', 'numero_resolucion' => 18760000001];
      }

      try {
         $client = new Client(['base_uri' => $this->url_emision]);

         $response = $client->get($this->url_emision . '?prefix=' . $resolucion->prefijo . '&number=' . $this->doc_encabezado->consecutivo, [
            // un array con la data de los headers como tipo de peticion, etc.
            'headers' => [
               'content-type' => 'application/json',
               'auth-token' => config('facturacion_electronica.tokenPassword')
            ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
         $response = $e->getResponse();
      }

      $json = json_decode((string) $response->getBody());

      if (!isset($json->support_doc)) {
         return null;
      }

      return $json->support_doc;
   }
}
