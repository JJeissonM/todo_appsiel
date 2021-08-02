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

use App\FacturacionElectronica\ResultadoEnvio;

use App\FacturacionElectronica\DATAICO\FacturaGeneral;

class FacturaController extends TransaccionController
{
    protected $documento_factura;

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
        $datos['estado'] = 'Sin enviar';
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $encabezado_factura = $encabezado_documento->crear_nuevo( $datos );

        $lineas_registros = json_decode( $request->lineas_registros );
        $encabezado_factura->almacenar_lineas_registros( $lineas_registros );
        
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

    public function procesar_envio_factura( $encabezado_factura, $adjuntos = 0 )
    {
        // Paso 1: Prepara documento electronico
    	$documento = new DocumentoElectronico();
    	$this->documento_factura = $documento->preparar_objeto_documento( $encabezado_factura );
        $this->documento_factura->tipoOperacion = "10"; // Para facturas: Estándar
    	$this->documento_factura->tipoDocumento = "01"; //Facturas

        // Paso 2: Preparar parámetros para envío
		$params = array(
				         'tokenEmpresa' =>  config('facturacion_electronica.tokenEmpresa'),
				         'tokenPassword' => config('facturacion_electronica.tokenPassword'),
				         'factura' => $this->documento_factura,
				         'adjuntos' => $adjuntos 
				     	);

		// Paso 3: Enviar Objeto Documento Electrónico
		$resultado_original = $documento->WebService->enviar( config('facturacion_electronica.WSDL'), $documento->options, $params );

		return $resultado_original;
    }

    // Llamado directamente
    public function enviar_factura_electronica( $id )
    {
        $encabezado_factura = Factura::find( $id );

        if ( empty( $encabezado_factura->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
            return redirect( 'fe_factura/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( 'mensaje_error', 'Documento no puede ser enviado. El pefijo ' . $encabezado_factura->tipo_documento_app->prefijo . ' no tiene una resolución asociada.');
        }

        switch ( config('facturacion_electronica.proveedor_tecnologico_default') )
        {
            case 'DATAICO':
                $factura_dataico = new FacturaGeneral( $encabezado_factura, 'factura' );
                $mensaje = $factura_dataico->procesar_envio_factura();
                break;
            
            case 'TFHKA':
                $resultado_original = $this->procesar_envio_factura( $encabezado_factura );

                // Almacenar resultado en base de datos para Auditoria
                $obj_resultado = new ResultadoEnvio;
                $mensaje = $obj_resultado->almacenar_resultado( $resultado_original, $this->documento_factura, $encabezado_factura->id );
                break;
            
            default:
                // code...
                break;
        }
                

        if ( $mensaje->tipo != 'mensaje_error' )
        {
            $encabezado_factura->estado = 'Enviada';
            $encabezado_factura->save();
        }

        return redirect( 'fe_factura/'.$encabezado_factura->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
    }
}
