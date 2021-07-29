<?php

namespace App\FacturacionElectronica\DATAICO;

use GuzzleHttp\Client;

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
      $this->env = 'PRUEBAS'; //'PRODUCCION'
   }

   public function procesar_envio_factura()
   {
      $json = $this->preparar_cadena_json();

      //dd( $json );

      $client = new Client(['base_uri' => $this->url_emision]);

      $response = $client->post( $this->url_emision, [
          // un array con la data de los headers como tipo de peticion, etc.
          'headers' => [
                        'content-type' => 'application/json',
                        'auth-token' => config('facturacion_electronica.tokenPassword')
                     ],
          // array de datos del formulario
          'json' => json_decode( $json )
      ]);

      dd( $response );
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

      $this->doc_encabezado->consecutivo = 101;

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

         $string_items .= '{"sku": "' . $linea->item->id . '","description": "' . $linea->item->descripcion . '","quantity": ' . $linea->cantidad . ',"price": ' . $linea->base_impuesto . ',"taxes": [  {    "tax_rate": ' . $linea->tasa_impuesto . ',"tax_category": "IVA"}]}';
         $es_primera_linea = false;
      }

      $string_items .= ']';

      return $string_items;
   }
}