<?php

namespace App\NominaElectronica\DATAICO\Services;

use App\Sistema\Services\AppDocType;

use App\Sistema\TipoTransaccion;

use App\Nomina\NomContrato;
use App\Nomina\ValueObjects\LapsoNomina;
use Illuminate\Support\Facades\Auth;

// declaramos factura
class DocumentoSoporteService
{
   const CORE_TIPO_TRANSACCION_ID = 59; // Documentos soporte Nómina Electrónica

   public $array_head_data, $array_accruals, $array_deductions, $array_employee;

   public function get_data_for_json( NomContrato $empleado, $lapso, $almacenar_registros )
   {
      return array_merge(
         ['empleado' => $empleado],
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
         'env' => config('nomina.nom_elec_ambiente'),
         'prefix' => $core_tipo_doc_app->prefijo,
         'number' => $consecutivo,
         'salary' =>  $empleado->sueldo,
         'periodicity' => 'MENSUAL',
         'initial-settlement-date' => $lapso->fecha_inicial,
         'final-settlement-date' => $lapso->fecha_final,
         'issue-date' => $lapso->fecha_final,
         'payment-date' => $lapso->fecha_final,
         'notes' => [ 
               'text' => ''
            ]
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

      $one_line['status'] = 'success';
      $one_line['code'] = 0;
      $one_line['amount'] = 0;
      $one_line['amount'] = 0;
      
      if ($concepto->cpto_dian == null) {
         $one_line['status'] = 'error';
         $one_line['message'] = 'Concepto NO está relacionado a un Concepto DIAN.';
         return $one_line;
      }
      
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

      return $one_line;
   }

   public function get_arr_employee_data($empleado, $lapso)
   {
      $fecha_ingreso = explode('-',$empleado->fecha_ingreso);
      $data['code'] = $empleado->tercero->numero_identificacion;
      $data['payment-means'] = 'EFECTIVO';
      $data['worker-type'] = $empleado->tipo_cotizante;//'FUNCIONARIOS_PUBLICOS_SIN_TOPE_MAXIMO_DE_IBC';
      $data['sub-code'] = 'NO_APLICA';
      $data['start-date'] = $fecha_ingreso[2].'/'.$fecha_ingreso[1].'/'.$fecha_ingreso[0];
      $data['fire-date'] = '';
      $data['high-risk'] = false;
      $data['integral-salary'] = ($empleado->salario_integral)?true:false;
      $data['contract-type'] = 'TERMINO_FIJO';
      $data['identification-type'] = $empleado->tercero->tipo_doc_identidad->abreviatura;//'NUIP';
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

   public function get_json_to_send()
   {
      $data = $this->toArray();
      
      $lapso = new LapsoNomina( $this->fecha );

      $data += [ 
         'env' => config('nomina.nom_elec_ambiente'),
         'prefix' => $this->tipo_documento_app->prefijo,
         'number' => $this->consecutivo,
         'salary' =>  $this->empleado->sueldo,
         'periodicity' => 'MENSUAL',
         'initial-settlement-date' => $lapso->fecha_inicial,
         'final-settlement-date' => $lapso->fecha_final,
         'issue-date' => $lapso->fecha_final,
         'payment-date' => $lapso->fecha_final,
         'notes' => [ 
               'text' => ''
         ]
      ];

      return $data;
   
   }
}