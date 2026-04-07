<?php

namespace App\Http\Controllers\Nomina;

use App\Core\Services\CompanyService;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;
use App\Sistema\Services\AppModel;
use App\Sistema\TipoTransaccion;

use App\Nomina\ValueObjects\LapsoNomina;

use App\NominaElectronica\DATAICO\DocumentoSoporte;
use App\NominaElectronica\DATAICO\Services\DocumentoSoporteService;
use App\Sistema\Html\BotonesAnteriorSiguiente;
use GuzzleHttp\Client;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

use Illuminate\Support\Facades\View;

class NominaElectronicaController extends TransaccionController
{
    const CORE_TIPO_TRANSACCION_ID = 59; // Documentos soporte Nómina Electrónica
    public $lapso;
    public $datos_vista = [];
    public $arr_ids_docs_generados = [];
    public $empleados_excluidos = [];

    public function index()
    {
        $model = new AppModel( 313 ); // Documento soporte Nómina Electrónica
        
        $miga_pan = [
                      [ 
                        'url' => 'NO',
                        'etiqueta' => 'Nómina Electrónica'
                        ]
                    ];

        $msj_advertencia = $this->get_mensaje_advertencia();
                
        $encabezado_tabla = $model->get_encabezado_tabla();
        
        $array_wheres = [
            ['estado', '=', 'Sin enviar']
        ];

        $registros = $model->get_records_filtered($array_wheres);
        $arr_ids_documentos_sin_enviar = json_encode($registros->pluck('id')->values()->all());

        $tabla_documentos_sin_enviar = View::make('nomina.nomina_electronica.tabla_documentos_sin_enviar', compact('model','encabezado_tabla','registros','arr_ids_documentos_sin_enviar'))->render();

    	return view('nomina.nomina_electronica.index', compact('miga_pan', 'tabla_documentos_sin_enviar', 'msj_advertencia') );
    }

    public function get_mensaje_advertencia()
    {
        $msj_advertencia = '';
        $transaccion = TipoTransaccion::find( self::CORE_TIPO_TRANSACCION_ID );
        if ( is_null( $transaccion ) )
        {
            $msj_advertencia = 'No se ha creado el tipo transacción de Documento de Soporte Nómina Electrónica.';
        }else{
            if ( is_null( $transaccion->tipos_documentos->first() ) )
            {
                $msj_advertencia = 'No hay un tipo de documento asociado a la transacción de documento de Soporte Nómina Electrónica.';
            }
        }
        
        if ( config('nomina.nom_elec_ambiente') == 'PRUEBAS' || config('nomina.nom_elec_ambiente') == null )
        {
            $msj_advertencia = 'Aún No está habilitado el MODO PRODUCCIÓN de Nómina Electrónica (variable nom_elec_ambiente en la Configuración)';
        }
        
        if ( config('nomina.nom_elect_tipo_doc_app_id') == '' || config('nomina.nom_elect_tipo_doc_app_id') == null )
        {
            $msj_advertencia = 'No se ha configurado un Tipo Doc. Default para Nómina Electrónica (variable nom_elect_tipo_doc_app_id en la Configuración)';
        }
        
        if ( config('nomina.url_servicio_emision') == '' || config('nomina.url_servicio_emision') == null )
        {
            $msj_advertencia = 'No se ha configurado la variable url_servicio_emision en la Configuración de nómina.';
        }
        
        if ( config('nomina.tokenPassword') == '' || config('nomina.tokenPassword') == null )
        {
            $msj_advertencia = 'No se ha configurado la variable tokenPassword en la Configuración de nómina.';
        }
        
        if ( config('nomina.tokenDian') == '' || config('nomina.tokenDian') == null )
        {
            $msj_advertencia = 'No se ha configurado la variable tokenDian en la Configuración de nómina.';
        }

        return $msj_advertencia;
    }

    public function generar_doc_soporte( Request $request )
    {
        $doc_soporte_service = new DocumentoSoporteService();
        $company_serv = (new CompanyService());
        
        $lapso = new LapsoNomina( $request->fecha_final_periodo );
        $this->lapso = $lapso;

        $this->datos_vista = [];
        $this->arr_ids_docs_generados = [];
        $this->empleados_excluidos = [];

        $data = [
            'core_empresa_id' => $company_serv->company->id,
            'core_tipo_transaccion_id' => self::CORE_TIPO_TRANSACCION_ID,
            'core_tipo_doc_app_id' => config('nomina.nom_elect_tipo_doc_app_id'),
            'fecha' => $lapso->fecha_final,
        ];
        
        $ids_contratos_all_docs_generados = DocumentoSoporte::where( $data )->get()->pluck('nom_contrato_id')->toArray();

        $empleados_con_movimiento = $lapso->get_empleados_con_movimiento();

        if( count($ids_contratos_all_docs_generados) == count($empleados_con_movimiento) )
        {
            return '<h4>Ya se generaron los documentos de nómina electrónica para TODOS los empleados en el periodo seleccionado.</h4>';
        }

        $almacenar_registros = $request->almacenar_registros;

        // Un "Documento de soporte de nómina electrónica" por cada empleado
        foreach ( $empleados_con_movimiento as $registro_empleado )
        {

            if ( in_array($registro_empleado->contrato->id, $ids_contratos_all_docs_generados) ) {
                continue;
            }

            $empleado = $registro_empleado->contrato;

            if ( $empleado->excluir_documentos_nomina_electronica )
            {
                $nombre_empleado = isset($empleado->tercero) && $empleado->tercero ? $empleado->tercero->descripcion : 'Contrato #' . $empleado->id;
                $identificacion = isset($empleado->tercero) && $empleado->tercero ? $empleado->tercero->numero_identificacion : null;
                $this->empleados_excluidos[] = trim( $nombre_empleado . ( $identificacion ? ' (' . $identificacion . ')' : '' ) );
                continue;
            }

            $datos_doc_soporte = $doc_soporte_service->get_data_for_json( $empleado, $lapso, $almacenar_registros );
            
            $this->actualizar_datos_vista( $datos_doc_soporte );

            if( $almacenar_registros && !$this->hay_errores($datos_doc_soporte) )
            {
                $data2 = [
                    'consecutivo' => $datos_doc_soporte['number'],
                    'nom_contrato_id' => $datos_doc_soporte['empleado']->id,
                    'descripcion' => '',
                    'head_data_json' => '',
                    'accruals_json' => json_encode( $this->remove_status_line($datos_doc_soporte['accruals']) ),
                    'deductions_json' => json_encode( $this->remove_status_line($datos_doc_soporte['deductions']) ),
                    'employee_json' => json_encode($datos_doc_soporte['employee']),
                    'estado' => 'Sin enviar',
                    'creado_por' => Auth::user()->id
                ];

                $dos_generado = DocumentoSoporte::create( $data + $data2 );

                $this->arr_ids_docs_generados[] = $dos_generado->id;
            }        
        }

        return $this->dibujar_vista();
    }

    public function remove_status_line($json_string)
    {
        $rows = [];
        foreach ($json_string as $line) {
            array_shift($line);
            $rows[] = $line;
        }
        
        return $rows;
    }

    public function hay_errores($datos_doc_soporte)
    {
        $hay_errores = false;

        $accruals = $datos_doc_soporte['accruals'];
        foreach ($accruals as $line) {
            if ( isset($line['status']) && $line['status'] == 'error' ) {
                $hay_errores = true;
            }
        }
        
        $deductions = $datos_doc_soporte['deductions'];
        foreach ($deductions as $line) {
            if ( isset($line['status']) && $line['status'] == 'error' ) {
                $hay_errores = true;
            }
        }

        return $hay_errores;
    }

    public function actualizar_datos_vista( $datos_doc_soporte )
    {
        $this->datos_vista[] = $datos_doc_soporte;
    }

    public function dibujar_vista()
    {
        return View::make('nomina.nomina_electronica.tabla_visualizacion_envio', [
            'datos_vista' => $this->datos_vista,
            'lapso' => $this->lapso,
            'arr_ids_docs_generados' => json_encode($this->arr_ids_docs_generados),
            'empleados_excluidos' => $this->empleados_excluidos
            ] )
            ->render();
    }

    public function enviar_documentos( $arr_ids )
    {        
        $arr_ids = $this->parse_arr_ids($arr_ids);
        if (empty($arr_ids)) {
            return redirect('nom_electronica?id=17&id_modelo=0#doc_soporte')->with('mensaje_error','No se recibieron documentos válidos para enviar.');
        }
        
        $resultado_lote = $this->procesar_envio_documentos($arr_ids);
        
        if ($resultado_lote['some_error']) {
            return redirect('nom_electronica?id=17&id_modelo=0#doc_soporte')->with('mensaje_error','Algunos documentos no pudieron ser enviados. Por favor revise la pestaña Docs. Soporte No enviados y los registros de envíos de documentos.');
        }

        return redirect('nom_electronica?id=17&id_modelo=0#doc_soporte')->with('flash_message','Documentos enviados correctamente.');
   }

    public function enviar_documento_ajax(Request $request, $documento_id)
    {
        $resultado = $this->procesar_envio_documento($documento_id);

        return response()->json($resultado, $resultado['ok'] ? 200 : 422);
    }

    protected function procesar_envio_documentos(array $arr_ids)
    {
        $resultados = [];
        $some_error = false;

        foreach ($arr_ids as $document_id) {
            $resultado = $this->procesar_envio_documento($document_id);
            $resultados[] = $resultado;

            if (!$resultado['ok']) {
                $some_error = true;
            }
        }

        return [
            'some_error' => $some_error,
            'resultados' => $resultados
        ];
    }

    protected function procesar_envio_documento($document_id)
    {
        $doc_soporte_service = new DocumentoSoporteService();
        $document_header = DocumentoSoporte::find($document_id);

        if (is_null($document_header)) {
            return [
                'ok' => false,
                'documento_id' => (int)$document_id,
                'documento' => '',
                'message' => 'Documento no encontrado.'
            ];
        }

        if ($document_header->estado != 'Sin enviar') {
            return [
                'ok' => false,
                'documento_id' => (int)$document_header->id,
                'documento' => $document_header->get_value_to_show(),
                'message' => 'El documento ya no está pendiente de envío.'
            ];
        }

        $json_doc_electronico_enviado = json_encode($document_header->get_json_to_send());
        $response = null;
        $array_respuesta = [];

        try {
            $client = new Client();

            $response = $client->post( config('nomina.url_servicio_emision'), [
                'headers' => [
                              'content-type' => 'application/json',
                              'auth-token' => config('nomina.tokenPassword')
                           ],
                'json' => json_decode( $json_doc_electronico_enviado )
            ]);

         } catch (\GuzzleHttp\Exception\ConnectException $e) {
             $array_respuesta = [
                'codigo' => 0,
                'dian_status' => 'DIAN_RECHAZADO',
                'dian_messages' => [ 'No fue posible conectar con Dataico. Verifique DNS/salida a internet del servidor. Detalle: ' . $e->getMessage() ]
            ];
         } catch (\GuzzleHttp\Exception\RequestException $e) {
             $response = $e->getResponse();
             if ( is_null($response) )
             {
                $array_respuesta = [
                    'codigo' => 0,
                    'dian_status' => 'DIAN_RECHAZADO',
                    'dian_messages' => [ $e->getMessage() ]
                ];
             }
         }

        if ( !is_null($response) )
        {
            $array_respuesta = json_decode( (string) $response->getBody(), true );
            if ( !is_array($array_respuesta) )
            {
                $array_respuesta = [];
            }
            $array_respuesta['codigo'] = $response->getStatusCode();
        }

        if (isset($array_respuesta['errors'])) {
            $array_respuesta['dian_messages'] = $array_respuesta['errors'];
            $array_respuesta['dian_status'] = 'DIAN_RECHAZADO';
        }

        if ( !isset($array_respuesta['dian_status']) )
        {
            $array_respuesta['codigo'] = isset($array_respuesta['codigo']) ? $array_respuesta['codigo'] : 0;
            $array_respuesta['dian_status'] = 'DIAN_RECHAZADO';
            $array_respuesta['dian_messages'] = isset($array_respuesta['dian_messages']) ? $array_respuesta['dian_messages'] : [ 'Respuesta inválida o incompleta del proveedor tecnológico. El documento no fue confirmado como enviado.' ];
        }

        $doc_soporte_service->store_resultado_envio_documento( $document_header, $array_respuesta, $json_doc_electronico_enviado );

        if ($array_respuesta['dian_status'] == 'DIAN_ACEPTADO') {
            $document_header->estado = 'Enviado';
            $document_header->save();

            return [
                'ok' => true,
                'documento_id' => (int)$document_header->id,
                'documento' => $document_header->get_value_to_show(),
                'message' => 'Documento enviado correctamente.'
            ];
        }

        $mensajes = $this->normalizar_mensajes_dian(isset($array_respuesta['dian_messages']) ? $array_respuesta['dian_messages'] : []);

        return [
            'ok' => false,
            'documento_id' => (int)$document_header->id,
            'documento' => $document_header->get_value_to_show(),
            'message' => empty($mensajes) ? 'El documento fue rechazado por el proveedor tecnológico.' : implode(' | ', $mensajes)
        ];
    }

    protected function normalizar_mensajes_dian($mensajes)
    {
        if (is_string($mensajes)) {
            $decoded = json_decode($mensajes, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $mensajes = $decoded;
            }
        }

        if (!is_array($mensajes)) {
            $mensajes = [ (string) $mensajes ];
        }

        $resultado = [];
        foreach ($mensajes as $mensaje) {
            foreach ($this->extraer_textos_mensaje_dian($mensaje) as $texto) {
                $texto = trim($texto);
                if ($texto !== '') {
                    $resultado[] = $texto;
                }
            }
        }

        return array_values(array_unique($resultado));
    }

    protected function extraer_textos_mensaje_dian($mensaje)
    {
        if (is_null($mensaje)) {
            return [];
        }

        if (is_string($mensaje) || is_numeric($mensaje) || is_bool($mensaje)) {
            return [ (string) $mensaje ];
        }

        if (!is_array($mensaje)) {
            return [ (string) $mensaje ];
        }

        $textos = [];

        if (isset($mensaje['message'])) {
            if (is_array($mensaje['message'])) {
                foreach ($mensaje['message'] as $item) {
                    $item = trim((string) $item);
                    if ($item !== '') {
                        $textos[] = $item;
                    }
                }
            } else {
                $texto = trim((string) $mensaje['message']);
                if ($texto !== '') {
                    $textos[] = $texto;
                }
            }
        }

        $path = '';
        if (isset($mensaje['path'])) {
            $path = is_array($mensaje['path']) ? implode('.', $mensaje['path']) : (string) $mensaje['path'];
            $path = trim($path);
        }

        if (!empty($textos)) {
            if ($path !== '') {
                return array_map(function ($texto) use ($path) {
                    return $path . ': ' . $texto;
                }, $textos);
            }

            return $textos;
        }

        $fallback = [];
        foreach ($mensaje as $valor) {
            foreach ($this->extraer_textos_mensaje_dian($valor) as $texto) {
                $texto = trim($texto);
                if ($texto !== '') {
                    $fallback[] = $texto;
                }
            }
        }

        return $fallback;
    }

    protected function parse_arr_ids($arr_ids)
    {
        $decoded = json_decode($arr_ids, true);

        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, function ($id) {
            return is_numeric($id);
        }));
    }

    public function show_doc_soporte( $id )
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );
        
        $doc_encabezado = DocumentoSoporte::find($id);
        $doc_encabezado->tercero = $doc_encabezado->empleado->tercero;
        $doc_encabezado->documento_transaccion_descripcion = $doc_encabezado->tipo_transaccion->descripcion;
        $doc_encabezado->documento_transaccion_prefijo_consecutivo = $doc_encabezado->get_value_to_show();

        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        //$url_crear = $this->modelo->url_crear.$this->variables_url;
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $this->transaccion->id;

        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

        $url_crear = $acciones->create;

        $documento_vista  = '';

        return view( 'nomina.nomina_electronica.show_documento_soporte', compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'doc_encabezado', 'empresa', 'url_crear','id_transaccion', 'documento_vista') );
    }

    // SOLO DATAICO
    public function consultar_documentos_emitidos( $doc_encabezado_id, $tipo_operacion )
    {
        switch ( $tipo_operacion )
        {
            case 'documento_soporte_nomina':
                $encabezado_doc = DocumentoSoporte::find( $doc_encabezado_id );
                $json_response = (new DocumentoSoporteService())->consultar_documento_emitido($encabezado_doc);

                break;
            
            default:
                // code...
                break;
        }

        /**
         * CAMPOS DEL JSON
         * "dian_status": { 'DIAN_ACEPTADO', 'DIAN_RECHAZADO'}
         * "number"
         * "dian_messages": []
         * "pdf": Cuando es aceptado
         * "email_status"
         * "cune"
         * "qrcode"
         * "response_xml": Cuando es aceptado
         * "request_xml": Cuando es rechazado.
         */

        if($json_response->dian_status != 'DIAN_ACEPTADO')
        {
            return dd($json_response);
        }

        $documento_electronico = $json_response->pdf;

        $view_pdf = View::make('nomina.nomina_electronica.pdf_base64_show',compact('documento_electronico','encabezado_doc') )->render();

        return $view_pdf;
    }
    /**
     * Conceptos deduccion DATAICO-DIAN:
     * ['DEUDA' 'AFC' 'PLANES_COMPLEMENTARIOS' 'EDUCACION' 'FONDO_PENSION' 'LIBRANZA' 'FONDO_SOLIDARIDAD_PENSIONAL' 'COOPERATIVA' 'SALUD' 'RETENCION_FUENTE' 'SANCION' 'REINTEGRO' 'ANTICIPO' 'PENSION_VOLUNTARIA' 'EMBARGO_FISCAL' 'SINDICATO' 'PAGO_TERCERO' 'OTRA_DEDUCCION' 'FONDO_SUBSISTENCIA']
     * 
     * Conceptos devengos DATAICO-DIAN
     * ['COMISION' 'COMPENSACION' 'BONO_EPCTV_ALIMENTACION' 'BONIFICACION' 'BONIFICACION_RETIRO' 'LICENCIA_REMUNERADA' 'LICENCIA_PATERNIDAD' 'PRIMA' 'VACACION_COMPENSADA' 'HUELGA_LEGAL' 'LICENCIA_NO_REMUNERADA' 'HORA_EXTRA_NOCTURNA_DF' 'HORA_EXTRA_DIURNA' 'HORA_RECARGO_DIURNA_DF' 'VACACION' 'OTRO_CONCEPTO' 'APOYO_PRACTICA' 'HORA_RECARGO_NOCTURNO_DF' 'VIATICO' 'REINTEGRO' 'INCAPACIDAD' 'INDEMNIZACION' 'ANTICIPO' 'BONO_EPCTV' 'CESANTIAS' 'HORA_RECARGO_NOCTURNO' 'HORA_EXTRA_DIURNA_DF' 'AUXILIO_DE_TRANSPORTE' 'HORA_EXTRA_NOCTURNA' 'DOTACION' 'AUXILIO' 'PAGO_TERCERO' 'TELETRABAJO' 'BASICO']
     */
}
