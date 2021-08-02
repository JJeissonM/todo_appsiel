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

   function __construct( $doc_encabezado, $tipo_transaccion )
   {
      $this->doc_encabezado = $doc_encabezado;
      switch ( $tipo_transaccion )
      {
         case 'factura':
            $this->url_emision = config('facturacion_electronica.WSDL');
            $this->invoice_type_code = 'FACTURA_VENTA';
            break;
         
         case 'nota_credito':
            $this->url_emision = config('facturacion_electronica.url_notas_credito');
            $this->invoice_type_code = 'FACTURA_VENTA'; // **************    PENDIENTE
            break;
         
         case 'nota_debito':
            $this->url_emision = config('facturacion_electronica.url_notas_debito');
            $this->invoice_type_code = 'FACTURA_VENTA'; //  **************   PENDIENTE
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
      $json_doc_electronico_enviado = $this->preparar_cadena_json();
      /*
dd($json_doc_electronico_enviado);
      
      $consecutivo = 105;
      $json_doc_electronico_enviado = '{"actions": {"send_dian": true,"send_email": false,"email": "ing.adalbertoperez@gmail.com"},"invoice": {"env": "PRUEBAS","dataico_account_id": "a2532b03-a8bf-4514-a4e8-5fd7ec0499e9","number":'.$consecutivo.',"issue_date": "2021-07-31","payment_date": "2021-07-31","invoice_type_code": "FACTURA_VENTA","payment_means_type": "CREDITO","payment_means": "MUTUAL_AGREEMENT","numbering":{"resolution_number":"18760000001","prefix":"SETT","flexible":true}, "customer": {"email": "ing.adalbertoperez@gmail.com","phone": "06 50 27 98 72","party_type": "PERSONA_NATURAL","company_name": "HODGES DILLON HADASSAH","first_name":"HADASSAH","family_name":"HODGES","party_identification": "163506063","tax_level_code": "SIMPLIFICADO","regimen": "ORDINARIO","department": "CESAR","city": "VALLEDUPAR","address_line": "Apartado núm.: 297, 6199 Ullamcorper Ctra."},"items": [{"sku": "6","description": "ARROZ DIANA 3 KL","quantity": 2,"price": 7500,"taxes": [  {    "tax_rate": 0,"tax_category": "IVA"}]},{"sku": "8","description": "LANGOSTINO","quantity": 1.5,"price": 46500,"discount_rate": 7,"taxes": [  {    "tax_rate": 0,"tax_category": "IVA"}]},{"sku": "43","description": "LANGOSTA GRANDE","quantity": 3.23,"price": 40840.3361,"discount_rate": 10,"taxes": [  {    "tax_rate": 19,"tax_category": "IVA"}]}],"charges": []}}';
*/
      //dd(json_decode( $json_doc_electronico_enviado )->invoice);
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

      //dd( $array_respuesta, $response->getStatusCode() );

      switch ( $response->getStatusCode() ) {
         case '201':
            /*La solicitud se ha cumplido y ha dado lugar a la creación de un nuevo recurso, la factura fue creada satisfactoriamente.*/
            
            // Almacenar resultado en base de datos para Auditoria
            

            break;
         
         case '401':
            /*Indica que la petición (request) no ha sido ejecutada porque carece de credenciales válidas de autenticación para el recurso solicitado*/
            break;
         
         case '404':
            /*Se trata de un enlace roto, defectuoso o que ya no existe y que, por lo tanto, no es posible navegar por él, normalmente algo errado en la URL*/
            break;
         
         case '500':
            /*Código de estado HTTP, que significa que algo ha ido mal en el servidor del sitio web*/
            break;
         
         default:
            // code...
            break;
      }

      $obj_resultado = new ResultadoEnvio;
      $mensaje = $obj_resultado->almacenar_resultado( $array_respuesta, json_decode( $json_doc_electronico_enviado ), $this->doc_encabezado->id );

      return $mensaje;
   }

   public function preparar_cadena_json()
   {
      $send_dian = 'true';
      $send_email = 'false';
      return '{"actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": "' . $this->doc_encabezado->cliente->tercero->email . '"},"invoice": {' . $this->get_encabezado_documento() . ',"items": ' . $this->get_lineas_registros() . ',"charges": []}}';
   }

   public function get_encabezado_documento()
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
         $resolucion = (object)['prefijo'=>'SETT','numero_resolucion'=>18760000001];
      }

      $flexible = 'true';

      //$this->doc_encabezado->consecutivo = 101;

      return '"env": "' . $this->env . '","dataico_account_id": "' . config('facturacion_electronica.tokenEmpresa') . '","number":'.$this->doc_encabezado->consecutivo.',"issue_date": "' . $this->doc_encabezado->fecha . '","payment_date": "' . $this->doc_encabezado->fecha_vencimiento . '","invoice_type_code": "' . $this->invoice_type_code . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","numbering":{"resolution_number":"' . $resolucion->numero_resolucion . '","prefix":"' . $resolucion->prefijo . '","flexible":' . $flexible . '}, "customer": ' . $this->get_datos_cliente();
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

      return '{"email": "' . $cliente->tercero->email . '","phone": "' . $cliente->tercero->telefono1 . '","party_type": "' . $party_type . '","company_name": "' . $cliente->tercero->descripcion . '","first_name":"' . $cliente->tercero->nombre1 . '","family_name":"' . $cliente->tercero->apellido1 . '","party_identification": "' . $cliente->tercero->numero_identificacion . '","tax_level_code": "' . $tax_level_code . '","regimen": "' . $regimen . '","department": "' . strtoupper( $cliente->tercero->ciudad->departamento->descripcion ) . '","city": "' . strtoupper( $cliente->tercero->ciudad->descripcion ) . '","address_line": "' . $cliente->tercero->direccion1 . '"}';
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

         $string_items .= '{"sku": "' . $linea->item->id . '","description": "' . $linea->item->descripcion . '","quantity": ' . abs( number_format( $linea->cantidad, $this->cantidadDecimales, '.', '') ) . ',"price": ' . abs( number_format($linea->base_impuesto, $this->cantidadDecimales, '.', '') );

         if ( $linea->tasa_descuento != 0 )
         {
            $string_items .= ',"discount_rate": ' . $linea->tasa_descuento;
         } 

         $string_items .= ',"taxes": [  {    "tax_rate": ' . $linea->tasa_impuesto . ',"tax_category": "IVA"}]}';
         $es_primera_linea = false;
      }

      $string_items .= ']';

      return $string_items;
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

         $response = $client->get( $this->url_emision . '?number=' .$resolucion->prefijo . $this->doc_encabezado->consecutivo, [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => config('facturacion_electronica.tokenPassword')
                        ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }  /**/      

      $json = json_decode( (string) $response->getBody() );

      return $json->invoice->pdf_url;
      //dd( $json->invoice->pdf_url );
   }
}