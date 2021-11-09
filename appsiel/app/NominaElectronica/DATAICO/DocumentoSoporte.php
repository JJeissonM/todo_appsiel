<?php

namespace App\NominaElectronica\DATAICO;

use Illuminate\Database\Eloquent\Model;

use GuzzleHttp\Client;

use App\Sistema\Services\AppDocType;

use Auth;
use DB;
use App\Sistema\TipoTransaccion;

use App\NominaElectronica\DATAICO\ResultadoEnvio;

use App\Nomina\NomContrato;

// declaramos factura
class DocumentoSoporte extends Model
{
   const CORE_TIPO_TRANSACCION_ID = 59; // Documentos soporte Nómina Electrónica

   public $head_data_string, $accruals_string, $deductions_string, $employee_string;

   protected $table = 'nom_elect_doc_soporte';

   protected $fillable = [ 'core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'nom_contrato_id', 'descripcion', 'head_data_json', 'accruals_json', 'deductions_json', 'employee_json', 'estado', 'creado_por', 'modificado_por' ];

   public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Empleado', 'Estado'];

   public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

   public static function consultar_registros($nro_registros, $search)
   {
      return DocumentoSoporte::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'nom_elect_doc_soporte.core_tipo_doc_app_id')
            ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_elect_doc_soporte.nom_contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->select(
                'nom_elect_doc_soporte.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",nom_elect_doc_soporte.consecutivo) AS campo2'),
                'core_terceros.descripcion AS campo3',
                'nom_elect_doc_soporte.estado AS campo4',
                'nom_elect_doc_soporte.id AS campo5'
            )
            ->orderBy('nom_elect_doc_soporte.created_at', 'DESC')
            ->paginate($nro_registros);
   }

   public function get_json( NomContrato $empleado, $lapso, $almacenar_registros )
   {
      dd('hi');
      $this->set_data_json( $empleado, $lapso, $almacenar_registros );
      //dd(  '{' . $this->head_data_string . '}'  );
      return json_decode( '{' . $this->head_data_string . '}' );
   }

   public function set_data_json( $empleado, $lapso, $almacenar_registros )
   {
      $this->set_head_data( $empleado, $lapso, $almacenar_registros );
      $this->set_accruals_data( $empleado, $lapso );
   }

   public function set_head_data( $empleado, $lapso, $almacenar_registros )
   {      
      $transaccion = TipoTransaccion::find( self::CORE_TIPO_TRANSACCION_ID );

      $core_tipo_doc_app = $transaccion->tipos_documentos->first();
      $core_empresa_id = Auth::user()->empresa_id;

      $app_doc_type = new AppDocType();
      $consecutivo = $app_doc_type->get_consecutivo_actual( $core_empresa_id, $core_tipo_doc_app->id ) + 1;

      if( $almacenar_registros )
      {
         $app_doc_type->aumentar_consecutivo( $core_empresa_id, $core_tipo_doc_app->id );
      }

      $this->head_data_string = '"env": "PRUEBAS","prefix": "' . $core_tipo_doc_app->prefijo . '","number": ' .$consecutivo . ',"salary": ' . $empleado->sueldo . ',"periodicity" : "MENSUAL","initial-settlement-date": "' . $lapso->fecha_inicial . '","final-settlement-date": "' . $lapso->fecha_final . '","issue-date": "' . date('Y-m-d') . '","payment-date": "' . date('Y-m-d') . '","notes": [{"text": ""}]';

   }

   public function set_accruals_data( $empleado, $lapso )
   {
      $registros = $empleado->get_registros_documentos_nomina_entre_fechas($lapso->fecha_inicial, $lapso->fecha_final);
      dd('set_accruals_data',$lapso->fecha_inicial, $lapso->fecha_final,$registros);
      $concepto->cpto_dian->codigo;
      
      /*

      "accruals": [ { "code": "BASICO", "amount": 1500000, "days": 5 }, { "code": "VIATICO", "amount": 100000, "amount-ns": 200000 } ]
      
      */

      $send_dian = 'true';
      $send_email = config('facturacion_electronica.enviar_email_clientes');

      $lista_emails = $this->doc_encabezado->cliente->tercero->email;
      if ( config('facturacion_electronica.email_copia_factura') != '' )
      {
         $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
      }

      $this->accruals_string = '{"actions": {"send_dian": ' . $send_dian . ',"send_email": ' . $send_email . ',"email": "' . $lista_emails . '"},"credit_note": {' . $this->get_encabezado_nota_credito( $factura_doc_encabezado ) . ',"items": ' . $this->get_lineas_registros() . ',"charges": []}}';
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

      return '"env": "' . $this->env . '","dataico_account_id": "' . config('facturacion_electronica.tokenEmpresa') . '","number":'.$this->doc_encabezado->consecutivo.',"issue_date": "' . date_format( date_create( $this->doc_encabezado->fecha ),'d/m/Y') . '","payment_date": "' . date_format( date_create( $this->doc_encabezado->fecha_vencimiento ),'d/m/Y') . '","invoice_type_code": "' . $this->invoice_type_code . '","payment_means_type": "' . $payment_means_type . '","payment_means": "' . $payment_means . '","numbering":{"resolution_number":"' . $resolucion->numero_resolucion . '","prefix":"' . $resolucion->prefijo . '","flexible":' . $flexible . '}, "customer": ' . $this->get_datos_cliente();
   }

   public function get_encabezado_nota_credito( $factura_doc_encabezado )
   {

      $factura_dataico = new FacturaGeneral( $factura_doc_encabezado, 'factura' );
      $invoice_id = $factura_dataico->consultar_documento()->uuid;

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

      return $json->invoice;
   }
}