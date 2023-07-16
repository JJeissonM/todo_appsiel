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

   public function procesar_envio_factura( $factura_doc_encabezado = null )
   {

      switch ( $this->tipo_transaccion )
      {
         case 'factura':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_factura();
            break;
         
         case 'nota_credito':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_nota_credito( $factura_doc_encabezado );
            break;
         
         case 'nota_debito':
            $json_doc_electronico_enviado = [];
            break;
         
         default:
            // code...
            break;
      }

      try {
         $client = new Client(['base_uri' => $this->url_emision]);

         $response = $client->post( $this->url_emision, [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => config('facturacion_electronica.tokenPassword')
                        ],
             // array de datos del formulario
             'json' => json_decode( $json_doc_electronico_enviado )
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }

      $array_respuesta = json_decode( (string) $response->getBody(), true );
      $array_respuesta['codigo'] = $response->getStatusCode();

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

      if ( $this->env == 'PRODUCCION' )
      {
         $resolucion = $this->doc_encabezado->resolucion_facturacion();
      }else{
         $resolucion = (object)['prefijo'=>'CONR','numero_resolucion'=>18763647];
      }

      $flexible = 'true';

      $notes = '---';
      if ($this->doc_encabezado->descripcion != null || $this->doc_encabezado->descripcion != '') {
	 $notes = str_replace('"', '\"', $this->doc_encabezado->descripcion);
      }

      return '"env": "' . $this->env . '","dataico_account_id": "' . config('facturacion_electronica.tokenEmpresa') . '","number":'.$this->doc_encabezado->consecutivo.',"issue_date": "' . date_format( date_create( $this->doc_encabezado->fecha ),'d/m/Y') . '","payment_date": "' . date_format( date_create( $this->doc_encabezado->fecha_vencimiento ),'d/m/Y') . '","invoice_type_code": "' . $this->invoice_type_code . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","numbering":{"resolution_number":"' . $resolucion->numero_resolucion . '","prefix":"' . $resolucion->prefijo . '","flexible":' . $flexible . '},"notes":["' . $notes . '"], "customer": ' . $this->get_datos_cliente();
   }

   public function get_encabezado_nota_credito( $factura_doc_encabezado )
   {
      $factura_dataico = new FacturaGeneral( $factura_doc_encabezado, 'factura' );

      $invoice_id = 0;
      if ($factura_dataico->consultar_documento() != null) {
         $invoice_id = $factura_dataico->consultar_documento()->uuid;
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

      if ( $cliente->tercero->tipo == 'Persona natural' )
      {
         $party_type = 'PERSONA_NATURAL';
         $tax_level_code = 'SIMPLIFICADO';
      }
      $regimen = 'ORDINARIO';

      // 16925001 = 169 pais, 25 departamento, 001 ciudad
      $department_id = substr($cliente->tercero->ciudad->id,3,2);
      $city_id = substr($cliente->tercero->ciudad->id, 5, strlen($cliente->tercero->ciudad->id)-1);
      
      return '{"email": "' . $cliente->tercero->email . '","phone": "' . $cliente->tercero->telefono1 . '","party_type": "' . $party_type . '","company_name": "' . $cliente->tercero->descripcion . '","first_name":"' . $cliente->tercero->nombre1 . '","family_name":"' . $cliente->tercero->apellido1 . '","party_identification": "' . $cliente->tercero->numero_identificacion . '","tax_level_code": "' . $tax_level_code . '","regimen": "' . $regimen . '","department": "' . $department_id . '","city": "' . $city_id . '","address_line": "' . $cliente->tercero->direccion1 . '"}';
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

         $string_items .= '{"sku": "' . $linea->item->id . '","description": "' . str_replace('"', '\"', $linea->item->descripcion) . '","quantity": ' . abs( number_format( $linea->cantidad, $this->cantidadDecimales, '.', '') ) . ',"price": ' . abs( number_format($linea->base_impuesto, $this->cantidadDecimales, '.', '') );

         if ( $linea->tasa_descuento != 0 )
         {
            $string_items .= ',"discount_rate": ' . $linea->tasa_descuento;
         } 

         $string_items .= ',"taxes": [  {    "tax_rate": ' . $linea->tasa_impuesto . ',"tax_category": "' . config('ventas.etiqueta_impuesto_principal') . '"}]}';
         $es_primera_linea = false;
      }

      $string_items .= ']';

      return $string_items;
   }

   // Representacion Grafica (PDF)
   public function consultar_documento()
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
         $prefijo_resolucion = 'APSI';
         $consecutivo_doc_encabezado = 185;
      */

      $tokenPassword = config('facturacion_electronica.tokenPassword');
      $prefijo_resolucion = $resolucion->prefijo;
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

      $json = json_decode( (string) $response->getBody() );
      if(!isset($json->invoice))
      {
         return null;
      }

      return $json->invoice;
   }
}
