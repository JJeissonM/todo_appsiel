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

   public $array_head_data, $array_accruals, $array_deductions, $array_employee;

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
      //dd('hi');
      $this->set_data_json( $empleado, $lapso, $almacenar_registros );
      dd(  $this->array_head_data );
      return json_decode( $this->array_head_data );
   }

   public function set_data_json( $empleado, $lapso, $almacenar_registros )
   {
      $this->set_head_data( $empleado, $lapso, $almacenar_registros );
      dd( $this->array_head_data );
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

      $this->array_head_data = [ 
         'env' => 'PRUEBAS',
         'prefix' => $core_tipo_doc_app->prefijo,
         'number' => $consecutivo,
         'salary' =>  $empleado->sueldo,
         'periodicity' => 'MENSUAL',
         'initial-settlement-date' => $lapso->fecha_inicial,
         'final-settlement-date' => $lapso->fecha_final,
         'issue-date' => date('Y-m-d'),
         'payment-date' => date('Y-m-d'),
         'notes' => [ 'text' => '']
      ];

   }

   public function set_accruals_data( $empleado, $lapso )
   {
      $registros = $empleado->get_registros_documentos_nomina_entre_fechas($lapso->fecha_inicial, $lapso->fecha_final);
      dd( $lapso->fecha_inicial, $lapso->fecha_final,$registros);
      $concepto->cpto_dian->codigo;
      
      /*

      "accruals": [ { "code => BASICO", "amount": 1500000, "days": 5 }, { "code => VIATICO", "amount": 100000, "amount-ns": 200000 } ]
      
      */

      $send_dian = 'true';
      $send_email = config('facturacion_electronica.enviar_email_clientes');

      $lista_emails = $this->doc_encabezado->cliente->tercero->email;
      if ( config('facturacion_electronica.email_copia_factura') != '' )
      {
         $lista_emails .= ';' . config('facturacion_electronica.email_copia_factura');
      }

      $this->array_accruals = [];
      /*'actions': {"send_dian" =>  $send_dian,
         'send_email" =>  $send_email,
         'email => ' . $lista_emails"},
      'credit_note": {' . $this->get_encabezado_nota_credito( $factura_doc_encabezado ),
         'items" =>  $this->get_lineas_registros(),
         'charges": []}}';*/
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
   }

}