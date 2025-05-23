<?php

namespace App\NominaElectronica\DATAICO\Services;

use App\Sistema\Services\AppDocType;

use App\Sistema\TipoTransaccion;

use App\Nomina\NomContrato;
use App\NominaElectronica\ResultadoEnvioDocumento;
use GuzzleHttp\Client;
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
         'send_dian' => true,
         'send_email' => true,
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

      $registros_agrupados_por_concepto = $registros->groupBy('nom_concepto_id');
      
      $line_accruals = [];
      $line_deductions = [];
      foreach ($registros_agrupados_por_concepto as $concepto_id => $registro_concepto) {         
         
         $concepto = $registro_concepto->all()[0]->concepto;

         if ($concepto->naturaleza == 'devengo') {

            $value_json = $this->get_linea_empleado($registro_concepto,$concepto,$registro_concepto->sum('valor_devengo'),$registros);
            if (!empty($value_json)) {
               $line_accruals[] = $value_json;
            }
            
         }else{
            $value_json = $this->get_linea_empleado($registro_concepto,$concepto,$registro_concepto->sum('valor_deduccion'),$registros);
            if (!empty($value_json)) {
               $line_deductions[] = $value_json;
            }
         }
      }

      return [
         'accruals' => $line_accruals,
         'deductions' => $line_deductions
      ];
   }

   public function get_linea_empleado($registro_concepto, $concepto, $amount, $registros)
   {
      $one_line = [];

      $one_line['status'] = 'success';
      $one_line['code'] = 0;
      $one_line['amount'] = 0;
      
      if ($concepto->cpto_dian == null) {
         $one_line['status'] = 'error';
         $one_line['message'] = 'Concepto (' . $concepto->descripcion . ') NO está relacionado a un Concepto DIAN.';
         return $one_line;
      }

      $codigo_cpto_dian = $concepto->cpto_dian->codigo;

      $skip = false;   
      if($concepto->modo_liquidacion_id ==  16) { // Intereses de cesantías. Se agrega como subconcepto de las Cesantías
         foreach ($registros as $registro) {
            if ($registro->concepto->modo_liquidacion_id == 17) { // Intereses de cesantías
               $skip = true;   
            }
         }
         if ($skip) {
            return [];
         }

         $one_line['percentage'] = 12;
         $one_line['cesantias-interest'] = $registro->valor_devengo;
         $amount = 0;
         $codigo_cpto_dian = 'CESANTIAS';
      }
      
      $one_line['code'] = $codigo_cpto_dian;
      
      if ($concepto->cpto_dian->liquida_dias) {
         $one_line['days'] = round( $registro_concepto->sum('cantidad_horas') / (float)config('nomina.horas_dia_laboral') , 0 );
      }
      
      if ($concepto->cpto_dian->liquida_horas) {
         $one_line['hours'] = $registro_concepto->sum('cantidad_horas');
      }
      
      if ($concepto->cpto_dian->porcentaje_del_basico != 0) {
         $one_line['percentage'] = $concepto->cpto_dian->porcentaje_del_basico;
      }

      if (in_array($concepto->cpto_dian->id, [33,52])) { // 33 = OTRO_CONCEPTO (devengo) , 52 = OTRA_DEDUCCION
         $one_line['description'] = $concepto->descripcion;
      }
      
      if($concepto->modo_liquidacion_id == 17) // Cesantias
      {
         foreach ($registros as $registro) {
            if ($registro->concepto->modo_liquidacion_id == 16) { // Intereses de cesantías
               $one_line['percentage'] = 12;
               $one_line['cesantias-interest'] = $registro->valor_devengo;
            }
         }
      }
      
      if($concepto->cpto_dian->id == 32) // INCAPACIDAD
      {
         $one_line['days'] = round( $registro_concepto->sum('cantidad_horas') / (float)config('nomina.horas_dia_laboral') , 0 );
         if ($registro_concepto->first()->novedad_tnl != null) {
            $one_line['medical-leave-type'] = strtoupper($registro_concepto->first()->novedad_tnl->origen_incapacidad);
         }
      }
      
      if ( $concepto->cpto_dian->tipo_concepto == 'amount-ns') {
         $one_line['amount-ns'] = $amount;
         unset($one_line['amount']);
      }else{
         $one_line['amount'] = $amount;
      }

      if ( $concepto->id == 78 ) {
         //dd( $concepto->cpto_dian->tipo_concepto, $concepto->cpto_dian, $one_line);
      }

      return $one_line;
   }

   public function get_arr_employee_data($empleado, $lapso)
   {
      $fecha_ingreso = explode('-',$empleado->fecha_ingreso);
      $data['code'] = "" . $empleado->tercero->numero_identificacion . "";
      $data['payment-means'] = 'EFECTIVO';
      $data['worker-type'] = $this->get_worker_type_for_technology_supplier($empleado->tipo_cotizante);
      $data['sub-code'] = 'NO_APLICA';
      $data['start-date'] = $fecha_ingreso[2].'/'.$fecha_ingreso[1].'/'.$fecha_ingreso[0];
      //$data['fire-date'] = '';
      $data['high-risk'] = false;
      $data['integral-salary'] = ($empleado->salario_integral)?true:false;
      $data['contract-type'] = 'TERMINO_FIJO';
      $data['identification-type'] = $this->get_identification_type_for_technology_supplier($empleado->tercero->tipo_doc_identidad->abreviatura);
      $data['identification'] = "" . $empleado->tercero->numero_identificacion . "";
      $data['first-name'] = $empleado->tercero->nombre1;

      if ($empleado->tercero->otros_nombres != '') {
         $data['other-names'] = $empleado->tercero->otros_nombres;
      }
      
      $data['last-name'] = $empleado->tercero->apellido1;
      if ($data['last-name'] == '') {
         $nombre1 = explode(' ', $empleado->tercero->descripcion);
         $data['first-name'] = $nombre1[0];
         $data['last-name'] = $nombre1[1];
      }
      
      if ($empleado->tercero->apellido2 != '') {
         $data['second-last-name'] = $empleado->tercero->apellido2;
      }

      //$data['bank'] = '';
      //$data['account-type-kw'] = '';
      //$data['account-number'] = '';
      
      // 16925001 = 169 pais, 25 departamento, 001 ciudad
      $department_id = substr($empleado->tercero->ciudad->id,3,2);
      $city_id = substr($empleado->tercero->ciudad->id, 5, strlen($empleado->tercero->ciudad->id)-1);

      $direccion1 = $empleado->tercero->direccion1;
      if ($direccion1 == '') {
         $direccion1 = $empleado->tercero->ciudad->descripcion;
      }

      $data['address'] = [
         'city' => $city_id,
         'line' => $direccion1,
         'department' => $department_id
      ];      

      if ( $empleado->tercero->email != '' && gettype( filter_var($empleado->tercero->email, FILTER_VALIDATE_EMAIL) ) == 'string') {
         $data['email'] = $empleado->tercero->email;
      }

      return [
         'employee' => $data
      ];
   }

   public function get_identification_type_for_technology_supplier($identification_code)
   {
      switch ($identification_code) {
         case 'CC':
            return 'CEDULA_DE_CIUDADANIA';
            break;
         
         case 'CE':
            return 'CEDULA_DE_EXTRANJERIA';
            break;

         case 'DNI':
            return 'DOCUMENTO_DE_IDENTIFICATION_EXTRANJERO';
            break;
         
         case 'NIT':
            return 'NIT';
            break;
         
         case 'NITOP':
            return 'NIT_DE_OTRO_PAIS';
            break;
         
         case 'NUIP':
            return 'NUIP';
            break;
         
         case 'PAS':
            return 'PASAPORTE';
            break;
      
         case 'PEP':
            return 'PEP';
            break;
      
         case 'RC':
            return 'REGISTRO_CIVIL';
            break;

         case 'TE':
            return 'TARJETA_DE_EXTRANJERIA';
            break;

         case 'TI':
            return 'TARJETA_DE_IDENTIDAD';
            break;
            
         default:
            return 'CEDULA_DE_CIUDADANIA';
            break;
      }
   }

   public function get_worker_type_for_technology_supplier($tipo_cotizante)
   {
      /**
      * Faltan todos estos 
      * ['' '' 'COOPERADOS_O_PRE_COOPERATIVAS_DE_TRABAJO_ASOCIADO' '' 'DEPENDIENTE_ENTIDADES_O_UNIVERSIDADES_PUBLICAS_CON_REGIMEN_ESPECIAL_EN_SALUD' 'ESTUDIANTES_APORTES_SOLO_RIESGOS_LABORALES' 'ESTUDIANTES_DE_POSTGRADO_EN_SALUD' 'ESTUDIANTES_DE_PRACTICAS_LABORALES_EN_EL_SECTOR_PUBLICO' 'FUNCIONARIOS_PUBLICOS_SIN_TOPE_MAXIMO_DE_IBC' 'MADRE_COMUNITARIA' 'PRE_PENSIONADO_CON_APORTE_VOLUNTARIO_A_SALUD' 'PRE_PENSIONADO_DE_ENTIDAD_EN_LIQUIDACION.' '' 'SERVICIO_DOMESTICO' 'TRABAJADOR_DEPENDIENTE_DE_ENTIDAD_BENEFICIARIA_DEL_SISTEMA_GENERAL_DE_PARTICIPACIONES_APORTES_PATRONALES' '']
       */
      switch ($tipo_cotizante) {
         case '01':
            return 'DEPENDIENTE';
            break;
         
         case '12':
            return 'APRENDICES_DEL_SENA_EN_ETAPA_LECTIVA';
            break;

         case '19':
            return 'APRENDICES_DEL_SENA_EN_ETAPA_PRODUCTIVA';
            break;
         
         case '22':
            return 'PROFESOR_DE_ESTABLECIMIENTO_PARTICULAR';
            break;
         
         case '51':
            return 'TRABAJADOR_DE_TIEMPO_PARCIAL';
            break;
            
         default:
            return 'DEPENDIENTE';
            break;
      }
   }

   public function store_resultado_envio_documento( $document_header, $array_respuesta, $json_doc_electronico_enviado )
   {
      if (isset($array_respuesta['dian_messages'])) {
         $array_respuesta['dian_messages'] = json_encode( $array_respuesta['dian_messages'] );
      }
      
      $array_respuesta['core_empresa_id'] = $document_header->core_empresa_id;
      $array_respuesta['core_tipo_transaccion_id'] = $document_header->core_tipo_transaccion_id;
      $array_respuesta['core_tipo_doc_app_id'] = $document_header->core_tipo_doc_app_id;
      $array_respuesta['consecutivo'] = $document_header->consecutivo;
      $array_respuesta['fecha'] = $document_header->fecha;
      
      $array_respuesta['objeto_json_enviado'] = $json_doc_electronico_enviado;

      ResultadoEnvioDocumento::create($array_respuesta);
   }

   public function consultar_documento_emitido($doc_encabezado)
   {
      //$env = config('nomina.nom_elec_ambiente'); //'PRUEBAS' || 'PRODUCCION'

      $url_emision = config('nomina.url_servicio_emision');

      //$url_emision2 = 'https://api.dataico.com/direct/payroll-api/payroll-entries/NE/51?include-pdf=true';
         
      try {
         $client = new Client(['base_uri' => $url_emision]);

         $response = $client->get( $url_emision . '/' . $doc_encabezado->tipo_documento_app->prefijo . '/' .$doc_encabezado->consecutivo . '?include-pdf=true', [
             // un array con la data de los headers como tipo de peticion, etc.
             'headers' => [
                           'content-type' => 'application/json',
                           'auth-token' => config('facturacion_electronica.tokenPassword')
                        ]
         ]);
      } catch (\GuzzleHttp\Exception\RequestException $e) {
          $response = $e->getResponse();
      }

      return json_decode( (string) $response->getBody() );
   }
}