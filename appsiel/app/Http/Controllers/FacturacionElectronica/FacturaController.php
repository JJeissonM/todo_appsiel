<?php

namespace App\Http\Controllers\FacturacionElectronica;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Ventas\VentaController;
use App\Http\Controllers\Core\TransaccionController;

use Auth;
use View;
use Input;

use App\Core\EncabezadoDocumentoTransaccion;

use App\Sistema\Html\BotonesAnteriorSiguiente;

use App\Inventarios\RemisionVentas;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\NotaCredito;

use App\CxC\CxcAbono;

use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoMovimiento;

use App\FacturacionElectronica\TFHKA\DocumentoElectronico;
use App\FacturacionElectronica\ResultadoEnvioDocumento;
use App\FacturacionElectronica\Factura;

class FacturaController extends TransaccionController
{
    public function index()
    {
    	return view('facturacion_electronica.index');
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
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $encabezado_factura = $encabezado_documento->crear_nuevo( $datos );

        // Paso 3 ( este falta refactorizar: separa la creación de lineas de registros de la contabilización y del registro del recaudo )
        $registros_medio_pago = new RegistrosMediosPago;
        $campo_lineas_recaudos = $registros_medio_pago->depurar_tabla_registros_medios_recaudos( $datos['lineas_registros_medios_recaudo'] );
        $request['creado_por'] = Auth::user()->email;
        $request['registros_medio_pago'] = $registros_medio_pago->get_datos_ids( $campo_lineas_recaudos );
        $lineas_registros = json_decode( $request->lineas_registros );
        VentaController::crear_registros_documento( $request, $encabezado_factura, $lineas_registros );

        // Paso 4 (Se está haciendo en el Paso 3)
        //$this->contabilizar( $encabezado_documento );

        // Paso 5: Enviar factura electrónica
        if ( empty( $encabezado_factura->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
            $encabezado_factura->estado = 'Sin enviar';
            $encabezado_factura->save();

            return redirect( 'fe_factura/'.$encabezado_factura->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error', 'El documento de factura no tiene resolución asociada. La factura electrónica no pudo ser enviada.');
        }
        
        $resultado = $this->procesar_envio_factura( $encabezado_factura );

        $mensaje = $this->get_mensaje( $resultado );

        if ( $mensaje->tipo == 'mensaje_error' )
        {
        	$encabezado_factura->estado = 'Sin enviar';
        	
        }else{
        	$encabezado_factura->estado = 'Enviada';
        }
        $encabezado_factura->save();

    	return redirect( 'fe_factura/'.$encabezado_factura->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion)->with( $mensaje->tipo, $mensaje->contenido);

    }

    public function procesar_envio_factura( $encabezado_factura, $adjuntos = 0 )
    {
    	$documento = new DocumentoElectronico();
    	
    	$documento_factura = $documento->preparar_objeto_documento( $encabezado_factura );

        $documento_factura->tipoOperacion = "10"; // Para facturas: Estándar
    	$documento_factura->tipoDocumento = "01"; //Facturas

    	//dd($documento_factura);
		$params = array(
				         'tokenEmpresa' =>  config('facturacion_electronica.tokenEmpresa'),
				         'tokenPassword' => config('facturacion_electronica.tokenPassword'),
				         'factura' => $documento_factura,
				         'adjuntos' => $adjuntos 
				     	);

		//Enviar Objeto Factura
		$resultado_original = $documento->WebService->enviar( config('facturacion_electronica.WSDL'), $documento->options, $params );
		
		$resultado_almacenar = $this->formatear_resultado( $resultado_original );

		$resultado_almacenar['vtas_doc_encabezado_id'] = $encabezado_factura->id;
		ResultadoEnvioDocumento::create( $resultado_almacenar );

		return $resultado_original;
    }

    public function formatear_resultado( $resultado )
    {
    	$mensaje = '';
    	if ( !is_null( $resultado['mensajesValidacion'] ) )
		{
			if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
			{
				$mensaje .= $resultado["mensajesValidacion"]->string . '\n';
			}else{
				foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
				{
					$mensaje .= $value . '\n';
				}
			}				
		}
		$resultado["mensajesValidacion"] = $mensaje;

		
    	$mensaje = '';
    	if ( !is_null( $resultado['reglasNotificacionDIAN'] ) )
		{
			$mensaje = '<br>Notificaciones DIAN<br>';
			if ( gettype( $resultado["reglasNotificacionDIAN"]->string ) == 'string' )
			{
				$mensaje .= $resultado["reglasNotificacionDIAN"]->string . '\n';
			}else{
				foreach ($resultado["reglasNotificacionDIAN"]->string as $key => $value) 
				{
					$mensaje .= $value . '\n';
				}
			}
		}
		$resultado["reglasNotificacionDIAN"] = $mensaje;
		
    	$mensaje = '';
    	if ( !is_null( $resultado['reglasValidacionDIAN'] ) )
		{
			$mensaje = '<br>Validaciones DIAN<br>';
			if ( gettype( $resultado["reglasValidacionDIAN"]->string ) == 'string' )
			{
				$mensaje .= $resultado["reglasValidacionDIAN"]->string . '\n';
			}else{
				foreach ($resultado["reglasValidacionDIAN"]->string as $key => $value) 
				{
					$mensaje .= $value . '\n';
				}
			}
		}
		$resultado["reglasValidacionDIAN"] = $mensaje;

		return $resultado;
    }

    public function get_mensaje( $resultado )
    {
    	$mensaje = (object)['tipo'=>'','contenido'=>''];

    	switch ( $resultado["codigo"] ) {
    		case '200':
    			$mensaje->tipo = 'flash_message';
    			$mensaje->contenido = '<h3>Factura enviada correctamente hacia el proveedor tecnológico</h3>';
    			$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Consecutivo:  " .$resultado["consecutivoDocumento"] ."</br>CUFE:  " .$resultado["cufe"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Hash:  " .$resultado["hash"] ."</br>Reglas de validación DIAN:  " .$resultado["reglasValidacionDIAN"] ."</br>Resultado:  " .$resultado["resultado"] ."</br>Tipo de CUFE:  " .$resultado["tipoCufe"] ."</br>Mensaje Validación:  ";

    			if ( !is_null( $resultado['mensajesValidacion'] ) )
				{
					if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
					{
						$mensaje->contenido .= "</br>" .$resultado["mensajesValidacion"]->string;
					}else{
						foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
						{
							$mensaje->contenido .= "</br>" . $value;
						}
					}				
				}
    			break;
    		
    		default:
    			$mensaje->tipo = 'mensaje_error';

				$mensaje->contenido .= "Código: " .$resultado["codigo"] ."</br>Mensaje:  " .$resultado["mensaje"] ."</br>Fecha de Respuesta:  " .$resultado["fechaRespuesta"] ."</br>Mensaje Validación:  ";

				if ( !is_null( $resultado['mensajesValidacion'] ) )
				{
					if ( gettype( $resultado["mensajesValidacion"]->string ) == 'string' )
					{
						$mensaje->contenido .= "</br>" . $resultado["mensajesValidacion"]->string;
					}else{
						foreach ($resultado["mensajesValidacion"]->string as $key => $value) 
						{
							$mensaje->contenido .= "</br>" . $value;
						}
					}				
				}
    			break;
    	}

    	return $mensaje;
    }

    // Llamado directamente
    public function enviar_factura_electronica( $id )
    {
        $encabezado_factura = Factura::find( $id );

        $resultado = $this->procesar_envio_factura( $encabezado_factura );

        $mensaje = $this->get_mensaje( $resultado );

        if ( $mensaje->tipo != 'mensaje_error' )
        {
            $encabezado_factura->estado = 'Enviada';
            $encabezado_factura->save();
        }

        return redirect( 'fe_factura/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
    }
}
