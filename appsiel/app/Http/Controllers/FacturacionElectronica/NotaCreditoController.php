<?php

namespace App\Http\Controllers\FacturacionElectronica;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Controllers\Ventas\VentaController;
use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Contabilidad\ContabilidadController;

use Auth;
use View;
use Input;

use App\Core\EncabezadoDocumentoTransaccion;

use App\Sistema\Html\BotonesAnteriorSiguiente;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\DevolucionVentas;
use App\Inventarios\InvProducto;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Ventas\Cliente;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoMovimiento;

use App\FacturacionElectronica\TFHKA\DocumentoElectronico;
use App\FacturacionElectronica\TFHKA\DocumentoReferenciado;
use App\FacturacionElectronica\ResultadoEnvioDocumento;
use App\FacturacionElectronica\ResultadoEnvio;
use App\FacturacionElectronica\Factura;
use App\FacturacionElectronica\NotaCredito;

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

        $saldo_pendiente = 0;
        $vec_saldos = [0,0,0];

        if ( is_null( Input::get('factura_id') ) )
        {
            return redirect( 'web?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id . '')->with('mensaje_error','No puede hacer notas crédito desde esta opción. Debe ir al Botón Crear Nota crédito directa');
        }

        $factura = VtasDocEncabezado::get_registro_impresion( Input::get('factura_id') );

        $this->movimiento_cxc = CxcMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                            ->where('consecutivo', $factura->consecutivo)
                            ->get()
                            ->first();

        if ( is_null( $this->movimiento_cxc ) )
        {
            return redirect('fe_factura/'.$factura->id.'?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id . '&id_transaccion=' . $fe_factura_transaccion_id)->with('mensaje_error','La factura no tiene registros de cuentas por cobrar');
        }

        if ( $this->movimiento_cxc->saldo_pendiente == 0 && $factura->forma_pago == 'credito' )
        {
            return redirect('fe_factura/'.$factura->id.'?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id . '&id_transaccion=' . $fe_factura_transaccion_id)->with('mensaje_error','La factura no tiene SALDO PENDIENTE por cobrar');
        }
        
        $vec_saldos = [$this->movimiento_cxc->valor_documento, $this->movimiento_cxc->valor_pagado, $this->movimiento_cxc->saldo_pendiente];

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

        return view( 'facturacion_electronica.notas_credito.show', compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito','medios_pago') );

    }

    public function store( Request $request )
    {
    	// WARNING: si la factura tiene varias entradas, no se puede hacer la nota

    	$datos = $request->all();
    	$factura = VtasDocEncabezado::get_registro_impresion( $request->ventas_doc_relacionado_id ); 

    	// Paso 1
    	$devolucion = new DevolucionVentas;
    	$documento_devolucion = $devolucion->crear_nueva( $datos, $factura->remision_doc_encabezado_id );

    	// Paso 2
    	$datos['creado_por'] = Auth::user()->email;
        $datos['remision_doc_encabezado_id'] = $documento_devolucion->id;
        $datos['ventas_doc_relacionado_id'] = $factura->id; // Relacionar Nota con la Factura       
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $encabezado_nota_credito = $encabezado_documento->crear_nuevo( $datos );

        // Paso 3 ( este falta refactorizar: separar la creación de lineas de registros de la contabilización y de otras transacciones )
        NotaCreditoController::crear_registros_nota_credito( $request, $encabezado_nota_credito, $factura );

        // Paso 4 (Se está haciendo en el Paso 3)
        //$this->contabilizar( $encabezado_documento );

        // Paso 5: Enviar factura electrónica
        if ( empty( $encabezado_nota_credito->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
        	$encabezado_nota_credito->estado = 'Sin enviar';
        	$encabezado_nota_credito->save();

        	return redirect( 'fe_nota_credito/'.$encabezado_nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error', 'El documento no tiene resolución asociada. Por tanto no pudo ser enviado.');
        }

        // Paso 5: Enviar factura electrónica
        $resultado_original = $this->procesar_envio_factura( $encabezado_nota_credito );

        // Paso 6: Almacenar resultado en base de datos para Auditoria
        $obj_resultado = new ResultadoEnvio;
        $mensaje = $obj_resultado->almacenar_resultado( $resultado_original, $this->documento_nota_credito, $encabezado_nota_credito->id );

        if ( $mensaje->tipo == 'mensaje_error' )
        {
        	$encabezado_nota_credito->estado = 'Sin enviar';
        	
        }else{
        	$encabezado_nota_credito->estado = 'Enviada';
        }
        $encabezado_nota_credito->save();

    	return redirect( 'fe_nota_credito/'.$encabezado_nota_credito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion)->with( $mensaje->tipo, $mensaje->contenido);

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
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        // Se crean los registro con base en el documento de inventario ya creado
        // lineas_registros solo tiene el ID del documentos de inventario
        // remision_doc_encabezado_id es el ID de una devolución en ventas
        $lineas_registros = [(object)[ 'id_doc' => $nota_credito->remision_doc_encabezado_id ]];

        //dd( $lineas_registros );

        NotaCreditoController::crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros, $factura );

        return true;
    }


    // Se crean los registros con base en los registros de la devolución
    public static function crear_lineas_registros_ventas( $datos, $nota_credito, $lineas_registros, $factura )
    {
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

                $precio_total = $precio_unitario * $cantidad;

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
                NotaCreditoController::contabilizar_movimiento_debito( $datos + $linea_datos, $detalle_operacion );

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
        
        // Un solo registro para reversar la cuenta por cobrar (CR)
        NotaCreditoController::contabilizar_movimiento_credito( $datos, $total_documento, $datos['descripcion'], $factura );

        // Actualizar registro del pago de la factura a la que afecta la nota
        NotaCreditoController::actualizar_registro_pago( $total_documento, $factura, $nota_credito, 'crear' ); 

        return true;
    }

    public static function contabilizar_movimiento_debito( $datos, $detalle_operacion )
    {
        // IVA descontable (DB)
        // Si se ha liquidado impuestos en la transacción
        if ( isset( $datos['tasa_impuesto'] ) && $datos['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_devolucion_ventas( $datos['inv_producto_id'] );
            ContabilidadController::contabilizar_registro2( $datos, $cta_impuesto_ventas_id, $detalle_operacion, abs( $datos['valor_impuesto'] ), 0);
        }

        // La cuenta de ingresos se toma del grupo de inventarios
        $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $datos['inv_producto_id'] );
        ContabilidadController::contabilizar_registro2( $datos, $cta_ingresos_id, $detalle_operacion, $datos['base_impuesto_total'], 0);
    }

    public static function contabilizar_movimiento_credito( $datos, $total_documento, $detalle_operacion, $factura = null )
    {
        /*
            Se crea un SOLO registro contable de la cuenta por cobrar (Crédito)
        */
            
        // Se resetean estos campos del registro
        $datos['inv_producto_id'] = 0;
        $datos['cantidad '] = 0;
        $datos['tasa_impuesto'] = 0;
        $datos['base_impuesto'] = 0;
        $datos['valor_impuesto'] = 0;
        $datos['inv_bodega_id'] = 0;

        if ( is_null($factura) )
        {
            $cxc_id = Cliente::get_cuenta_cartera( $datos['cliente_id'] );
        }else{
            $cxc_id = Cliente::get_cuenta_cartera( $factura->cliente_id );
        }
        
        ContabilidadController::contabilizar_registro2( $datos, $cxc_id, $detalle_operacion, 0, abs($total_documento) );
    }

    public static function actualizar_registro_pago( $total_nota, $factura, $nota, $accion )
    {
        /*
            Al crear la nota: Se disminuye el saldo pendiente y se aumenta el valor pagado
            A anular la nota: Se aumenta el saldo pendiente y se disminuye el valor pagado
        */

        // total_nota es negativo cuando se hace la nota y positivo cuando se anula

        $movimiento_cxc = CxcMovimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
                                ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
                                ->where('consecutivo', $factura->consecutivo)
                                ->get()
                                ->first();

        $nuevo_total_pendiente = $movimiento_cxc->saldo_pendiente + $total_nota; 
        $nuevo_total_pagado = $movimiento_cxc->valor_pagado - $total_nota;

        $estado = 'Pendiente';
        if ( $nuevo_total_pendiente == 0)
        {
            $estado = 'Pagado';
        }

        $movimiento_cxc->update( [ 
                                    'valor_pagado' => $nuevo_total_pagado,
                                    'saldo_pendiente' => $nuevo_total_pendiente,
                                    'estado' => $estado
                                ] );

        $datos = ['core_tipo_transaccion_id' => $nota->core_tipo_transaccion_id]+
                  ['core_tipo_doc_app_id' => $nota->core_tipo_doc_app_id]+
                  ['consecutivo' => $nota->consecutivo]+
                  ['fecha' => $nota->fecha]+
                  ['core_empresa_id' => $nota->core_empresa_id]+
                  ['core_tercero_id' => $nota->core_tercero_id]+
                  ['modelo_referencia_tercero_index' => 'App\Ventas\Cliente']+
                  ['referencia_tercero_id' => $factura->cliente_id]+
                  ['doc_cxc_transacc_id' => $factura->core_tipo_transaccion_id]+
                  ['doc_cxc_tipo_doc_id' => $factura->core_tipo_doc_app_id]+
                  ['doc_cxc_consecutivo' => $factura->consecutivo]+
                  ['doc_cruce_transacc_id' => 0]+
                  ['doc_cruce_tipo_doc_id' => 0]+
                  ['doc_cruce_consecutivo' => 0]+
                  ['abono' => abs($total_nota)]+
                  ['creado_por' => $nota->creado_por];

        if ( $accion == 'crear')
        {
            // Almacenar registro de abono
            CxcAbono::create( $datos );
        }else{
            // Eliminar registro de abono
            CxcAbono::where( $datos )->delete();
        }
    }


    public function enviar( $id )
    {
    	$encabezado_nota_credito = NotaCredito::find( $id );

        $resultado_original = $this->procesar_envio_factura( $encabezado_nota_credito );

        // Almacenar resultado en base de datos para Auditoria
        $obj_resultado = new ResultadoEnvio;
        $mensaje = $obj_resultado->almacenar_resultado( $resultado_original, $this->documento_nota_credito, $encabezado_nota_credito->id );

        if ( $mensaje->tipo != 'mensaje_error' )
        {
        	$encabezado_nota_credito->estado = 'Enviada';
        	$encabezado_nota_credito->save();
        }

    	return redirect( 'fe_nota_credito/'.$encabezado_nota_credito->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
    }
}
