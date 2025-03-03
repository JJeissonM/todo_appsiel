<?php

namespace App\NominaElectronica\DATAICO;

use App\Core\TipoDocApp;
use Illuminate\Database\Eloquent\Model;

use App\Sistema\Services\AppDocType;

use App\Sistema\TipoTransaccion;

use App\Nomina\NomContrato;
use App\Nomina\ValueObjects\LapsoNomina;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// declaramos factura
class DocumentoSoporte extends Model
{
   const CORE_TIPO_TRANSACCION_ID = 59; // Documentos soporte Nómina Electrónica

   public $array_head_data, $array_accruals, $array_deductions, $array_employee;

   protected $table = 'nom_elect_doc_soporte';

   protected $fillable = [ 'core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'nom_contrato_id', 'descripcion', 'head_data_json', 'accruals_json', 'deductions_json', 'employee_json', 'estado', 'creado_por', 'modificado_por' ];

   public $encabezado_tabla = ['Acción', 'Fecha', 'Documento', 'Empleado', 'Estado'];

   public $urls_acciones = '{"show":"nom_electronica_show_doc_soporte/id_fila"}';

   public function tipo_transaccion()
   {
       return $this->belongsTo( TipoTransaccion::class, 'core_tipo_transaccion_id' );
   }

   public function tipo_documento_app()
   {
       return $this->belongsTo( TipoDocApp::class, 'core_tipo_doc_app_id' );
   }

   public function empleado()
   {
       return $this->belongsTo( NomContrato::class, 'nom_contrato_id' );
   }

   public function get_value_to_show()
   {
       return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
   }

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
            ->where("nom_elect_doc_soporte.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",nom_elect_doc_soporte.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_elect_doc_soporte.estado", "LIKE", "%$search%")
            ->orderBy('nom_elect_doc_soporte.created_at', 'DESC')
            ->paginate($nro_registros);
   }

	public static function sqlString($search)
	{
		$string = DocumentoSoporte::select(
			DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",nom_elect_doc_soporte.consecutivo) AS DOCUMENTO'),
			'nom_elect_doc_soporte.fecha AS FECHA',
			'core_terceros.descripcion AS EMPLEADO',
			'nom_elect_doc_soporte.estado AS ESTADO'
		)->where("nom_elect_doc_soporte.fecha", "LIKE", "%$search%")
      ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
      ->orWhere("core_terceros.estado", "LIKE", "%$search%")
			->orderBy('nom_elect_doc_soporte.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE RESULTADO ENVIO DE DOCUMENTOS";
	}

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
         $one_line['days'] = round( $registro_concepto->sum('cantidad_horas') / (float)config('nomina.horas_dia_laboral') , 0 );
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

   public function get_json_to_send()
   {
      $lapso = new LapsoNomina( $this->fecha );
      
      $document_header = $this->toArray();

      $data = [ 
         'env' => config('nomina.nom_elec_ambiente'),
         'send_dian' => true,
         'prefix' => $this->tipo_documento_app->prefijo,
         'number' => $this->consecutivo,
         'salary' =>  $this->empleado->sueldo,
         'periodicity' => 'MENSUAL',
         'initial-settlement-date' => formatear_fecha_factura_electronica($lapso->fecha_inicial),
         'final-settlement-date' => formatear_fecha_factura_electronica($lapso->fecha_final),
         'issue-date' => formatear_fecha_factura_electronica($lapso->fecha_final),
         'payment-date' => formatear_fecha_factura_electronica($lapso->fecha_final),
         'accruals' => json_decode($document_header['accruals_json'],true),
         'deductions' => json_decode($document_header['deductions_json'],true),
         'employee' => json_decode($document_header['employee_json'],true),
         'software' => [
            'pin' => config('nomina.pin_software'),
            'test-set-id' => config('nomina.tokenEmpresa'),
            'dian-id' => config('nomina.tokenDian'),
         ]
      ];

      //dd('{"env":"PRODUCCION","prefix":"N","number":1,"salary":1854290.00,"periodicity":"MENSUAL","initial-settlement-date":"01/01/2023","final-settlement-date":"31/01/2023","issue-date":"04/04/2023","payment-date":"31/01/2023","accruals":[{"amount":51393.00,"code":"AUXILIO_DE_TRANSPORTE"},{"days":11,"amount":679906.0,"code":"BASICO"},{"days":19,"amount":1174384.0,"code":"VACACION"}],"deductions":[{"amount":74172.0,"percentage":4.0,"code":"SALUD"},{"amount":74172.0,"percentage":4.0,"code":"FONDO_PENSION"}],"employee":{"other-names":"PEDRO","second-last-name":"PICAPIEDRA","first-name":"","integral-salary":false,"fire-date":"31/12/2023","email":"pedro@hotmail.com","last-name":"BEDOYA","worker-type":"DEPENDIENTE","address":{"line":"CR 53 50 31","city":"101","department":"05"},"identification":"111111111","payment-means":"EFECTIVO","sub-code":"NO_APLICA","start-date":"18/01/2012","identification-type":"NIT","contract-type":"TERMINO_FIJO"},"software":{"pin":"4123412","test-set-id":"201b3830-cf33-4966-9e63-4e8dcc450457","dian-id":"d0e88268-a4ab-447d-918c-19c1c248b5c3"}}',json_encode($data));

      return $data;
   
   }
}