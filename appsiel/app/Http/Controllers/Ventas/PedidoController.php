<?php

namespace App\Http\Controllers\Ventas;

use App\Ventas\ListaPrecioDetalle;
use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Core\Empresa;
use App\Sistema\TipoTransaccion;
use App\Core\Tercero;
use App\Http\Controllers\Inventarios\InventarioController;
use App\Ventas\Cliente;
use App\Ventas\VtasTransaccion;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;

use App\Contabilidad\Impuesto;
use App\Http\Controllers\Tesoreria\RecaudoController;
use App\Sistema\Modelo;
use App\Tesoreria\TesoMotivo;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\Services\DocumentsLinesServices;
use App\Ventas\Services\OrderServices;
use App\Ventas\VtasPedido;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class PedidoController extends TransaccionController
{
    protected $doc_encabezado;

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de ventas
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        if( is_null($this->transaccion) )
        {
            $this->transaccion = TipoTransaccion::find(42);
        }

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(VtasTransaccion::get_datos_tabla_ingreso_lineas_registros($this->transaccion, $motivos));

        return $this->crear($this->app, $this->modelo, $this->transaccion, 'ventas.pedidos.create', $tabla);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if( isset( $request['pedido_web'] ) )
        {
            if(!Auth::check()){
                return response()->json([
                    'status' => 'error',
                    'mensaje' => 'Debe estar logueado para poder realizar el pedido.'
                ]);
            }

            $request = $this->completar_request( $request );
        }

        $lineas_registros = json_decode($request->lineas_registros);
        $request['estado'] = "Pendiente";

        if (!isset($request['vendedor_id'])) {
            
            $request['vendedor_id'] = config('ventas.vendedor_id');

            $cliente = Cliente::find($request['cliente_id']);
            if ($cliente != null) {
                $request['vendedor_id'] = $cliente->vendedor_id;
            }
        }

        // 2do. Crear documento de Ventas
        $ventas_doc_encabezado_id = PedidoController::crear_documento($request, $lineas_registros, $request->url_id_modelo);

        if( isset($request['pedido_web']) )
        {
            //self::enviar_pedidoweb_email($ventas_doc_encabezado_id);                     

            return  response()->json([
                'pedidowebid' => $ventas_doc_encabezado_id,
                'status' => 'ok',
                'mensaje' => 'Pedido recibido correctamente, pronto uno de nuestros asesores te estará contactando para proceder con el envío.'
            ]);
        }else{
            return redirect('vtas_pedidos/' . $ventas_doc_encabezado_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->set_variables_globales();

        $general = new ModeloController();
        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);
        $registros = VtasDocRegistro::get_registros_impresion($registro->id);

        $lista_campos = $general->get_campos_modelo($modelo, $registro, 'edit');

        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion, $tipo_transaccion, $lista_campos, $cantidad_campos, 'edit', null);

        $tercero = Tercero::find($registro->core_tercero_id);
        $registro->cliente_input = $tercero->apellido1 . " " . $tercero->apellido2 . " " . $tercero->nombre1 . " " . $tercero->otros_nombres;

        $registro->inv_bodega_id = 1;
        
        $url_action = 'web/'.$id.$this->variables_url;
        if ($modelo->url_form_create != '') {
            $url_action = $modelo->url_form_create.'/'.$id.$this->variables_url;
        }

        $form_create = [
            'url' => $url_action,
            'campos' => $lista_campos
        ];

        $body = View::make('ventas.incluir.lineas_registros_edit', compact('registro', 'registros'))->render();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        $miga_pan = [
            ['url' => 'ventas?id=' . Input::get('id'), 'etiqueta' => 'Ventas'],
            ['url' => 'NO', 'etiqueta' => $tipo_transaccion->descripcion]
        ];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(VtasTransaccion::get_datos_tabla_ingreso_lineas_registros($tipo_transaccion, $motivos, $body));

        $cliente = $registro->cliente;

        return view('ventas.pedidos.edit', compact('form_create', 'id_transaccion', 'miga_pan', 'tabla', 'registro', 'registros', 'cliente'));
    }

    //     A L M A C E N A R  LA MODIFICACION DE UN REGISTRO
    public function update(Request $request, $id)
    {
        $datos = $request->all();
        $modelo = Modelo::find( $request->url_id_modelo );

        $registro_encabezado_doc = app( $modelo->name_space )->find($id);

        // Borrar registros viejos del documento
        VtasDocRegistro::where( 'vtas_doc_encabezado_id', $id )->delete();

        $request['core_tipo_transaccion_id'] = $registro_encabezado_doc->core_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $registro_encabezado_doc->core_tipo_doc_app_id;
        $request['consecutivo'] = $registro_encabezado_doc->consecutivo;

        $lineas_registros = json_decode($request->lineas_registros);
        
        (new DocumentsLinesServices())->crear_registros_documento($request, $registro_encabezado_doc, $lineas_registros);

        $registro_encabezado_doc->fill( $datos );
        $registro_encabezado_doc->save();

        return redirect( 'vtas_pedidos/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }
    
    // Desde la Web
    public function completar_request( $request )
    {
        $tercero = DB::select('select id from core_terceros where user_id = ?', [Auth::user()->id]);
        $cliente = DB::select('select id from vtas_clientes where core_tercero_id = ?',[$tercero[0]->id]);
        $request['core_tipo_transaccion_id'] = config( 'ventas.pv_tipo_transaccion_id' );
        $request['core_tipo_doc_app_id'] = config( 'ventas.pv_tipo_doc_app_id' );
        $request['core_empresa_id'] = Empresa::find(1)->id;
        $request['fecha'] = date('Y-m-d');
        $request['cliente_input'] = Auth::user()->id;
        $request['descripcion'] = '';
        $request['consecutivo'] = '';
        $request['url_id'] = 13;
        $request['url_id_modelo'] = 175;
        $request['inv_bodega_id_aux'] = '';
        $request['vendedor_id'] = config('ventas.vendedor_id');
        $request['forma_pago'] = 'forma_pago';
        $request['fecha_entrega'] = date('Y-m-d');
        $request['fecha_vencimiento'] = date('Y-m-d',strtotime(date('Y-m-d')."+ 1 days"));
        $request['inv_bodega_id'] = config('ventas.inv_bodega_id');
        $request['zona_id'] = config('ventas.zona_id');
        $request['clase_cliente_id'] = config('ventas.clase_cliente_id');
        $request['equipo_ventas_id'] = config('ventas.equipo_ventas_id');
        $request['core_tercero_id'] = $tercero[0]->id;
        $request['lista_precios_id'] = config('ventas.lista_precios_id');
        $request['lista_descuentos_id'] = config('ventas.lista_descuentos_id');
        $request['liquida_impuestos'] = config('configuracion.liquidacion_impuestos');
        $request['cliente_id'] = $cliente[0]->id;
        $request['dvc_tipo_transaccion_id'] = config('ventas.dvc_tipo_transaccion_id');
        $request['rm_tipo_transaccion_id'] = config('ventas.rm_tipo_transaccion_id');
        $request['tipo_transaccion'] = config('ventas.tipo_transaccion');
        $id_transaccion = Input::get('id_transaccion');
        if( is_null( $id_transaccion ) )
        {
            $id_transaccion = 42;
        }

        $request['id_transaccion'] = $id_transaccion;

        return $request;
    }

    /*
        Crea un documento completo: encabezados y registros
        Devuelve en ID del documento creado
    */
    public function crear_documento(Request $request, array $lineas_registros, $modelo_id)
    {
        $doc_encabezado = $this->crear_encabezado_documento($request, $modelo_id);
        
        (new DocumentsLinesServices())->crear_registros_documento($request, $doc_encabezado, $lineas_registros);
        
        return $doc_encabezado->id;
    }

    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public function crear_registros_documento(Request $request, $doc_encabezado, array $lineas_registros)
    {
        $lista_precios_id = $doc_encabezado->cliente->lista_precios_id;
        $lista_descuentos_id = $doc_encabezado->cliente->lista_descuentos_id;

        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            if( !isset($lineas_registros[$i]->inv_motivo_id) )
                $inv_motivo_id = config('pagina_web.pedidos_inv_motivo_id');
            else
                $inv_motivo_id = $lineas_registros[$i]->inv_motivo_id;

            // Se llama nuevamente el precio de venta para estar SEGURO ( Cuando se hace desde la web )
              $precio_unitario = ListaPrecioDetalle::get_precio_producto( $lista_precios_id, $doc_encabezado->fecha, $lineas_registros[$i]->inv_producto_id );
              $tasa_descuento = ListaDctoDetalle::get_descuento_producto( $lista_descuentos_id, $doc_encabezado->fecha, $lineas_registros[$i]->inv_producto_id );

              $cantidad = (float)$lineas_registros[$i]->cantidad;
              $valor_unitario_descuento = 0;

              if ( isset( $request->url_id ) && !$request->pedido_web)
              {
                // Si el pedido se hace desde el modulo de Ventas o POS
                if ( (int)$request->url_id == 13 || (int)$request->url_id == 20 ) 
                {
                    $tasa_descuento = (float)$lineas_registros[$i]->tasa_descuento;
                    $precio_unitario = (float)$lineas_registros[$i]->precio_unitario;
                    $valor_unitario_descuento = $precio_unitario * ( $tasa_descuento / 100 );
                }
              }

              $precio_venta_unitario = $precio_unitario - $valor_unitario_descuento;
              
              $tasa_impuesto = Impuesto::get_tasa($lineas_registros[$i]->inv_producto_id,0,$doc_encabezado->cliente_id);

              $base_impuesto = $precio_venta_unitario / ( 1 + $tasa_impuesto / 100 );
              $valor_impuesto = $precio_venta_unitario - $base_impuesto;

              $precio_total = $precio_venta_unitario *  $cantidad;

              $linea_datos = ['vtas_motivo_id' =>$inv_motivo_id] +
                          ['inv_producto_id' => $lineas_registros[$i]->inv_producto_id] +
                          ['precio_unitario' => $precio_unitario] +
                          ['cantidad' =>  $cantidad ] +
                          ['cantidad_pendiente' =>  $cantidad ] +
                          ['precio_total' => $precio_total ] +
                          ['base_impuesto' => $base_impuesto] +
                          ['tasa_impuesto' => $tasa_impuesto] +
                          ['valor_impuesto' => $valor_impuesto] +
                          ['base_impuesto_total' => $base_impuesto *  $cantidad ] +
                          [ 'tasa_descuento' => $tasa_descuento ] +
                          [ 'valor_total_descuento' => $valor_unitario_descuento *  $cantidad ] +
                          ['creado_por' => Auth::user()->email] +
                          ['estado' => 'Activo'];

            VtasDocRegistro::create(
                                        ['vtas_doc_encabezado_id' => $doc_encabezado->id] +
                                        $linea_datos
                                    );

            $total_documento += $precio_total;

        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();
    }

    /**
     * Mostrar las EXISTENCIAS de una bodega ($id).
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->set_variables_globales();
        $this->doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);
        $doc_encabezado = $this->doc_encabezado;
        
        $aditional_wheres = [];
        if ( Auth::user()->hasRole('Vendedor') ) {
            $aditional_wheres = [
                ['vendedor_id', '=' , $doc_encabezado->vendedor_id]
            ];
        }

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id, $aditional_wheres);
        
        
        $doc_registros = VtasDocRegistro::get_registros_impresion($this->doc_encabezado->id);

        $this->empresa = Empresa::find($this->doc_encabezado->core_empresa_id);
        
        $resolucion = '';
        $empresa = $this->empresa;
        //$documento_vista = $this->generar_documento_vista($id, 'documento_vista');
        $documento_vista = "";
        $id_transaccion = $this->transaccion->id;
        $registros_contabilidad = [];
        $cliente = Cliente::find($doc_encabezado->cliente_id);
        $miga_pan = [
            ['url' => 'ventas?id=' . Input::get('id'), 'etiqueta' => 'Ventas'],
            ['url' => 'web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'), 'etiqueta' => $this->modelo->descripcion],
            ['url' => 'NO', 'etiqueta' => $this->doc_encabezado->documento_transaccion_prefijo_consecutivo]
        ];

        //$id_transaccion = 8; // 8 = Recaudo cartera
        $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion('recaudo-cartera');
        $medios_recaudo = RecaudoController::get_medios_recaudo();
        $cajas = RecaudoController::get_cajas();
        $cuentas_bancarias = RecaudoController::get_cuentas_bancarias();

        return view('ventas.pedidos.show', compact('id', 'cliente', 'doc_registros', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan', 'doc_encabezado', 'registros_contabilidad', 'empresa', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias'));
    }

    /*
        Imprimir
    */
    public function imprimir($id)
    {
        $documento_vista = $this->generar_documento_vista($id, 'ventas.pedidos.formatos_impresion.'.Input::get('formato_impresion_id') );

        // Se prepara el PDF
        $orientacion = 'portrait';
        $tam_hoja = array(0, 0, 50, 800); //'A4';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($documento_vista); //->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream($this->doc_encabezado->documento_transaccion_descripcion . ' - ' . $this->doc_encabezado->documento_transaccion_prefijo_consecutivo . '.pdf');
    }

    /*
        Enviar por email
    */
    public function enviar_por_email($id)
    {
        $documento_vista = $this->generar_documento_vista($id, 'ventas.pedidos.formatos_impresion.estandar');

        $tercero = Tercero::find($this->doc_encabezado->core_tercero_id);

        $asunto = $this->doc_encabezado->documento_transaccion_descripcion . ' No. ' . $this->doc_encabezado->documento_transaccion_prefijo_consecutivo;

        $cuerpo_mensaje = 'Saludos, <br/> Le hacemos llegar su ' . $asunto;

        $vec = EmailController::enviar_por_email_documento($this->empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $documento_vista);

        return redirect('vtas_pedidos/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') )->with($vec['tipo_mensaje'], $vec['texto_mensaje']);
    }

    public function enviar_pedidoweb_email($id){

        $documento_vista = $this->generar_documento_vista($id, 'ventas.pedidos.formatos_impresion.estandar');

        $tercero = Tercero::find($this->doc_encabezado->core_tercero_id);

        $asunto = $this->doc_encabezado->documento_transaccion_descripcion . ' No. ' . $this->doc_encabezado->documento_transaccion_prefijo_consecutivo;
        $this->empresa = Empresa::all()->first();
        $descripcion =  $this->empresa->descripcion;
        $cuerpo_mensaje = "Hola <strong>$tercero->nombre1 $tercero->nombre2</strong> </br>"
                          ."Gracias por su compra en <strong> $descripcion </strong> </br>"
                          ."Hemos recibido tu pedido; el cual ha ingresado a un proceso de validación de datos personales e inventario. Una vez finalizada esta verificación se  procederá a realizar el despacho. </br>"
                          ."<strong style='color:red;'>NOTA:</strong>  para los productos pesados el precio puede variar, los detalles de está variación los podra revisar en la factura que le haremos llegar con los productos, Esta observación es valida para los productos que son sometidos a un proceso de medida , donde el proceso de medición no siempre es exacto. ";

        
        $email_interno = 'info@appsiel.com.co';//.substr( url('/'), 7);
        $empresa = Empresa::find( Auth::user()->empresa_id );

        if ( !is_null( $empresa ) )
        {
            //$email_interno = $empresa->email;
        }

        $vec = EmailController::enviar_por_email_documento($this->empresa->descripcion, $tercero->email . ',' . $email_interno, $asunto, $cuerpo_mensaje, $documento_vista);

    }

    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista($id, $ruta_vista)
    {
        $this->doc_encabezado = VtasDocEncabezado::get_registro_impresion($id);

        $doc_registros = VtasDocRegistro::get_registros_impresion($this->doc_encabezado->id);

        $this->empresa = Empresa::find($this->doc_encabezado->core_empresa_id);

        $contacto = (object)[ 'descripcion'=> $this->doc_encabezado->tercero->descripcion, 'telefono1' => $this->doc_encabezado->tercero->telefono1, 'email' => $this->doc_encabezado->tercero->email ];
        if ( $this->doc_encabezado->contacto_cliente_id != 0 )
        {
            $contacto = $this->doc_encabezado->contacto_cliente->tercero;
        }

        $resolucion = '';

        $doc_encabezado = $this->doc_encabezado;
        $empresa = $this->empresa;

        $etiquetas = $this->get_etiquetas();

        return View::make( $ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion','etiquetas','contacto'))->render();
    }

    public function get_etiquetas()
    {
        $parametros = config('ventas');

        $encabezado = '';

        if ($parametros['encabezado_linea_1'] != '')
        {
            $encabezado .= $parametros['encabezado_linea_1'];
        }

        if ($parametros['encabezado_linea_2'] != '')
        {
            $encabezado .= '<br>'.$parametros['encabezado_linea_2'];
        }

        if ($parametros['encabezado_linea_3'] != '')
        {
            $encabezado .= '<br>'.$parametros['encabezado_linea_3'];
        }


        $pie_pagina = '';

        if ($parametros['pie_pagina_linea_1'] != '')
        {
            $pie_pagina .= $parametros['pie_pagina_linea_1'];
        }

        if ($parametros['pie_pagina_linea_2'] != '')
        {
            $pie_pagina .= '<br>'.$parametros['pie_pagina_linea_2'];
        }

        if ($parametros['pie_pagina_linea_3'] != '')
        {
            $pie_pagina .= '<br>'.$parametros['pie_pagina_linea_3'];
        }

        return [ 'encabezado' => $encabezado, 'pie_pagina' => $pie_pagina ];
    }

    /*
        Proceso de eliminar PEDIDO
        Se eliminan los registros de:
            - se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public static function anular_pedido($id)
    {
        $pedido = VtasDocEncabezado::find( $id );
        
        if ( $pedido->documento_ventas_hijo() != null )
        {
            return redirect('vtas_pedidos/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with('flash_message', 'Pedido NO puede ser anulado. Tiene documentos relacionados.');
        }

        (new OrderServices())->cancel_order($pedido);

        return redirect('vtas_pedidos/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion') )->with('flash_message', 'Pedido ANULADO correctamente.');
    }

    //Crea remision a partir del pedido
    public function remision(Request $request)
    {
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('ventas');

        // Modelo del encabezado del documento
        $rm_modelo_id = $parametros['rm_modelo_id'];
        $rm_tipo_transaccion_id = $parametros['rm_tipo_transaccion_id'];
        $rm_tipo_doc_app_id = $parametros['rm_tipo_doc_app_id'];

        $lineas_registros = json_decode($request->lineas_registros);

        // Se crea el documento, se cambia temporalmente el tipo de transacción y el tipo_doc_app
        $tipo_transaccion_id_original = $request['core_tipo_transaccion_id'];
        $core_tipo_doc_app_id_original = $request['core_tipo_doc_app_id'];

        $request['core_tipo_transaccion_id'] = $rm_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $rm_tipo_doc_app_id;
        $request['estado'] = 'Pendiente';
        $request['consecutivo'] = "";
        $hoy = getdate();
        $request['fecha'] = $hoy['year'] . "-" . $hoy['mon'] . "-" . $hoy['mday'];
        $remision_creada_id = InventarioController::crear_documento($request, $lineas_registros, $rm_modelo_id);
        
        $pedido = VtasDocEncabezado::get_registro_impresion($request->id);
        $pedido->remision_doc_encabezado_id = $remision_creada_id;
        $pedido->estado = "Cumplido";
        $pedido->save();
        
        return redirect('inventarios/' . $remision_creada_id . '?id=' . $request->url_id . '&id_modelo=' . $rm_modelo_id . '&id_transaccion=' . $rm_tipo_transaccion_id);
    }

    // Petición AJAX. Parámetro enviados por GET
    public function get_formulario_edit_registro()
    {
        $linea_factura = VtasDocRegistro::get_un_registro( Input::get('linea_registro_id') );
        $doc_encabezado = VtasDocEncabezado::get_registro_impresion( $linea_factura->vtas_doc_encabezado_id );

        $id = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $id_transaccion = Input::get('id_transaccion');

        $formulario = View::make( 'ventas.pedidos.formulario_editar_registro', compact( 'linea_factura', 'id', 'id_modelo', 'id_transaccion', 'doc_encabezado'))->render();

        return $formulario;
    }

    public function doc_registro_guardar( Request $request )
    {
        $linea_registro = VtasDocRegistro::find( $request->linea_factura_id );
        $cantidad_inicial = $linea_registro->cantidad;
        $doc_encabezado = VtasDocEncabezado::find( $linea_registro->vtas_doc_encabezado_id );

        $viejo_total_encabezado = $doc_encabezado->valor_total;

        // Se pasaron las validaciones
        $precio_unitario = (float)$request->precio_unitario; // IVA incluido
        $cantidad = (float)$request->cantidad;
        $valor_total_descuento = (float)$request->valor_total_descuento;
        $tasa_descuento = (float)$request->tasa_descuento;

        $precio_total = $precio_unitario * $cantidad - $valor_total_descuento;

        $precio_venta_unitario = 0;

        if ( $cantidad != 0 )
        {
            $precio_venta_unitario = $precio_unitario - ( $valor_total_descuento / $cantidad );
        }

        // Valores unitarios
        $base_impuesto = $precio_venta_unitario / ( 1 + $linea_registro->tasa_impuesto / 100);
        $valor_impuesto = $precio_venta_unitario - $base_impuesto;

        $base_impuesto_total = $base_impuesto * $cantidad;
        $valor_impuesto_total = $valor_impuesto * $cantidad;

        // 1. Actualizar total del encabezado de la factura
        $nuevo_total_encabezado = $viejo_total_encabezado - $linea_registro->precio_total + $precio_total;

        $doc_encabezado->update(
                                    ['valor_total' => $nuevo_total_encabezado]
                                );


        // 5. Actualizar el registro del documento de factura
        $cantidad = $request->cantidad; // Se vuelve a la cantidad positiva otra vez
        $linea_registro->update( [
                                    'precio_unitario' => $precio_unitario,
                                    'cantidad' => $cantidad,
                                    'precio_total' => $precio_total,
                                    'base_impuesto' => $base_impuesto,
                                    'valor_impuesto' => $valor_impuesto,
                                    'base_impuesto_total' => $base_impuesto_total,
                                    'tasa_descuento' => $tasa_descuento,
                                    'cantidad_pendiente' => $linea_registro->cantidad_pendiente - $cantidad_inicial + $cantidad,
                                    'valor_total_descuento' => $valor_total_descuento
                                ] );


        return redirect( 'vtas_pedidos/'.$doc_encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','El registro del Pedido de ventas fue MODIFICADO correctamente.');
    }

    /**
     * 
     */
    public function mesero_listado_pedidos_pendientes()
    {
        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Ventas'],
            ['url' => 'ventas?id=' . Input::get('id'), 'etiqueta' => 'Cocinas'],
            ['url' => 'NO', 'etiqueta' => 'Listado de Pedidos Pendientes']
        ];
        
        $pedidos = VtasPedido::where('estado', 'Pendiente')
            ->whereIn('core_tipo_transaccion_id', [42, 60])
            ->orderBy('created_at', 'DESC')->get();

        $vista_pedidos = View::make('ventas.pedidos.restaurante.mesero.lista_pedidos_pendientes_tabla', compact('pedidos'))->render();

        return view('ventas.pedidos.restaurante.mesero.index_pedidos_pendientes', compact( 'miga_pan', 'vista_pedidos'));
    }
}