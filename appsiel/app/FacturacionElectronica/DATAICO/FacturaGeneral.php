<?php

namespace App\FacturacionElectronica\DATAICO;

use GuzzleHttp\Client;

use App\FacturacionElectronica\DATAICO\ResultadoEnvio;

// declaramos factura
class FacturaGeneral
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
         case 'factura':
            $this->url_emision = config('facturacion_electronica.WSDL');
            $this->invoice_type_code = 'FACTURA_VENTA';
            break;
         
         case 'nota_credito':
            $this->url_emision = config('facturacion_electronica.url_notas_credito');
            break;
         
         case 'nota_debito':
            $this->url_emision = config('facturacion_electronica.url_notas_debito');
            break;
         
         default:
            // code...
            break;
      }
         
      $this->cantidadDecimales = config('facturacion_electronica.cantidadDecimales');
      $this->env = config('facturacion_electronica.fe_ambiente'); //'PRUEBAS' || 'PRODUCCION'
   }

   public function procesar_envio_factura( $factura_doc_encabezado )
   {

      switch ( $this->tipo_transaccion )
      {
         case 'factura':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_factura( $factura_doc_encabezado );
            break;
         
         case 'nota_credito':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_nota_credito( $factura_doc_encabezado );
            break;
         
         case 'nota_debito':
            $json_doc_electronico_enviado = [];
            break;
         
         default:
            break;
      }
      
      $tokenPassword = config('facturacion_electronica.tokenPassword');
      
      return $this->enviar_documento_electronico( $tokenPassword, $json_doc_electronico_enviado, $factura_doc_encabezado->get_label_documento());      
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

      $obj_resultado = new ResultadoEnvio;
      $mensaje = $obj_resultado->almacenar_resultado( $array_respuesta, json_decode( $json_doc_electronico_enviado ), $this->doc_encabezado->id );

      return $mensaje;
   }

   public function preparar_cadena_json_factura()
   {
      $send_dian = 'true';
      $send_email = config('facturacion_electronica.enviar_email_clientes');

      $lista_emails = $this->doc_encabezado->cliente->tercero->email;
      if ( config('facturacion_electronica.email_copia_factura') != '' )
      {
         $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
      }

      return '{"actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": "' . $lista_emails . '"},"invoice": {' . $this->get_encabezado_factura() . ',"items": ' . $this->get_lineas_registros() . ',"charges": []}}';
   }

   public function preparar_cadena_json_nota_credito( $factura_doc_encabezado )
   {
      $send_dian = 'true';
      $send_email = config('facturacion_electronica.enviar_email_clientes');

      $lista_emails = $this->doc_encabezado->cliente->tercero->email;
      if ( config('facturacion_electronica.email_copia_factura') != '' )
      {
         $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
      }

      return '{"actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": "' . $lista_emails . '"},"credit_note": {' . $this->get_encabezado_nota_credito( $factura_doc_encabezado ) . ',"items": ' . $this->get_lineas_registros() . ',"charges": []}}';
   }

   public function get_encabezado_factura()
   {
      $payment_means_type = 'DEBITO'; // Contado
      if ( $this->doc_encabezado->forma_pago == 'credito' )
      {
         $payment_means_type = 'CREDITO';
      }

      $payment_means = 'MUTUAL_AGREEMENT'; //  Medio de pago

      $resolucion = (object)['prefijo'=>'FEE','numero_resolucion'=>18760000001];
      if ( $this->env == 'PRODUCCION' )
      {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      }

      if ( $resolucion == null )
      {
         $resolucion = (object)['prefijo'=> $this->doc_encabezado->tipo_documento_app->prefijo,'numero_resolucion'=>18760000001];
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
            }else{
               $notes2 .= ' ' . $value;
            }            
         }
      }
      
      return '"env": "' . $this->env . '","dataico_account_id": "' . config('facturacion_electronica.tokenEmpresa') . '","number":'.$this->doc_encabezado->consecutivo.',"issue_date": "' . date_format( date_create( $this->doc_encabezado->fecha ),'d/m/Y') . '","payment_date": "' . date_format( date_create( $this->doc_encabezado->fecha_vencimiento ),'d/m/Y') . '","invoice_type_code": "' . $this->invoice_type_code . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","numbering":{"resolution_number":"' . $resolucion->numero_resolucion . '","prefix":"' . $resolucion->prefijo . '","flexible":' . $flexible . '},"notes":["' . $notes2 . '"], "customer": ' . $this->get_datos_cliente();
   }

   public function get_encabezado_nota_credito( $factura_doc_encabezado )
   {
      $factura_dataico = new FacturaGeneral( $factura_doc_encabezado, 'factura' );

      $invoice_id = 0;
      $json_dataico = $factura_dataico->get_einvoice_in_dataico();
      if ( isset($json_dataico->invoice) ) {
         $invoice_id = $json_dataico->invoice->uuid;
      }      

      $payment_means_type = 'CREDITO';

      $payment_means = 'CREDITO'; //  Medio de pago

      if ( $this->env == 'PRODUCCION' )
      {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      }else{
         $resolucion = (object)['prefijo'=>'SETT','numero_resolucion'=>18760000001];
      }

      $flexible = 'true';
      $reason = 'DEVOLUCION'; /**********  List [ "DEVOLUCION", "ANULACION", "REBAJA", "DESCUENTO", "RECISION", "OTROS" ]    PENDIENTE    ******/
      $issue_date = date_format( date_create( $this->doc_encabezado->fecha ),'d/m/Y');
      $fecha_vencimiento = date_create( $this->doc_encabezado->fecha_vencimiento );
      $payment_date = date_format( date_add( $fecha_vencimiento, date_interval_create_from_date_string("1 month")),'d/m/Y');

      return '"env": "' . $this->env . '","dataico_account_id": "' . config('facturacion_electronica.tokenEmpresa') . '","issue_date": "' . $issue_date . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","payment_date": "' . $payment_date . '","reason": "' . $reason . '","invoice_id": "' . $invoice_id . '","number":'.$this->doc_encabezado->consecutivo.',"numbering":{"prefix":"' . $this->doc_encabezado->tipo_documento_app->prefijo . '","flexible":' . $flexible . '}';
   }

   public function get_datos_cliente()
   {
      $cliente = $this->doc_encabezado->cliente;

      $party_type = 'PERSONA_JURIDICA';
      $tax_level_code = 'COMUN';
      $company_name = $cliente->tercero->descripcion;
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

      // 16925001 = 169 pais, 25 departamento, 001 ciudad
      $department_id = substr($cliente->tercero->ciudad->id,3,2);
      $city_id = substr($cliente->tercero->ciudad->id, 5, strlen($cliente->tercero->ciudad->id)-1);

      $address_line = $cliente->tercero->ciudad->descripcion;
      if ( $cliente->tercero->direccion1 != '') {
         $address_line = $cliente->tercero->direccion1;
      }
      
      return '{"email": "' . $cliente->tercero->email . '","phone": "' . $cliente->tercero->telefono1 . '","party_type": "' . $party_type . '","company_name": "' . $company_name . '","first_name":"' . $first_name . '","family_name":"' . $family_name . '","party_identification": "' . $cliente->tercero->numero_identificacion . '","tax_level_code": "' . $tax_level_code . '","regimen": "' . $regimen . '","department": "' . $department_id . '","city": "' . $city_id . '","address_line": "' . $address_line . '"}';
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

         $string_items .= '{"sku": "' . $linea->item->id . '","description": "' . str_replace('"', '\"', $linea->item->descripcion) . '","quantity": ' . abs( number_format( $linea->cantidad, $this->cantidadDecimales, '.', '') ) . ',"price": ' . abs( number_format( $price, $this->cantidadDecimales, '.', '') );
         
         if ( $original_price != 0 ) {
            $string_items .= ',"original_price": ' . $original_price;
         }

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
   
   public function get_einvoice_in_dataico()
   {
      $prefijo_resolucion = $this->doc_encabezado->tipo_documento_app->prefijo;

      if ( $this->tipo_transaccion == 'factura') {
         
         $resolucion = (object)['prefijo'=>'SETT','numero_resolucion'=>18760000001];
         if ( $this->env == 'PRODUCCION' )
         {
            $resolucion = $this->doc_encabezado->resolucion_facturacion();
         }
      
         if ( $resolucion == null )
         {
            $prefijo_resolucion = $this->doc_encabezado->tipo_documento_app->prefijo;
         }
      }
        
      /*
               DATOS APPSIEL SAS - Para Pruebas      
         Dataico Account Id: a2532b03-a8bf-4514-a4e8-5fd7ec0499e9
         Dataico Auth Token: 088c164ef2ff8964cca84f76e8059f18
         $tokenPassword = '088c164ef2ff8964cca84f76e8059f18';
         $prefijo_resolucion = 'APSI';
         $consecutivo_doc_encabezado = 185;
      */

      $tokenPassword = config('facturacion_electronica.tokenPassword');

      $consecutivo_doc_encabezado = $this->doc_encabezado->consecutivo;

      try {
         $client = new Client(['base_uri' => $this->url_emision]);

         $response = $client->get( $this->url_emision . '?number=' . $prefijo_resolucion . $consecutivo_doc_encabezado, [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => $tokenPassword
                        ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }

      if( $response->getStatusCode() == 401)
      {
         return json_decode(
                              json_encode([
                                 "errors" => "Codigo 401. Cuenta no autorizada."
                              ])
                           );
      }

      return json_decode( (string) $response->getBody() );
   }

   /**
    * 
    */
   public function get_errores( $json_dataico )
   {
      $errors_list = '';

      if (isset($json_dataico->errors)) {

         if (gettype($json_dataico->errors) == 'string') {
            $errors_list .= ' - ' . $json_dataico->errors;
         }else{
            foreach ($json_dataico->errors as $key => $object) {
               $errors_list .= ' - ' . $object->error;
            }
         }
     }

     if (isset($json_dataico->invoice )) {
         if ($json_dataico->invoice->dian_status == 'DIAN_RECHAZADO') {

               $errors_list = '';
               $dian_messages = [];
               if ( isset($json_dataico->invoice->dian_messages) ) {
                  $dian_messages = $json_dataico->invoice->dian_messages;
               }
               foreach ( $dian_messages as $key => $object) {
                  $errors_list .= ' - ' . $object;
               }
         }
      }

      return $errors_list;
   }
}
