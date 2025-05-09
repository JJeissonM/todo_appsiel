<?php

namespace App\FacturacionElectronica\DATAICO;

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

   function __construct( $doc_encabezado, $tipo_transaccion )
   {
      $this->doc_encabezado = $doc_encabezado;
      $this->tipo_transaccion = $tipo_transaccion;
      switch ( $tipo_transaccion )
      {
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
      switch ( $this->tipo_transaccion )
      {
         case 'support_doc':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_doc_soporte();
            break;
         
         default:
            // code...
            break;
      }

      $tokenPassword = config('facturacion_electronica.tokenPassword');

      return $this->enviar_documento_electronico( $tokenPassword, $json_doc_electronico_enviado, $this->doc_encabezado->get_label_documento() );
      
   }

   public function enviar_documento_electronico( $tokenPassword, $json_doc_electronico_enviado, $label_documento, $testing = false )
   {
      try {
         $client = new Client(['base_uri' => $this->url_emision]);

         $response = $client->post( $this->url_emision, [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => $tokenPassword
                        ],
             // array de datos del formulario
             'json' => json_decode( $json_doc_electronico_enviado )
         ]);
         
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }

      $array_respuesta = json_decode( (string) $response->getBody(), true );

      $array_respuesta['codigo'] = $response->getStatusCode();

      if ( !isset( $array_respuesta['number'] ) ) {
         $array_respuesta['number'] = $label_documento;
      }

      if ( $testing ) {
         dd( $array_respuesta );
      }      
      
      $obj_resultado = new ResultadoEnvioDocSoporte;
      $mensaje = $obj_resultado->almacenar_resultado( $array_respuesta, json_decode( $json_doc_electronico_enviado ), $this->doc_encabezado->id );

      return $mensaje;
   }

   public function preparar_cadena_json_doc_soporte()
   {
      return '{"support_doc": {' . $this->get_encabezado_factura() . '}}';
   }

   public function get_encabezado_factura()
   {
      $send_dian = 'true';
      $send_email = config('facturacion_electronica.enviar_email_clientes');
      
      $lista_emails = $this->doc_encabezado->proveedor->tercero->email;
      if ( config('facturacion_electronica.email_copia_factura') != '' )
      {
         $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
      }

      $payment_means_type = 'DEBITO'; // Contado
      if ( $this->doc_encabezado->forma_pago == 'credito' )
      {
         $payment_means_type = 'CREDITO';
      }

      $payment_means = 'MUTUAL_AGREEMENT'; //  Medio de pago

      if ( $this->env == 'PRODUCCION' )
      {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      }else{
         $resolucion = (object)['prefijo'=>'CONR','numero_resolucion'=>18763647];
      }

      $flexible = 'true';

      return '"send_dian": "' . $send_dian . '","send_email": ' . $send_email . ',"email": "' . $lista_emails . '","env": "' . $this->env . '","dataico_account_id": "' . config('facturacion_electronica.tokenEmpresa') . '","number":'.$this->doc_encabezado->consecutivo.',"issue_date": "' . date_format( date_create( $this->doc_encabezado->fecha ),'d/m/Y') . '","payment_date": "' . date_format( date_create( $this->doc_encabezado->fecha_vencimiento ),'d/m/Y') . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","numbering":{"resolution_number":"' . $resolucion->numero_resolucion . '","prefix":"' . $resolucion->prefijo . '","flexible":' . $flexible . '}, "customer": ' . $this->get_datos_cliente().',"items": ' . $this->get_lineas_registros() . ',"retentions": ' . $this->get_document_retentions();
      // . ',"charges": []'
   }

   public function get_einvoice_in_dataico()
   {
      if ( $this->env == 'PRODUCCION' )
      {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      }else{
         $resolucion = (object)['prefijo'=>'SETT','numero_resolucion'=>18760000001];
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
         $client = new Client(['base_uri' => $url_emision ]);

         $response = $client->get( $this->url_emision . '?prefix=' . $prefijo_resolucion . '&number=' . $consecutivo_doc_encabezado, [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => $tokenPassword
                        ]
         ]);

      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }

      //dd($response, json_decode( (string) $response->getBody() ));

      return json_decode( (string) $response->getBody() );
   }

   public function get_datos_cliente()
   {
      $cliente = $this->doc_encabezado->proveedor;

      $party_identification_type = $cliente->tercero->id_tipo_documento_id;

      $company_name = $cliente->tercero->descripcion;

      $party_type = 'PERSONA_JURIDICA';
      $tax_level_code = 'COMUN';
      $first_name = '';
      $family_name = '';

      if ( $cliente->tercero->tipo == 'Persona natural' )
      {
         $party_type = 'PERSONA_NATURAL';
         $tax_level_code = 'SIMPLIFICADO';

         $first_name = explode(" ", $cliente->tercero->descripcion)[0];
         $family_name = substr($cliente->tercero->descripcion, strlen($first_name) + 1);

         if ( $cliente->tercero->nombre1 != '' && $cliente->tercero->apellido1 != '') {
            $first_name = $cliente->tercero->nombre1;
            $family_name = $cliente->tercero->apellido1;
         }
      }

      $regimen = 'ORDINARIO';

      $address_line = $cliente->tercero->ciudad->descripcion;
      if ( $cliente->tercero->direccion1 != '') {
         $address_line = $cliente->tercero->direccion1;
      }

      return '{"email": "' . $cliente->tercero->email . '","phone": "' . (int)$cliente->tercero->telefono1 . '","party_type": "' . $party_type . '","company_name": "' . $company_name . '","first_name":"' . $first_name . '","family_name":"' . $family_name . '","party_identification_type": "' . $party_identification_type . '","party_identification": "' . $cliente->tercero->numero_identificacion . '","tax_level_code": "' . $tax_level_code . '","regimen": "' . $regimen . '","department": "' . strtoupper( $cliente->tercero->ciudad->departamento->descripcion ) . '","city": "' . strtoupper( $cliente->tercero->ciudad->descripcion ) . '","address_line": "' . $address_line . '"}';
   }

   public function get_lineas_registros()
   {
      $string_items = '[';
      
      $lineas_registros = $this->doc_encabezado->lineas_registros;
      $es_primera_linea = true;
      foreach ($lineas_registros as $linea)
      {

         if ( !$es_primera_linea )
         {
            $string_items .= ',';
         }

         $string_items .= '{"sku": "' . $linea->item->id . '","description": "' . $linea->item->descripcion . '","quantity": ' . abs( number_format( $linea->cantidad, $this->cantidadDecimales, '.', '') ) . ',"price": ' . abs( number_format($linea->base_impuesto / $linea->cantidad, $this->cantidadDecimales, '.', '') );

         if ( $linea->tasa_descuento != 0 )
         {
            $string_items .= ',"discount_rate": ' . $linea->tasa_descuento;
         }

         $tax_category = config('ventas.etiqueta_impuesto_principal');
         if ( $linea->item->impuesto->tax_category != null && $linea->item->impuesto->tax_category != '' ) {
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
      $registro_retencion = (new ContabilidadService())->get_retenciones( $this->doc_encabezado )->first();

      if( $registro_retencion == null )
      {
         return '[]';
      }

      $categoria_retencion = $registro_retencion->retencion->categoria_retencion;

      return '[{"tax_category": "' . $categoria_retencion->nombre_corto . '","tax_rate": ' . $registro_retencion->tasa_retencion . '}]';
   }

   public function consultar_documento()
   {
      if ( $this->env == 'PRODUCCION' )
      {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      }else{
         $resolucion = (object)['prefijo'=>'SETT','numero_resolucion'=>18760000001];
      }      
         
      try {
         $client = new Client(['base_uri' => $this->url_emision]);

         $response = $client->get( $this->url_emision . '?prefix=' . $resolucion->prefijo . '&number=' . $this->doc_encabezado->consecutivo, [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => config('facturacion_electronica.tokenPassword')
                        ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }

      $json = json_decode( (string) $response->getBody() );
      
      if(!isset($json->support_doc))
      {
         return null;
      }

      return $json->support_doc;
   }
}