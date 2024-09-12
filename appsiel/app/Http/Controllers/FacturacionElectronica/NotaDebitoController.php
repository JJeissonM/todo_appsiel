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
use App\Inventarios\RemisionVentas;
use App\Inventarios\InvProducto;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Ventas\Cliente;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;

use App\FacturacionElectronica\TFHKA\DocumentoElectronico;
use App\FacturacionElectronica\TFHKA\DocumentoReferenciado;
use App\FacturacionElectronica\ResultadoEnvioDocumento;
use App\FacturacionElectronica\ResultadoEnvio;

use App\FacturacionElectronica\NotaDebito;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class NotaDebitoController extends TransaccionController
{
    protected $documento_nota_debito;

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
            return redirect( 'web?id=' . $fe_app_id . '&id_modelo=' . $fe_factura_modelo_id . '')->with('mensaje_error','No puede hacer notas débito desde esta opción. Debe ir al Botón Crear Nota débito directa');
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

        return view( 'ventas.notas_debito.create', compact('form_create','id_transaccion','miga_pan','tabla','doc_encabezado') );
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

        // Datos de Notas débito aplicadas a la factura
        $notas_debito = NotaDebito::get_notas_aplicadas_factura( $doc_encabezado->id );

        $documento_vista = '';

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        //$url_crear = $this->modelo->url_crear.$this->variables_url;
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        $acciones = $this->acciones_basicas_modelo( $this->modelo, $variables_url );

        $url_crear = $acciones->create;

        return view( 'facturacion_electronica.notas_debito.show', compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_debito','medios_pago') );

    }

    public function store( Request $request )
    {
        // WARNING: si la factura tiene varios documentos de remision, se toma el primero

        $datos = $request->all();
        $datos['creado_por'] = Auth::user()->email;
        $factura = VtasDocEncabezado::get_registro_impresion( $request->ventas_doc_relacionado_id ); 

        // Paso 1: Crear remisión para los datos devueltos
        $remision_factura_id = explode(',',$factura->remision_doc_encabezado_id)[0];
        $datos['lineas_registros'] = json_encode( $this->obtener_lineas_registros_con_base_remision( $datos, $remision_factura_id ) ); // Se necesita en tipo string
        $datos['core_empresa_id'] = $factura->core_empresa_id;
        $datos['inv_bodega_id'] = $this->get_bodega_para_remision( $remision_factura_id );
        $remision = new RemisionVentas;
        $documento_remision = $remision->crear_nueva( $datos );

        // Paso 2
        $datos['remision_doc_encabezado_id'] = $documento_remision->id;
        $datos['ventas_doc_relacionado_id'] = $factura->id; // Relacionar Nota con la Factura  
        $datos['forma_pago'] = $factura->forma_pago; // Relacionar Nota con la Factura   
        $datos['vendedor_id'] = $factura->vendedor_id;    
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $encabezado_nota_debito = $encabezado_documento->crear_nuevo( $datos );


        // Paso 3 ( este falta refactorizar: separar la creación de lineas de registros de la contabilización y de otras transacciones )
        NotaDebitoController::crear_registros_nota_debito( $request, $encabezado_nota_debito, $factura );

        // Paso 4 (Se está haciendo en el Paso 3)
        //$this->contabilizar( $encabezado_documento );

        // Paso 5: Enviar factura electrónica
        if ( empty( $encabezado_nota_debito->tipo_documento_app->resolucion_facturacion->toArray() ) )
        {
            $encabezado_nota_debito->estado = 'Sin enviar';
            $encabezado_nota_debito->save();

            return redirect( 'fe_nota_debito/'.$encabezado_nota_debito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error', 'El documento no tiene resolución asociada. Por tanto no pudo ser enviado.');
        }

        // Paso 5: Enviar factura electrónica
        $resultado_original = $this->procesar_envio_factura( $encabezado_nota_debito );

        // Paso 6: Almacenar resultado en base de datos para Auditoria
        $obj_resultado = new ResultadoEnvio;
        $mensaje = $obj_resultado->almacenar_resultado( $resultado_original, $this->documento_nota_debito, $encabezado_nota_debito->id );

        if ( $mensaje->tipo == 'mensaje_error' )
        {
            $encabezado_nota_debito->estado = 'Sin enviar';
            
        }else{
            $encabezado_nota_debito->estado = 'Enviada';
        }
        $encabezado_nota_debito->save();

        return redirect( 'fe_nota_debito/'.$encabezado_nota_debito->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion)->with( $mensaje->tipo, $mensaje->contenido);

    }

    public function procesar_envio_factura( $encabezado_nota_debito, $adjuntos = 0 )
    {
        // Paso 1: Prepara documento electronico
        $documento = new DocumentoElectronico();
        $this->documento_nota_debito = $documento->preparar_objeto_documento( $encabezado_nota_debito );
        $this->documento_nota_debito->tipoOperacion = "30"; // Para ND
        $this->documento_nota_debito->tipoDocumento = "92"; // Nota débito

        $datos_factura_electronica = ResultadoEnvioDocumento::where( 'vtas_doc_encabezado_id', $encabezado_nota_debito->ventas_doc_relacionado_id )->get()->first();

        $DocRef = new DocumentoReferenciado();

            $DocRef->codigoEstatusDocumento = '2';
            $DocRef->codigoInterno = '4';
            $DocRef->cufeDocReferenciado = $datos_factura_electronica->cufe;
            $DocRef->tipoCUFE = $datos_factura_electronica->tipoCufe;
            $DocRef->tipoDocumento = $datos_factura_electronica->tipoDocumento;
            $DocRef->descripcion[0] = "Nota débito por devolución/anulación de factura";
            $DocRef->numeroDocumento= $datos_factura_electronica->consecutivoDocumento;
        
        $this->documento_nota_debito->documentosReferenciados[0] =$DocRef;

        $DocRef1 = new DocumentoReferenciado();

            $DocRef1->codigoInterno = '5';
            $DocRef1->cufeDocReferenciado = $datos_factura_electronica->cufe;
            $DocRef1->fecha = explode( " ", $datos_factura_electronica->fechaAceptacionDIAN )[0];
            $DocRef->tipoCUFE = $datos_factura_electronica->tipoCufe;
            $DocRef->tipoDocumento = $datos_factura_electronica->tipoDocumento;
            $DocRef1->numeroDocumento= $datos_factura_electronica->consecutivoDocumento;

        $this->documento_nota_debito->documentosReferenciados[1] =$DocRef1;

        // Paso 2: Preparar parámetros para envío
        $params = array(
                         'tokenEmpresa' =>  config('facturacion_electronica.tokenEmpresa'),
                         'tokenPassword' => config('facturacion_electronica.tokenPassword'),
                         'factura' => $this->documento_nota_debito,
                         'adjuntos' => $adjuntos 
                        );

        // Paso 3: Enviar Objeto Documento Electrónico
        $resultado_original = $documento->WebService->enviar( config('facturacion_electronica.WSDL'), $documento->options, $params );

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
                $mensaje->contenido = '<h3>Nota débito enviada correctamente hacia el proveedor tecnológico</h3>';
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



    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public static function crear_registros_nota_debito( Request $request, $nota_debito, $factura )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        // Se crean los registro con base en el documento de inventario ya creado
        // lineas_registros solo tiene el ID del documentos de inventario
        // remision_doc_encabezado_id es el ID de una devolución en ventas
        $lineas_registros = [(object)[ 'id_doc' => $nota_debito->remision_doc_encabezado_id ]];

        NotaDebitoController::crear_lineas_registros_ventas( $datos, $nota_debito, $lineas_registros, $factura );

        return true;
    }


    // Se crean los registros con base en los registros de la devolución
    public static function crear_lineas_registros_ventas( $datos, $nota_debito, $lineas_registros, $factura )
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
                $cantidad = $un_registro->cantidad; // Fue una entrada de inventarios, se vuelve la cantidad negativa, porque es una diminución de las ventas

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
                                        [ 'vtas_doc_encabezado_id' => $nota_debito->id ] +
                                        $linea_datos
                                    );

                $datos['consecutivo'] = $nota_debito->consecutivo;
                VtasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // Contabilizar
                $detalle_operacion = $datos['descripcion'];

                // Reversar ingresos e impuestos
                NotaDebitoController::contabilizar_movimiento_credito( $datos + $linea_datos, $detalle_operacion );

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

        $nota_debito->valor_total = $total_documento;
        $nota_debito->remision_doc_encabezado_id = $remision_doc_encabezado_id;
        $nota_debito->save();

        // Cartera ó Caja (DB)
        NotaDebitoController::contabilizar_movimiento_debito( $nota_debito->forma_pago, $datos, $total_documento, '' );

        // Actualizar registro del pago de la factura a la que afecta la nota
        NotaDebitoController::crear_registro_pago( $nota_debito->forma_pago, $datos, $total_documento, 'Nota débito' );

        return true;
    }

    public static function contabilizar_movimiento_debito( $forma_pago, $datos, $total_documento, $detalle_operacion, $caja_banco_id = null )
    {
        /*
            WARNING. Esto debe ser un parámetro de la configuración. Si se quiere llevar la factura contado a la caja directamente o si se causa una cuenta por cobrar
        */
        
        if ( $forma_pago == 'credito')
        {
            // Se resetean estos campos del registro
            $datos['inv_producto_id'] = 0;
            $datos['cantidad '] = 0;
            $datos['tasa_impuesto'] = 0;
            $datos['base_impuesto'] = 0;
            $datos['valor_impuesto'] = 0;
            $datos['inv_bodega_id'] = 0;

            // La cuenta de CARTERA se toma de la clase del cliente
            $cta_x_cobrar_id = Cliente::get_cuenta_cartera( $datos['cliente_id'] );
            ContabilidadController::contabilizar_registro2( $datos, $cta_x_cobrar_id, $detalle_operacion, $total_documento, 0);
        }
        
        // Agregar el movimiento a tesorería
        if ( $forma_pago == 'contado')
        {
            if( is_null( $caja_banco_id ) )
            {
                if ( empty( $datos['registros_medio_pago'] ) )
                {   
                    // Por defecto
                    $caja = TesoCaja::get()->first();
                    $teso_caja_id = $caja->id;
                    $teso_cuenta_bancaria_id = 0;
                    $contab_cuenta_id = $caja->contab_cuenta_id;
                }else{

                    // WARNING!!! Por ahora solo se está aceptando un solo medio de pago
                    $contab_cuenta_id = TesoCaja::find( 1 )->contab_cuenta_id;

                    $teso_caja_id = $datos['registros_medio_pago']['teso_caja_id'];
                    if ($teso_caja_id != 0)
                    {
                        $contab_cuenta_id = TesoCaja::find( $teso_caja_id )->contab_cuenta_id;
                    }

                    $teso_cuenta_bancaria_id = $datos['registros_medio_pago']['teso_cuenta_bancaria_id'];
                    if ($teso_cuenta_bancaria_id != 0)
                    {
                        $contab_cuenta_id = TesoCuentaBancaria::find( $teso_cuenta_bancaria_id )->contab_cuenta_id;
                    }

                    $total_documento = $datos['registros_medio_pago']['valor_recaudo'];
                    
                }
            }else{
                // $caja_banco_id se manda desde Ventas POS
                $caja = TesoCaja::find( $caja_banco_id );
                $teso_caja_id = $caja->id;
                $teso_cuenta_bancaria_id = 0;
                $contab_cuenta_id = $caja->contab_cuenta_id;
            }
            
            ContabilidadController::contabilizar_registro2( $datos, $contab_cuenta_id, $detalle_operacion, $total_documento, 0, $teso_caja_id, $teso_cuenta_bancaria_id);
        }
    }

    public static function contabilizar_movimiento_credito( $una_linea_registro, $detalle_operacion )
    {    
        // IVA generado (CR)
        // Si se ha liquidado impuestos en la transacción
        $valor_total_impuesto = 0;
        if ( $una_linea_registro['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_ventas( $una_linea_registro['inv_producto_id'] );
            $valor_total_impuesto = abs( $una_linea_registro['valor_impuesto'] * $una_linea_registro['cantidad'] );

            ContabilidadController::contabilizar_registro2( $una_linea_registro, $cta_impuesto_ventas_id, $detalle_operacion, 0, abs($valor_total_impuesto) );
        }

        // Contabilizar Ingresos (CR)
        // La cuenta de ingresos se toma del grupo de inventarios
        $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $una_linea_registro['inv_producto_id'] );
        ContabilidadController::contabilizar_registro2( $una_linea_registro, $cta_ingresos_id, $detalle_operacion, 0, $una_linea_registro['base_impuesto_total']);
    }

    public static function crear_registro_pago( $forma_pago, $datos, $total_documento, $detalle_operacion )
    {        
        // Cargar la cuenta por cobrar (CxC)
        if ( $forma_pago == 'credito')
        {
            $datos['modelo_referencia_tercero_index'] = 'App\Ventas\Cliente';
            $datos['referencia_tercero_id'] = $datos['cliente_id'];
            $datos['valor_documento'] = $total_documento;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $total_documento;
            $datos['estado'] = 'Pendiente';
            CxcMovimiento::create( $datos );
        }

        if ( $forma_pago == 'contado')
        {
            $teso_movimiento = new TesoMovimiento();
            $teso_movimiento->almacenar_registro_pago_contado( $datos, $datos['registros_medio_pago'], 'entrada', $total_documento );
        }
    }

    public function get_bodega_para_remision( $encabezado_remision_id )
    {
        return InvDocRegistro::where( 'inv_doc_encabezado_id', $encabezado_remision_id )->get()->first()->inv_bodega_id;
    }

    public function obtener_lineas_registros_con_base_remision( $datos, $encabezado_remision_id )
    {
        $lineas_registros = [];

        // Obtener registros de la remisión de la factura de ventas
        // Se harán la devoluciones a cada línea de estos registros (si se le ingresó cantidad a devolver)
        $registros_rm = InvDocRegistro::where( 'inv_doc_encabezado_id', $encabezado_remision_id )->get();
        
        $l = 0; // Contador para las lineas a devolver
        $regs = 0; // Contador para los registro de la remisión, es la misma cantidad de registros enviados en $datos[]
        foreach ($registros_rm as $linea)
        {
            $cantidad_devolver = (float)$datos['cantidad_devolver'][$regs];
            
            if ( $cantidad_devolver > 0)
            {

                $linea_devolucion = $linea->toArray();
                $linea_devolucion['cantidad'] = $cantidad_devolver * -1; // salida de inventarios
                $linea_devolucion['inv_motivo_id'] = (int)explode('-', $datos['motivos_ids'][$l])[0]; // El input del formulario trae los motivos en formato ID-descripcion, se toma solo el ID
                $linea_devolucion['costo_total'] = $cantidad_devolver * $linea['costo_unitario'] * -1;

                $lineas_registros[$l] = (object)( $linea_devolucion );

                $l++;
            }
            $regs++;  
        }
        return $lineas_registros;
    }

    public function enviar( $id )
    {
        $encabezado_nota_debito = NotaDebito::find( $id );

        $resultado_original = $this->procesar_envio_factura( $encabezado_nota_debito );

        // Almacenar resultado en base de datos para Auditoria
        $obj_resultado = new ResultadoEnvio;
        $mensaje = $obj_resultado->almacenar_resultado( $resultado_original, $this->documento_nota_debito, $encabezado_nota_debito->id );

        if ( $mensaje->tipo != 'mensaje_error' )
        {
            $encabezado_nota_debito->estado = 'Enviada';
            $encabezado_nota_debito->save();
        }

        return redirect( 'fe_nota_debito/'.$encabezado_nota_debito->id.'?id=' . Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with( $mensaje->tipo, $mensaje->contenido);
    }
}