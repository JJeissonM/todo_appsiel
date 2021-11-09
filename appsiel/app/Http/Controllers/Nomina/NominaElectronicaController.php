<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use View;
use Input;

use App\Sistema\Services\AppModel;
use App\Sistema\TipoTransaccion;

use App\Nomina\ValueObjects\LapsoNomina;

use App\NominaElectronica\DATAICO\ResultadoEnvio;

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
        foreach ( $empleados_con_movimiento as $registro_empleado )
        {
      dd('hi');
            $empleado = $registro_empleado->contrato;

            $doc_soporte_empleado = new DocumentoSoporte();

            $datos_doc_soporte = $doc_soporte_empleado->get_json( $empleado, $lapso, $almacenar_registros );

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
    	$this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados( $doc_encabezado );
        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $registros_tesoreria = TesoMovimiento::get_registros_un_documento( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo )->first();
        $medios_pago = View::make('tesoreria.incluir.show_medios_pago', compact('registros_tesoreria'))->render();

        // Datos de los abonos aplicados a la factura
        $abonos = CxcAbono::get_abonos_documento( $doc_encabezado );

        // Datos de Notas Crédito aplicadas a la factura
        $notas_credito = NotaCredito::get_notas_aplicadas_factura( $doc_encabezado->id );

        $documento_vista = '';

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        //$url_crear = $this->modelo->url_crear.$this->variables_url;
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

        $url_crear = $acciones->create;

        return view( 'facturacion_electronica.facturas.show', compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito','medios_pago') );

    }

    public function store( Request $request )
    {
    	$datos = $request->all();

    	// Paso 1
    	$remision = new RemisionVentas;
    	$documento_remision = $remision->crear_nueva( $datos );

    	// Paso 2
    	$datos['creado_por'] = Auth::user()->email;
    	$datos['remision_doc_encabezado_id'] = $documento_remision->id;
        $datos['estado'] = 'Sin enviar';
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $encabezado_factura = $encabezado_documento->crear_nuevo( $datos );

        $lineas_registros = json_decode( $request->lineas_registros );
        $encabezado_factura->almacenar_lineas_registros( $lineas_registros );
        
        $encabezado_factura->actualizar_valor_total();
        
        // NOTA: No se crea el movimiento de ventas, ni de tesoreria, ni de contabilidad

        $mensaje = (object)[ 'tipo'=>'flash_message', 'contenido' => 'Documento almacenado creado correctamente.' ];

        // Paso 3: Validar Resolución (secuenciales) del documento
        if ( empty( $encabezado_factura->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
            $mensaje->tipo = 'mensaje_error';
            $mensaje->contenido .= ' NOTA: El documento de factura no tiene resolución asociada.';
        }

    	return redirect( 'fe_factura/'.$encabezado_factura->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion)->with( $mensaje->tipo, $mensaje->contenido );

    }

    // Llamado directamente
    public function enviar_factura_electronica( $id )
    {
        $encabezado_factura = Factura::find( $id );

        if ( empty( $encabezado_factura->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
            return redirect( 'fe_factura/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( 'mensaje_error', 'Documento no puede ser enviado. El pefijo ' . $encabezado_factura->tipo_documento_app->prefijo . ' no tiene una resolución asociada.');
        }

        $mensaje = $encabezado_factura->enviar_al_proveedor_tecnologico();                

        if ( $mensaje->tipo != 'mensaje_error' )
        {
            if ( $encabezado_factura->estado != 'Contabilizado - Sin enviar')
            {
                $encabezado_factura->crear_movimiento_ventas();

                // Contabilizar
                $encabezado_factura->contabilizar_movimiento_debito();
                $encabezado_factura->contabilizar_movimiento_credito();

                $encabezado_factura->crear_registro_pago();
            }
            
            $encabezado_factura->estado = 'Enviada';
            $encabezado_factura->save();
        }

        return redirect( 'fe_factura/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
    }
}
