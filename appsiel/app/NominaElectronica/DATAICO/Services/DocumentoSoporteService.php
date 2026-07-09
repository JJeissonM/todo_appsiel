<?php

namespace App\NominaElectronica\DATAICO\Services;

use App\Sistema\Services\AppDocType;

use App\Sistema\TipoTransaccion;

use App\Nomina\NomContrato;
use App\Nomina\ParametroLegal;
use App\Nomina\ValueObjects\LapsoNomina;
use App\NominaElectronica\DATAICO\DocumentoSoporte;
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

   public function get_data_for_documento_soporte( DocumentoSoporte $documento_soporte )
   {
      $lapso = new LapsoNomina( $documento_soporte->fecha );

      return array_merge(
         ['empleado' => $documento_soporte->empleado],
         $this->get_arr_head_data_from_documento( $documento_soporte, $lapso ),
         $this->get_arr_content_data( $documento_soporte->empleado, $lapso ),
         $this->get_arr_employee_data( $documento_soporte->empleado, $lapso )
      );
   }

   public function get_data_to_store_from_calculation( array $datos_doc_soporte, $user_id = null )
   {
      $data = [
         'consecutivo' => $datos_doc_soporte['number'],
         'nom_contrato_id' => $datos_doc_soporte['empleado']->id,
         'descripcion' => '',
         'head_data_json' => json_encode( $this->get_head_data_to_store( $datos_doc_soporte ) ),
         'accruals_json' => json_encode( $this->remove_status_line( $datos_doc_soporte['accruals'] ) ),
         'deductions_json' => json_encode( $this->remove_status_line( $datos_doc_soporte['deductions'] ) ),
         'employee_json' => json_encode( $datos_doc_soporte['employee'] ),
         'estado' => 'Sin enviar'
      ];

      if ( !is_null($user_id) )
      {
         $data['creado_por'] = $user_id;
      }

      return $data;
   }

   public function recalcular_json_documento_soporte( DocumentoSoporte $documento_soporte, $user_id = null )
   {
      $datos_doc_soporte = $this->get_data_for_documento_soporte( $documento_soporte );

      if ( $this->hay_errores($datos_doc_soporte) )
      {
         return [
            'ok' => false,
            'message' => 'No se pudo recalcular el documento porque hay conceptos sin configuración DIAN.',
            'datos_doc_soporte' => $datos_doc_soporte
         ];
      }

      $data = $this->get_data_to_store_from_calculation( $datos_doc_soporte );
      unset( $data['consecutivo'], $data['nom_contrato_id'], $data['descripcion'], $data['estado'], $data['creado_por'] );

      if ( !is_null($user_id) )
      {
         $data['modificado_por'] = $user_id;
      }

      $documento_soporte->fill( $data );
      $documento_soporte->save();

      return [
         'ok' => true,
         'message' => 'Documento recalculado correctamente.',
         'datos_doc_soporte' => $datos_doc_soporte
      ];
   }

   public function hay_errores($datos_doc_soporte)
   {
      foreach ($datos_doc_soporte['accruals'] as $line) {
         if ( isset($line['status']) && $line['status'] == 'error' ) {
            return true;
         }
      }

      foreach ($datos_doc_soporte['deductions'] as $line) {
         if ( isset($line['status']) && $line['status'] == 'error' ) {
            return true;
         }
      }

      if ( isset($datos_doc_soporte['employee_errors']) && !empty($datos_doc_soporte['employee_errors']) ) {
         return true;
      }

      return false;
   }

   public function remove_status_line($json_string)
   {
      $rows = [];
      foreach ($json_string as $line) {
         if (isset($line['status'])) {
            unset($line['status']);
         }
         if (isset($line['concept-description'])) {
            unset($line['concept-description']);
         }

         $rows[] = $line;
      }

      return $rows;
   }

   protected function get_head_data_to_store( array $datos_doc_soporte )
   {
      $keys = [
         'env',
         'send_dian',
         'send_email',
         'prefix',
         'number',
         'salary',
         'periodicity',
         'initial-settlement-date',
         'final-settlement-date',
         'issue-date',
         'payment-date',
         'notes',
         'software'
      ];

      return array_intersect_key( $datos_doc_soporte, array_flip($keys) );
   }

   public function get_arr_head_data_from_documento( DocumentoSoporte $documento_soporte, $lapso )
   {
      return $this->build_arr_head_data(
         $documento_soporte->empleado,
         $lapso,
         $documento_soporte->tipo_documento_app,
         $documento_soporte->consecutivo
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

      return $this->build_arr_head_data( $empleado, $lapso, $core_tipo_doc_app, $consecutivo );
   }

   protected function build_arr_head_data( $empleado, $lapso, $core_tipo_doc_app, $consecutivo )
   {
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
            ],
         'software' => [
            'pin' => config('nomina.pin_software'),
            'test-set-id' => config('nomina.tokenEmpresa'),
            'dian-id' => config('nomina.tokenDian'),
         ]
      ];
   }

   public function get_arr_content_data( $empleado, $lapso )
   {
      $registros = $empleado->get_registros_documentos_nomina_entre_fechas($lapso->fecha_inicial, $lapso->fecha_final);
      $horas_dia_laboral = $this->get_horas_dia_laboral($lapso->fecha_final);

      $registros_agrupados_por_concepto = $registros->groupBy('nom_concepto_id');
      
      $line_accruals = [];
      $line_deductions = [];

      foreach ($registros_agrupados_por_concepto as $concepto_id => $registro_concepto) {         
         
         $concepto = $registro_concepto->all()[0]->concepto;

         if ($concepto->naturaleza == 'devengo') {

            $value_json = $this->get_linea_empleado($registro_concepto,$concepto,$registro_concepto->sum('valor_devengo'),$registros,$horas_dia_laboral);
            if (!empty($value_json)) {
               $line_accruals[] = $value_json;
            }
            
         }else{
            $value_json = $this->get_linea_empleado($registro_concepto,$concepto,$registro_concepto->sum('valor_deduccion'),$registros,$horas_dia_laboral);
            if (!empty($value_json)) {
               $line_deductions[] = $value_json;
            }
         }
      }

      $has_basico = false;
      foreach ($line_accruals as $line) {
         if (isset($line['code']) && $line['code'] === 'BASICO') {
            $has_basico = true;
            break;
         }
      }

      if (!$has_basico) {
         $line_accruals[] = [
            'code' => 'BASICO',
            'amount' => 0,
            'days' => 0
         ];
      }

      return [
         'accruals' => $line_accruals,
         'deductions' => $line_deductions
      ];
   }

   public function get_linea_empleado($registro_concepto, $concepto, $amount, $registros, $horas_dia_laboral)
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

      $codigo_cpto_dian = $this->normalize_dian_code($concepto->cpto_dian->codigo, $concepto->descripcion);

      $skip = false;   
      if($concepto->modo_liquidacion_id ==  16) { // Intereses de cesantías. Se agrega como subconcepto de las Cesantías

         foreach ($registros as $registro) {
            if ($registro->concepto->modo_liquidacion_id == 17) { // Cesantías pagadas
               $skip = true;   
            }
         }
         if ($skip) {
            return [];
         }

         // Solo se pagan Intereses
         $one_line['percentage'] = 12;
         $one_line['cesantias-interest'] = $amount;
         $amount = 0;
         $codigo_cpto_dian = 'CESANTIAS';
      }
      
      $one_line['code'] = $codigo_cpto_dian;
      $one_line['concept-description'] = $concepto->descripcion;

      if ($amount <= 0 && $concepto->modo_liquidacion_id != 16) { // Intereses de cesantías
         return [];
      }
      
      if ($concepto->cpto_dian->liquida_dias) {
         if ($horas_dia_laboral <= 0) {
            return $this->get_error_line('No hay parámetro legal activo con Horas por día laboral mayor a cero para el periodo del documento. Revise nom_parametros_legales.');
         }

         $one_line['days'] = round( $registro_concepto->sum('cantidad_horas') / $horas_dia_laboral , 0 );
      }
      
      if ($concepto->cpto_dian->liquida_horas) {
         $one_line['hours'] = $registro_concepto->sum('cantidad_horas');
      }
      
      if ($concepto->cpto_dian->porcentaje_del_basico != 0) {
         $one_line['percentage'] = $concepto->cpto_dian->porcentaje_del_basico;
      }

      if (in_array($codigo_cpto_dian, ['OTRO_CONCEPTO','OTRA_DEDUCCION'])) {
         $one_line['description'] = $concepto->descripcion;
      }
      
      if($concepto->modo_liquidacion_id == 17) // Cesantías pagadas
      {
         $one_line['percentage'] = 12;
         $cesantias_interest = 0;
         foreach ($registros as $registro) {
            if ($registro->concepto->modo_liquidacion_id == 16) { // Intereses de cesantías
               $cesantias_interest += $registro->valor_devengo;
            }
         }

         $one_line['cesantias-interest'] = $cesantias_interest;
         if ($cesantias_interest == 0) {
            $one_line['cesantias-interest'] = $amount * $one_line['percentage'] / 100;
         }
      }
      
      if($concepto->cpto_dian->id == 32) // INCAPACIDAD
      {
         if ($horas_dia_laboral <= 0) {
            return $this->get_error_line('No hay parámetro legal activo con Horas por día laboral mayor a cero para el periodo del documento. Revise nom_parametros_legales.');
         }

         $one_line['days'] = round( $registro_concepto->sum('cantidad_horas') / $horas_dia_laboral , 0 );
         if ($registro_concepto->first()->novedad_tnl != null) {
            $one_line['medical-leave-type'] = strtoupper($registro_concepto->first()->novedad_tnl->origen_incapacidad);
         }
      }
      
      $one_line['amount'] = $amount;
      if ( $concepto->cpto_dian->tipo_concepto == 'amount-ns') {
         $one_line['amount-ns'] = $amount;
         unset($one_line['amount']);
      }

      return $one_line;
   }

   protected function normalize_dian_code($codigo_cpto_dian, $descripcion_concepto)
   {
      if ($codigo_cpto_dian != 'OTRO_CONCEPTO') {
         return $codigo_cpto_dian;
      }

      $descripcion = $this->normalize_text($descripcion_concepto);
      if (strpos($descripcion, 'AUXILIO') !== false || strpos($descripcion, 'ALIMENT') !== false || strpos($descripcion, 'ALMUERZO') !== false || strpos($descripcion, 'REEMBOLSO') !== false || strpos($descripcion, 'REMBOLSO') !== false) {
         return 'AUXILIO';
      }

      return $codigo_cpto_dian;
   }

   protected function normalize_text($value)
   {
      return strtoupper(strtr((string)$value, [
         'á' => 'a',
         'é' => 'e',
         'í' => 'i',
         'ó' => 'o',
         'ú' => 'u',
         'Á' => 'A',
         'É' => 'E',
         'Í' => 'I',
         'Ó' => 'O',
         'Ú' => 'U',
         'ñ' => 'n',
         'Ñ' => 'N',
      ]));
   }

   protected function get_horas_dia_laboral($fecha_periodo)
   {
      $parametro = ParametroLegal::where('estado', 'Activo')
         ->where('fecha_inicio', '<=', $fecha_periodo)
         ->where(function ($query) use ($fecha_periodo) {
            $query->whereNull('fecha_fin')
               ->orWhere('fecha_fin', '>=', $fecha_periodo);
         })
         ->orderBy('fecha_inicio', 'DESC')
         ->first();

      if (is_null($parametro)) {
         return 0;
      }

      return (float)$parametro->horas_dia_laboral;
   }

   protected function get_error_line($message)
   {
      return [
         'status' => 'error',
         'code' => 0,
         'amount' => 0,
         'message' => $message
      ];
   }

   public function get_arr_employee_data($empleado, $lapso)
   {
      $tercero = $empleado->tercero;
      if ( is_null($tercero) ) {
         return $this->get_employee_data_error([], 'El contrato #' . $empleado->id . ' no tiene tercero asociado.');
      }

      $employee_errors = [];

      $fecha_ingreso = explode('-',$empleado->fecha_ingreso);
      $data['code'] = "" . $tercero->numero_identificacion . "";
      $data['payment-means'] = 'EFECTIVO';
      $data['worker-type'] = $this->get_worker_type_for_technology_supplier($empleado->tipo_cotizante);
      $data['sub-code'] = 'NO_APLICA';
      $data['start-date'] = $fecha_ingreso[2].'/'.$fecha_ingreso[1].'/'.$fecha_ingreso[0];
      //$data['fire-date'] = '';
      $data['high-risk'] = false;
      $data['integral-salary'] = ($empleado->salario_integral)?true:false;
      $data['contract-type'] = 'TERMINO_FIJO';
      if ( is_null($tercero->tipo_doc_identidad) ) {
         $employee_errors[] = 'El tercero ' . $tercero->descripcion . ' no tiene tipo de documento de identidad configurado.';
         $data['identification-type'] = 'CEDULA_DE_CIUDADANIA';
      } else {
         $data['identification-type'] = $this->get_identification_type_for_technology_supplier($tercero->tipo_doc_identidad->abreviatura);
      }
      $data['identification'] = "" . $tercero->numero_identificacion . "";
      $data['first-name'] = $tercero->nombre1;

      if ($tercero->otros_nombres != '') {
         $data['other-names'] = $tercero->otros_nombres;
      }
      
      $data['last-name'] = $tercero->apellido1;
      if ($data['last-name'] == '') {
         $nombre1 = explode(' ', $tercero->descripcion);
         $data['first-name'] = $nombre1[0];
         $data['last-name'] = isset($nombre1[1]) ? $nombre1[1] : $nombre1[0];
      }
      
      if ($tercero->apellido2 != '') {
         $data['second-last-name'] = $tercero->apellido2;
      }

      //$data['bank'] = '';
      //$data['account-type-kw'] = '';
      //$data['account-number'] = '';

      // 16925001 = 169 pais, 25 departamento, 001 ciudad
      if ( is_null($tercero->ciudad) ) {
         $employee_errors[] = 'El tercero ' . $tercero->descripcion . ' no tiene ciudad configurada.';
      } else {
         $department_id = substr($tercero->ciudad->id,3,2);
         $city_id = substr($tercero->ciudad->id, 5, strlen($tercero->ciudad->id)-1);

         $direccion1 = $tercero->direccion1;
         if ($direccion1 == '') {
            $direccion1 = $tercero->ciudad->descripcion;
         }

         $data['address'] = [
            'city' => $city_id,
            'line' => $direccion1,
            'department' => $department_id
         ];
      }

      if ( $tercero->email != '' && gettype( filter_var($tercero->email, FILTER_VALIDATE_EMAIL) ) == 'string') {
         $data['email'] = $tercero->email;
      }

      if ( !empty($employee_errors) ) {
         return $this->get_employee_data_error($data, implode(' ', $employee_errors));
      }

      return ['employee' => $data];
   }

   protected function get_employee_data_error($data, $message)
   {
      return [
         'employee' => $data,
         'employee_errors' => [
            [
               'status' => 'error',
               'message' => $message
            ]
         ]
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
	                           'auth-token' => config('nomina.tokenPassword')
	                        ]
	         ]);
	      } catch (\GuzzleHttp\Exception\RequestException $e) {
	          $response = $e->getResponse();
	      }

	      if ( is_null($response) ) {
	         return (object)[
	            'dian_status' => 'DIAN_RECHAZADO',
	            'dian_messages' => ['No fue posible consultar el documento emitido en DATAICO.']
	         ];
	      }

	      return json_decode( (string) $response->getBody() );
	   }
	}
