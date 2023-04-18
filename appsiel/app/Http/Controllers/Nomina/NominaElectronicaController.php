<?php

namespace App\Http\Controllers\Nomina;

use App\Core\Services\CompanyService;
use App\FacturacionElectronica\DATAICO\ResultadoEnvio;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Sistema\Services\AppModel;
use App\Sistema\TipoTransaccion;

use App\Nomina\ValueObjects\LapsoNomina;

use App\NominaElectronica\DATAICO\DocumentoSoporte;
use App\NominaElectronica\DATAICO\Services\DocumentoSoporteService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NominaElectronicaController extends Controller
{
    const CORE_TIPO_TRANSACCION_ID = 59; // Documentos soporte Nómina Electrónica
    public $lapso;
    public $datos_vista = [];
    public $arr_ids_docs_generados = [];

    public function index()
    {
        $model = new AppModel( 313 ); // Documento soporte Nómina Electrónica
        
        $miga_pan = [
                      [ 
                        'url' => 'NO',
                        'etiqueta' => 'Nómina Electrónica'
                        ]
                    ];

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

    	return view('nomina.nomina_electronica.index', compact('miga_pan', 'model', 'msj_advertencia') );
    }

    public function generar_doc_soporte( Request $request )
    {
        $doc_soporte_empleado = new DocumentoSoporteService();
        $company_serv = (new CompanyService());
        
        $lapso = new LapsoNomina( $request->fecha_final_periodo );
        $this->lapso = $lapso;

        $data = [
            'core_empresa_id' => $company_serv->company->id,
            'core_tipo_transaccion_id' => self::CORE_TIPO_TRANSACCION_ID,
            'core_tipo_doc_app_id' => config('nomina.nom_elect_tipo_doc_app_id'),
            'fecha' => $lapso->fecha_final,
        ];

        $one_doc_generado = DocumentoSoporte::where( $data )->get()->first();

        if($one_doc_generado != null)
        {
            return '<h4>Ya existen documentos de nómina electrónica generados para el periodo seleccionado.</h4>';
        }

        $empleados_con_movimiento = $lapso->get_empleados_con_movimiento();
        $almacenar_registros = $request->almacenar_registros;

        // Un "Documento de soporte de nómina electrónica" por cada empleado
        foreach ( $empleados_con_movimiento as $registro_empleado )
        {
            $empleado = $registro_empleado->contrato;

            $datos_doc_soporte = $doc_soporte_empleado->get_data_for_json( $empleado, $lapso, $almacenar_registros );
            
            $this->actualizar_datos_vista( $datos_doc_soporte );

            if( $almacenar_registros )
            {
                $data2 = [
                    'consecutivo' => $datos_doc_soporte['number'],
                    'nom_contrato_id' => $datos_doc_soporte['empleado']->id,
                    'descripcion' => '',
                    'head_data_json' => '',
                    'accruals_json' => json_encode($datos_doc_soporte['accruals']),
                    'deductions_json' => json_encode($datos_doc_soporte['deductions']),
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

    public function actualizar_datos_vista( $datos_doc_soporte )
    {
        $this->datos_vista[] = $datos_doc_soporte;
    }

    public function dibujar_vista()
    {
        return View::make('nomina.nomina_electronica.tabla_visualizacion_envio', [
            'datos_vista' => $this->datos_vista,
            'lapso' => $this->lapso,
            'arr_ids_docs_generados' => json_encode($this->arr_ids_docs_generados)
            ] )
            ->render();
    }

    public function enviar_documentos( $arr_ids )
    {
        $arr_ids = json_decode($arr_ids);
        
        foreach ($arr_ids as $key => $document_id) {
            $document_header = DocumentoSoporte::find($document_id);
            
            if ($document_header->estado != 'Sin enviar') {
                continue;
            }

            $json_doc_electronico_enviado = json_encode($document_header->get_json_to_send());

            try {
                $client = new Client(['base_uri' => config('nomina.url_servicio_emision')]);
                
                $response = $client->post( config('nomina.url_servicio_emision'), [
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
        }

      return redirect('nom_electronica?id=17&id_modelo=0')->with('flash_message','Documentos enviados correctamente.');
   }

    public function show( $id )
    {
        //
    }

    public function store( Request $request )
    {
        //
    }
}
