<?php

namespace App\Http\Controllers\FacturacionElectronica;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Contabilidad\ContabilidadController;

use App\Core\EncabezadoDocumentoTransaccion;

use App\Sistema\Html\BotonesAnteriorSiguiente;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\DevolucionVentas;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Ventas\Cliente;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\Tesoreria\TesoMovimiento;

use App\FacturacionElectronica\TFHKA\DocumentoElectronico;
use App\FacturacionElectronica\TFHKA\DocumentoReferenciado;
use App\FacturacionElectronica\ResultadoEnvioDocumento;
use App\FacturacionElectronica\ResultadoEnvio;
use App\FacturacionElectronica\Factura;
use App\FacturacionElectronica\NotaCredito;

use App\FacturacionElectronica\DATAICO\FacturaGeneral;

use App\Ventas\Services\NotaCreditoServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class NotaCreditoController extends TransaccionController
{
    protected $documento_nota_credito;

    public function index()
    {
    	return view('facturacion_electronica.index');
    }

    public function create()
    {
    	$this->set_variables_globales();

    	$fe_app_id = 21;
        $fe_factura_modelo_id = 244; // Se devuelve a la vista de Factura
        $fe_factura_transaccion_id = 52; // Se devuelve a la vista de Factura

        $id_transaccion = $this->transaccion->id;

        $factura = VtasDocEncabezado::get_registro_impresion( Input::get('factura_id') );

        if($this->transaccion->tipos_documentos->first() == null)
        {
            return redirect( 'fe_factura/'.$factura->id.'?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id  . '&id_transaccion=' . $fe_factura_transaccion_id)->with('mensaje_error','No hay un Tipo de Documento asociado para este Tipo de Transacción: ' . $this->transaccion->descripcion);
        }

        $saldo_pendiente = 0;
        $vec_saldos = [0,0,0];

        if ( is_null( Input::get('factura_id') ) )
        {
            return redirect( 'web?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id . '')->with('mensaje_error','No puede hacer notas crédito desde esta opción. Debe ir al Botón Crear Nota crédito directa');
        }

        $movimiento_cxc = CxcMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                            ->where('consecutivo', $factura->consecutivo)
                            ->get()
                            ->first();        
            
        $vec_saldos = [0, 0, 0];
        if ( $movimiento_cxc != null )
        {
            if ( $movimiento_cxc->saldo_pendiente == 0 && $factura->forma_pago == 'credito' )
            {
                return redirect('fe_factura/'.$factura->id.'?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id . '&id_transaccion=' . $fe_factura_transaccion_id)->with('mensaje_error','La factura no tiene SALDO PENDIENTE por cobrar');
            }
            
            $vec_saldos = [$movimiento_cxc->valor_documento, $movimiento_cxc->valor_pagado, $movimiento_cxc->saldo_pendiente];

            //return redirect('fe_factura/'.$factura->id.'?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id . '&id_transaccion=' . $fe_factura_transaccion_id)->with('mensaje_error','La factura no tiene registros de cuentas por cobrar');
        }       

        // Información de la Factura de ventas
        $doc_encabezado = VtasDocEncabezado::get_registro_impresion( Input::get('factura_id') );
        $doc_registros = VtasDocRegistro::get_registros_impresion( Input::get('factura_id') );

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['15-entrada'=>'Devolución por ventas'];

        $tabla = View::make( 'ventas.notas_credito.tabla_registros_create', compact( 'doc_registros', 'motivos', 'vec_saldos' ) )->render();
        
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$this->transaccion,$lista_campos,$cantidad_campos,'create',null);

        $eid = '';

        if( config("configuracion.tipo_identificador") == 'NIT') { 
            $eid = number_format( $doc_encabezado->numero_identificacion, 0, ',', '.');
        }	else { 
            $eid = $doc_encabezado->numero_identificacion;
        }

        // Agregar al comienzo del documento
        array_unshift($lista_campos, [
                                            "id" => 201,
                                            "descripcion" => "Empresa",
                                            "tipo" => "personalizado",
                                            "name" => "encabezado",
                                            "opciones" => "",
                                            "value" => '<div style="border: solid 1px #ddd; padding-top: -20px;">
                                                            <b style="font-size: 1.6em; text-align: center; display: block;">
                                                                '.$doc_encabezado->documento_transaccion_descripcion.'
                                                                <br/>
                                                                <b>No.</b> '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'
                                                            </b>
                                                            <br/>
                                                            <b>Cliente:</b> '.$doc_encabezado->tercero_nombre_completo.'
                                                            <br/>
                                                            <b>'.config("configuracion.tipo_identificador").' &nbsp;&nbsp;</b> ' . $eid. '
                                                        </div>',
                                            "atributos" => [],
                                            "definicion" => "",
                                            "requerido" => 0,
                                            "editable" => 1,
                                            "unico" => 0
                                        ] );

        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];
        
        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Crear: '.$this->transaccion->descripcion );

        return view('ventas.notas_credito.create', compact('form_create','id_transaccion','miga_pan','tabla','doc_encabezado'));
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

        $registros_tesoreria = TesoMovimiento::get_registros_un_documento( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo );
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

        return view( 'facturacion_electronica.notas_credito.show', compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito','medios_pago') );

    }

    public function store( Request $request )
    {
    	// WARNING: si la factura tiene varias entradas, no se puede hacer la nota

    	$datos = $request->all();
    	$factura = VtasDocEncabezado::get_registro_impresion( $request->ventas_doc_relacionado_id ); 

    	// Paso 1
        $datos['remision_doc_encabezado_id'] = 0;
        if (!in_array($factura->remision_doc_encabezado_id, [null, 0, '']))
        {
            $devolucion = new DevolucionVentas;
            $documento_devolucion = $devolucion->crear_nueva( $datos, $factura->remision_doc_encabezado_id );
            $datos['remision_doc_encabezado_id'] = $documento_devolucion->id;
            $request['remision_doc_encabezado_id'] = $documento_devolucion->id;
        }

    	// Paso 2
    	$datos['creado_por'] = Auth::user()->email;
        $datos['ventas_doc_relacionado_id'] = $factura->id; // Relacionar Nota con la Factura
        $datos['forma_pago'] = $factura->forma_pago;
        $datos['vendedor_id'] = $factura->vendedor_id;
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $encabezado_nota_credito = $encabezado_documento->crear_nuevo( $datos );

        // Paso 3 ( este falta refactorizar: separar la creación de lineas de registros de la contabilización y de otras transacciones )
        NotaCreditoController::crear_registros_nota_credito( $request, $encabezado_nota_credito, $factura );

        // Paso 4 (Se está haciendo en el Paso 3)
        //$this->contabilizar( $encabezado_documento );

        // Paso 5: Enviar nota electrónica
        $mensaje = $this->enviar_nota_credito_electronica( $encabezado_nota_credito->id, $factura );

    	return redirect( 'fe_nota_credito/'.$encabezado_nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion)->with( $mensaje->tipo, $mensaje->contenido);

    }

    public function enviar_nota_credito_electronica( $id, $factura_doc_encabezado )
    {
        $encabezado_nota_credito = Factura::find( $id );

        switch ( config('facturacion_electronica.proveedor_tecnologico_default') )
        {
            case 'DATAICO':
                $factura_dataico = new FacturaGeneral( $encabezado_nota_credito, 'nota_credito' );
                $mensaje = $factura_dataico->procesar_envio_factura( $factura_doc_encabezado );
                break;
            
            case 'TFHKA':
                $resultado_original = $this->procesar_envio_factura( $encabezado_nota_credito );

                // Almacenar resultado en base de datos para Auditoria
                $obj_resultado = new ResultadoEnvio;
                $mensaje = $obj_resultado->almacenar_resultado( $resultado_original, $factura_doc_encabezado, $encabezado_nota_credito->id );
                break;
            
            default:
                // code...
                break;
        }

        if ( $mensaje->tipo == 'mensaje_error' )
        {
            $encabezado_nota_credito->estado = 'Sin enviar';
            
        }else{
            $encabezado_nota_credito->estado = 'Enviada';
        }
        $encabezado_nota_credito->save();

        return $mensaje;
    }

    public function procesar_envio_factura( $encabezado_nota_credito, $adjuntos = 0 )
    {
        // Paso 1: Prepara documento electronico
    	$documento = new DocumentoElectronico();
    	$this->documento_nota_credito = $documento->preparar_objeto_documento( $encabezado_nota_credito );
    	$this->documento_nota_credito->tipoOperacion = "20"; // Para NC
        $this->documento_nota_credito->tipoDocumento = "91"; // Nota Crédito

        $datos_factura_electronica = ResultadoEnvioDocumento::where( 'vtas_doc_encabezado_id', $encabezado_nota_credito->ventas_doc_relacionado_id )->get()->first();

        $DocRef = new DocumentoReferenciado();

            $DocRef->codigoEstatusDocumento = '2';
            $DocRef->codigoInterno = '4';
            $DocRef->cufeDocReferenciado = $datos_factura_electronica->cufe;
            $DocRef->tipoCUFE = $datos_factura_electronica->tipoCufe;
            $DocRef->tipoDocumento = $datos_factura_electronica->tipoDocumento;
            $DocRef->descripcion[0] = "Nota Crédito por devolución/anulación de factura";
            $DocRef->numeroDocumento= $datos_factura_electronica->consecutivoDocumento;
        
        $this->documento_nota_credito->documentosReferenciados[0] =$DocRef;

        $DocRef1 = new DocumentoReferenciado();

            $DocRef1->codigoInterno = '5';
            $DocRef1->cufeDocReferenciado = $datos_factura_electronica->cufe;
            $DocRef1->fecha = explode( " ", $datos_factura_electronica->fechaAceptacionDIAN )[0];
            $DocRef->tipoCUFE = $datos_factura_electronica->tipoCufe;
            $DocRef->tipoDocumento = $datos_factura_electronica->tipoDocumento;
            $DocRef1->numeroDocumento= $datos_factura_electronica->consecutivoDocumento;

        $this->documento_nota_credito->documentosReferenciados[1] =$DocRef1;

        // Paso 2: Preparar parámetros para envío
		$params = array(
				         'tokenEmpresa' =>  config('facturacion_electronica.tokenEmpresa'),
				         'tokenPassword' => config('facturacion_electronica.tokenPassword'),
				         'factura' => $this->documento_nota_credito,
				         'adjuntos' => $adjuntos 
				     	);

		// Paso 3: Enviar Objeto Documento Electrónico
		$resultado_original = $documento->WebService->enviar( config('facturacion_electronica.WSDL'), $documento->options, $params );

		return $resultado_original;
    }


    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public static function crear_registros_nota_credito( Request $request, $nota_credito, $factura )
    {
        $nota_credito_service = new NotaCreditoServices();

        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        if (!in_array($nota_credito->remision_doc_encabezado_id, [null, 0, '']))
        {
            // Se crean los registro con base en el documento de inventario ya creado
            // lineas_registros solo tiene el ID del documentos de inventario
            // remision_doc_encabezado_id es el ID de una devolución en ventas
            $lineas_registros = [(object)[ 'id_doc' => $nota_credito->remision_doc_encabezado_id ]];

            NotaCreditoController::crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros, $factura );
        }else{
            NotaCreditoController::crear_lineas_registros_con_base_en_factura( $datos, $nota_credito, $factura );
        }       

        return true;
    }

    // Se crean los registros con base en los registros de la Nota crédito
    public static function crear_lineas_registros_con_base_en_factura( $datos, $nota_credito, $factura )
    {
        $total_documento = 0;

        $nota_credito_service = new NotaCreditoServices();
        
        $lineas_registros = $factura->lineas_registros;
        foreach ($lineas_registros as $linea_factura)
        {
            $cantidad = $linea_factura->cantidad * -1; // Se vuelve la cantidad negativa, porque es una disminución de las ventas

            $precio_unitario = $linea_factura->precio_unitario;

            $precio_unitario_con_descuento = $linea_factura->precio_unitario * ( 1 - $linea_factura->tasa_descuento / 100 );

            $base_impuesto = $linea_factura->base_impuesto;

            $precio_total_con_descuento = $precio_unitario_con_descuento * $cantidad;

            $valor_total_descuento = ( $precio_unitario - $precio_unitario_con_descuento ) * $linea_factura->cantidad;

            $linea_datos = [ 'inv_bodega_id' => $linea_factura->inv_bodega_id ] +
                            [ 'inv_motivo_id' => $linea_factura->inv_motivo_id ] +
                            [ 'inv_producto_id' => $linea_factura->inv_producto_id ] +
                            [ 'precio_unitario' => $precio_unitario ] +
                            [ 'cantidad' => $cantidad ] +
                            [ 'precio_total' => $precio_total_con_descuento ] +
                            [ 'base_impuesto' =>  $base_impuesto ] +
                            [ 'tasa_impuesto' => $linea_factura->tasa_impuesto ] +
                            [ 'valor_impuesto' => ( $precio_unitario_con_descuento - $base_impuesto ) ] +
                            [ 'base_impuesto_total' => ( $base_impuesto * $linea_factura->cantidad ) ] +
                            [ 'tasa_descuento' => $linea_factura->tasa_descuento ] +
                            [ 'valor_total_descuento' => $valor_total_descuento ] +
                            [ 'creado_por' => Auth::user()->email ] +
                            [ 'estado' => 'Activo' ];

            VtasDocRegistro::create( 
                                    $datos + 
                                    [ 'vtas_doc_encabezado_id' => $nota_credito->id ] +
                                    $linea_datos
                                );

            $datos['consecutivo'] = $nota_credito->consecutivo;
            VtasMovimiento::create( 
                                    $datos +
                                    $linea_datos
                                );

            // Contabilizar
            $detalle_operacion = $datos['descripcion'];

            // Reversar ingresos e impuestos
            $nota_credito_service->contabilizar_movimiento_debito( $datos + $linea_datos, $detalle_operacion );

            $total_documento += $precio_total_con_descuento;

            // Actualizar campo de cantidad_devuelta en cada línea de registro de la factura de ventas
            $nueva_cantidad_devuelta = $linea_factura->cantidad_devuelta + abs($linea_factura->cantidad);
            $linea_factura->cantidad_devuelta = $nueva_cantidad_devuelta;
            $linea_factura->save();

        }

        $nota_credito->valor_total = $total_documento;
        $nota_credito->save();
        
        // Un solo registro para reversar la cuenta por cobrar (CR)
        $nota_credito_service->contabilizar_movimiento_credito( $datos, $total_documento, $datos['descripcion'], $factura );

        // Actualizar registro del pago de la factura a la que afecta la nota
        if ($factura->forma_pago == 'credito') {
            $nota_credito_service->actualizar_registro_pago( $total_documento, $factura, $nota_credito, 'crear' );
        }else {
            $nota_credito_service->actualizar_movimiento_tesoreria( $total_documento, $factura, $nota_credito, 'crear' );
        }

        return true;
    }

    // Se crean los registros con base en los registros de la devolución
    public static function crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros, $factura )
    {
        $nota_credito_service = new NotaCreditoServices();

        $total_documento = 0;
        // Por cada remisión pendiente
        $cantidad_registros = count( $lineas_registros );
        $remision_doc_encabezado_id = '';
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_devolucion_id = (int)$lineas_registros[$i]->id_doc;

            $registros_devolucion = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_devolucion_id )->get();

            foreach ($registros_devolucion as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad * -1; // Fue una entrada de inventarios, se vuelve la cantidad negativa, porque es una diminución de las ventas

                // Los precios se deben traer de la linea de la factura
                $linea_factura = VtasDocRegistro::where( 'vtas_doc_encabezado_id', $factura->id)
                                                ->where( 'inv_producto_id', $un_registro->inv_producto_id )
                                                ->get()
                                                ->first();

                $precio_unitario = $linea_factura->precio_unitario;

                $precio_unitario_con_descuento = $linea_factura->precio_unitario * ( 1 - $linea_factura->tasa_descuento / 100 );

                $base_impuesto = $linea_factura->base_impuesto;

                $precio_total_con_descuento = $precio_unitario_con_descuento * $cantidad;

                $valor_total_descuento = ( $precio_unitario - $precio_unitario_con_descuento ) * $un_registro->cantidad;

                $linea_datos = [ 'inv_bodega_id' => $un_registro->inv_bodega_id ] +
                                [ 'inv_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total_con_descuento ] +
                                [ 'base_impuesto' =>  $base_impuesto ] +
                                [ 'tasa_impuesto' => $linea_factura->tasa_impuesto ] +
                                [ 'valor_impuesto' => ( $precio_unitario_con_descuento - $base_impuesto ) ] +
                                [ 'base_impuesto_total' => ( $base_impuesto * $un_registro->cantidad ) ] +
                                [ 'tasa_descuento' => $linea_factura->tasa_descuento ] +
                                [ 'valor_total_descuento' => $valor_total_descuento ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                VtasDocRegistro::create( 
                                        $datos + 
                                        [ 'vtas_doc_encabezado_id' => $nota_credito->id ] +
                                        $linea_datos
                                    );

                $datos['consecutivo'] = $nota_credito->consecutivo;
                VtasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // Contabilizar
                $detalle_operacion = $datos['descripcion'];

                // Reversar ingresos e impuestos                
                $nota_credito_service->contabilizar_movimiento_debito( $datos + $linea_datos, $detalle_operacion );

                $total_documento += $precio_total_con_descuento;

                // Actualizar campo de cantidad_devuelta en cada línea de registro de la factura de ventas
                $nueva_cantidad_devuelta = $linea_factura->cantidad_devuelta + abs($un_registro->cantidad);
                $linea_factura->cantidad_devuelta = $nueva_cantidad_devuelta;
                $linea_factura->save();

            } // Fin por cada registro de la entrada

            // Marcar la entrada como facturada
            InvDocEncabezado::find( $doc_devolucion_id )->update( [ 'estado' => 'Facturada' ] );

            // Se va creando un listado de entradas separadas por coma 
            if ($primera)
            {
                $remision_doc_encabezado_id = $doc_devolucion_id;
                $primera = false;
            }else{
                $remision_doc_encabezado_id .= ','.$doc_devolucion_id;
            }

        }

        $nota_credito->valor_total = $total_documento;
        $nota_credito->remision_doc_encabezado_id = $remision_doc_encabezado_id;
        $nota_credito->save();
        
        // Un solo registro (CR)
        $nota_credito_service->contabilizar_movimiento_credito( $datos, $total_documento, $datos['descripcion'], $factura );

        // Actualizar registro del pago de la factura a la que afecta la nota
        if ($factura->forma_pago == 'credito') {
            $nota_credito_service->actualizar_registro_pago( $total_documento, $factura, $nota_credito, 'crear' );
        }else {
            $nota_credito_service->actualizar_movimiento_tesoreria( $total_documento, $factura, $nota_credito, 'crear' ); 
        } 

        return true;
    }

    public function enviar( $id )
    {
        $ruta_show = 'fe_nota_credito/'.$id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion');

        $encabezado_nota_credito = NotaCredito::find( $id );
        $factura = Factura::find( $encabezado_nota_credito->ventas_doc_relacionado_id );

        $mensaje = $this->enviar_nota_credito_electronica( $id, $factura );
        
        $documento_electronico = new FacturaGeneral( $encabezado_nota_credito, 'nota_credito' );

        $json_dataico = $documento_electronico->get_einvoice_in_dataico();
        
        $errores_einvoice =  $documento_electronico->get_errores($json_dataico);

        if ( $errores_einvoice != '' ) {
            return redirect( $ruta_show )->with( 'mensaje_error', 'Documento no pudo ser enviado. <br> Presenta inconsistencias: ' . $errores_einvoice);
        }     
        
        if (isset($json_dataico->credit_note)) {
            if ($json_dataico->credit_note->dian_status != 'DIAN_RECHAZADO') {
                // La factura ya está en DATAICO, pero no se reflejó en Appsiel
                
                $msj_primera_parte = 'Documento ya fue enviado correctamente hacia el proveedor tecnológico.';
                $mensaje = (object)[
                    'tipo'=>'flash_message',
                    'contenido' => '<h3>' . $msj_primera_parte . '</h3>'
                ];

                if ($json_dataico->credit_note->dian_status == 'DIAN_NO_ENVIADO') {
                    $mensaje = (object)[
                        'tipo'=>'mensaje_error',
                        'contenido' => '<h3>' . $msj_primera_parte . '<br> Sin embargo NO fue enviado hacia la DIAN.</h3>'
                    ];
                }else{
                    $encabezado_nota_credito->estado = 'Enviada';
                    $encabezado_nota_credito->save();
                }
    
                return redirect( $ruta_show )->with( $mensaje->tipo, $mensaje->contenido);
            }
        }

    	return redirect( $ruta_show )->with( $mensaje->tipo, $mensaje->contenido);
    }
}
