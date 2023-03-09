<?php

namespace App\Http\Controllers\Nomina;

use App\FacturacionElectronica\DATAICO\ResultadoEnvio;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Sistema\Services\AppModel;
use App\Sistema\TipoTransaccion;

use App\Nomina\ValueObjects\LapsoNomina;

use App\NominaElectronica\DATAICO\DocumentoSoporte;

class NominaElectronicaController extends Controller
{
    const CORE_TIPO_TRANSACCION_ID = 59; // Documentos soporte Nómina Electrónica
    public $lapso;
    public $datos_vista = [];

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
        $lapso = new LapsoNomina( $request->fecha_final_periodo );
        $empleados_con_movimiento = $lapso->get_empleados_con_movimiento();
        $almacenar_registros = $request->almacenar_registros;

        // Un "Documento de soporte de nómina electrónica" por cada empleado
        foreach ( $empleados_con_movimiento as $registro_empleado )
        {
            $empleado = $registro_empleado->contrato;

            $doc_soporte_empleado = new DocumentoSoporte();

            $datos_doc_soporte = $doc_soporte_empleado->get_data_for_json( $empleado, $lapso, $almacenar_registros );

            dd($datos_doc_soporte);

            $this->actualizar_datos_vista( $datos_doc_soporte );

            if( $almacenar_registros )
            {
                $doc_soporte_empleado->almacenar_registro( $datos_doc_soporte );

                $doc_soporte_empleado->enviar_al_proveedor_tecnologico();
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
        dd( $this->datos_vista );
    }

    public function enviar_al_proveedor_tecnologico()
    {
        switch ( config('facturacion_electronica.proveedor_tecnologico_default') )
        {
            case 'DATAICO':
                $factura_dataico = new DocumentoSoporte( $this, 'factura' );
                $mensaje = $factura_dataico->procesar_envio();
                break;
            
            case 'TFHKA':
                //
                break;
            
            default:
                // code...
                break;
        }
                
        return $mensaje;
    }

    public function procesar_envio( $factura_doc_encabezado = null )
   {

      switch ( $this->tipo_transaccion )
      {
         case 'documento_soporte':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_factura();
            break;
         
         case 'nota_credito':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_nota_credito( $factura_doc_encabezado );
            break;
         
         case 'nota_debito':
            $json_doc_electronico_enviado = $this->preparar_cadena_json_factura();
            break;
         
         default:
            // code...
            break;
      }

      /*
dd($json_doc_electronico_enviado);
   
*/
      //dd(json_decode( $json_doc_electronico_enviado )->invoice);
      try {
         $client = new Client(['base_uri' => $this->url_emision]);

         $response = $client->post( $this->url_emision, [
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

      //dd( $array_respuesta );

      $obj_resultado = new ResultadoEnvio;
      $mensaje = $obj_resultado->almacenar_resultado( $array_respuesta, json_decode( $json_doc_electronico_enviado ), $this->doc_encabezado->id );

      return $mensaje;
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
