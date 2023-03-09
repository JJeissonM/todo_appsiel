<?php

namespace App\NominaElectronica\DATAICO;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Services\AppDocType;

use App\Sistema\TipoTransaccion;

use App\Nomina\NomContrato;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

   public function get_data_for_json( NomContrato $empleado, $lapso, $almacenar_registros )
   {
      return array_merge(
         $this->get_arr_head_data( $empleado, $lapso, $almacenar_registros ),
         $this->get_arr_content_data( $empleado, $lapso ),
         $this->get_arr_employee_data($empleado, $lapso)
      );
   }

   public function get_arr_head_data( $empleado, $lapso, $almacenar_registros )
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

      return [ 
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

   public function get_arr_content_data( $empleado, $lapso )
   {
      $registros = $empleado->get_registros_documentos_nomina_entre_fechas($lapso->fecha_inicial, $lapso->fecha_final);

      $registros_por_conceptos = $registros->groupBy('nom_concepto_id');
      
      $line_accruals = [];
      $line_deductions = [];
      foreach ($registros_por_conceptos as $concepto_id => $registro_concepto) {         
         
         $concepto = $registro_concepto->all()[0]->concepto;

         if ($concepto->naturaleza == 'devengo') {
            $line_accruals[] = $this->get_linea_empleado($registro_concepto,$concepto,$registro_concepto->sum('valor_devengo'));
         }else{
            $line_deductions[] = $this->get_linea_empleado($registro_concepto,$concepto,$registro_concepto->sum('valor_deduccion'));
         }
      }

      return [
         'accruals' => $line_accruals,
         'deductions' => $line_deductions
      ];
   }

   public function get_linea_empleado($registro_concepto,$concepto,$amount)
   {         
      $one_line = [];
      
      if ($concepto->cpto_dian == null) {
         $one_line['status'] = 'error';
         $one_line['message'] = 'Concepto NO está relacionado a un Concepto DIAN.';
         return $one_line;
      }
      
      $one_line['status'] = 'success';
      $one_line['code'] = $concepto->cpto_dian->codigo;
      
      if ($concepto->cpto_dian->liquida_dias) {
         $one_line['days'] = round( $registro_concepto->sum('cantidad_horas') / (int)config('nomina.horas_dia_laboral') , 0 );
      }
      
      if ($concepto->cpto_dian->liquida_horas) {
         $one_line['hours'] = $registro_concepto->sum('cantidad_horas');
      }
      
      if ($concepto->cpto_dian->porcentaje_del_basico != 0) {
         $one_line['percentage'] = $concepto->cpto_dian->porcentaje_del_basico;
      }         
      
      $one_line['amount'] = $amount;
      //$registro_concepto->sum('valor_devengo') + $registro_concepto->sum('valor_deduccion');

      return $one_line;
   }

   public function get_arr_employee_data($empleado, $lapso)
   {
      $data['code'] = $empleado->tercero->numero_identificacion;
      $data['payment-means'] = 'EFECTIVO';
      $data['worker-type'] = 'FUNCIONARIOS_PUBLICOS_SIN_TOPE_MAXIMO_DE_IBC';
      $data['sub-code'] = 'NO_APLICA';
      $data['start-date'] = '01/01/2001';
      $data['fire-date'] = '';
      $data['high-risk'] = false;
      $data['integral-salary'] = false;
      $data['contract-type'] = 'TERMINO_FIJO';
      $data['identification-type'] = 'NUIP';
      $data['identification'] = $empleado->tercero->numero_identificacion;
      $data['first-name'] = $empleado->tercero->nombre1;
      $data['other-names'] = $empleado->tercero->otros_nombres;
      $data['last-name'] = $empleado->tercero->apellido1;
      $data['second-last-name'] = $empleado->tercero->apellido2;
      $data['bank'] = '';
      $data['account-type-kw'] = '';
      $data['account-number'] = '';

      
      // 16925001 = 169 pais, 25 departamento, 001 ciudad
      $department_id = substr($empleado->tercero->ciudad->id,3,2);
      $city_id = substr($empleado->tercero->ciudad->id, 5, strlen($empleado->tercero->ciudad->id)-1);

      $data['address'] = [
         'city' => $city_id,
         'line' => $empleado->tercero->direccion1,
         'department' => $department_id
      ];

      return [
         'employee' => $data
      ];
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