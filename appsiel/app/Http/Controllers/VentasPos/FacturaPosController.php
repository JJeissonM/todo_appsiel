<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;


use Spatie\Permission\Models\Permission;

use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Inventarios\InventarioController;

use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\Ventas\ReportesController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Sistema\Modelo;
use App\Sistema\Campo;
use App\Core\Tercero;


use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\InvMotivo;

use App\VentasPos\PreparaTransaccion;

use App\VentasPos\FacturaPos;
use App\VentasPos\DocRegistro;
use App\VentasPos\Movimiento;

use App\VentasPos\Pdv;

use App\Ventas\Cliente;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\NotaCredito;

use App\CxC\DocumentosPendientes;
use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoMotivo;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Impuesto;


class FacturaPosController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['22-salida'=>'Ventas POS'];

        $inv_motivo_id = 22;

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros( PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros( $this->transaccion, $motivos ) );

        if ( is_null($tabla) )
        {
            $tabla = '';
        }
        
        $lista_campos = ModeloController::get_campos_modelo($this->modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos, 'create', null);

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo( $this->modelo, '' );



        $user = Auth::user();        

        $pdv = Pdv::find( Input::get('pdv_id') );

        //dd( $pdv->bodega );

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name']) {

                case 'core_tipo_doc_app_id':
                    $lista_campos[$i]['opciones'] = [ $pdv->tipo_doc_app_default_id => $pdv->tipo_doc_app->prefijo . " - " . $pdv->tipo_doc_app->descripcion];
                    break;

                case 'cliente_input':
                    $lista_campos[$i]['value'] = $pdv->cliente->tercero->descripcion;
                    break;

                case 'vendedor_id':
                    $lista_campos[$i]['opciones'] = [ $pdv->cliente->vendedor->id => $pdv->cliente->vendedor->tercero->descripcion];
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$i]['value'] = date('Y-m-d');
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$i]['opciones'] = [ $pdv->bodega_default_id => $pdv->bodega->descripcion ];
                    break;
                default:
                    # code...
                    break;
            }
        }
        
        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Crear: '.$this->transaccion->descripcion );
        
        $productos = InvProducto::get_datos_basicos( '', 'Activo' );
        $precios = ListaPrecioDetalle::get_precios_productos_de_la_lista( $pdv->cliente->lista_precios_id );
        $descuentos = ListaDctoDetalle::get_descuentos_productos_de_la_lista( $pdv->cliente->lista_descuentos_id );

        return view( 'ventas_pos.create', compact('form_create','miga_pan','tabla','pdv','productos','precios','descuentos', 'inv_motivo_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $lineas_registros = json_decode($request->lineas_registros);

        // Crear documento de Ventas
        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);


        // Crear Registros del documento de ventas
        $request['creado_por'] = Auth::user()->email;
        FacturaPosController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        return $doc_encabezado->id;
    }

   


    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public static function crear_registros_documento( Request $request, $doc_encabezado, array $lineas_registros )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);

        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            $linea_datos = [ 'vtas_motivo_id' => (int)$request->inv_motivo_id ] +
                            [ 'inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id ] +
                            [ 'precio_unitario' => (float)$lineas_registros[$i]->precio_unitario ] +
                            [ 'cantidad' => (float)$lineas_registros[$i]->cantidad ] +
                            [ 'precio_total' => (float)$lineas_registros[$i]->precio_total ] +
                            [ 'base_impuesto' => (float)$lineas_registros[$i]->base_impuesto ] +
                            [ 'tasa_impuesto' => (float)$lineas_registros[$i]->tasa_impuesto ] +
                            [ 'valor_impuesto' => (float)$lineas_registros[$i]->valor_impuesto ] +
                            [ 'base_impuesto_total' => (float)$lineas_registros[$i]->base_impuesto_total ] +
                            [ 'tasa_descuento' => (float)$lineas_registros[$i]->tasa_descuento ] +
                            [ 'valor_total_descuento' => (float)$lineas_registros[$i]->valor_total_descuento ] +
                            [ 'creado_por' => Auth::user()->email ] +
                            [ 'estado' => 'Pendiente' ] +
                            [ 'vtas_pos_doc_encabezado_id' => $doc_encabezado->id ];

                            //dd([$datos, $linea_datos]);

            DocRegistro::create( 
                                    $datos +
                                    $linea_datos
                                );

            $datos['consecutivo'] = $doc_encabezado->consecutivo;
            Movimiento::create( 
                                    $datos +
                                    $linea_datos
                                );

            $total_documento += (float)$lineas_registros[$i]->precio_total;

        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();
        
    }

    /**
     *
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        $docs_relacionados = FacturaPos::get_documentos_relacionados( $doc_encabezado );
        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        // Datos de los abonos aplicados a la factura
        $abonos = CxcAbono::get_abonos_documento( $doc_encabezado );

        // Datos de Notas Crédito aplicadas a la factura
        $notas_credito = NotaCredito::get_notas_aplicadas_factura( $doc_encabezado->id );

        $documento_vista = '';

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        $url_crear = $this->modelo->url_crear.$this->variables_url;
        
        $vista = 'ventas.show';

        if( !is_null( Input::get('vista') ) )
        {
            $vista = Input::get('vista');
        }

        return view( $vista, compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito') );
    }


    /*
        Imprimir
    */
    public function imprimir( $id )
    {
        $documento_vista = $this->generar_documento_vista( $id, 'ventas.formatos_impresion.pos' );

        // Se prepara el PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $this->doc_encabezado->documento_transaccion_descripcion.' - '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
        
    }

    /*
        Enviar por email
    */
    public function enviar_por_email( $id )
    {
        $documento_vista = $this->generar_documento_vista( $id, 'ventas.formatos_impresion.'.Input::get('formato_impresion_id') );

        $tercero = Tercero::find( $this->doc_encabezado->core_tercero_id );

        $asunto = $this->doc_encabezado->documento_transaccion_descripcion.' No. '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo;

        $cuerpo_mensaje = 'Saludos, <br/> Le hacemos llegar su '. $asunto;

        $vec = EmailController::enviar_por_email_documento( $this->empresa->descripcion, $tercero->email, $asunto, $cuerpo_mensaje, $documento_vista );

        return redirect( 'ventas/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with( $vec['tipo_mensaje'], $vec['texto_mensaje'] );
    }

    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista( $id, $ruta_vista )
    {
        $this->set_variables_globales();
        
        $this->doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $this->doc_encabezado->id );

        $doc_encabezado = $this->doc_encabezado;
        $empresa = $this->empresa;

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)->where('estado','Activo')->get()->last();

        $etiquetas = $this->get_etiquetas();

        return View::make( $ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas' ) )->render();
    }

    /**
     * Editar encabezado del documento
     */
    public function edit($id)
    {
        $this->set_variables_globales();

        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id);

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, $registro,'edit');

        $doc_encabezado = FacturaPos::get_registro_impresion( $id );

        $cantidad = count( $lista_campos );

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
                                                                <br/>
                                                                <b>Fecha:</b> '.$doc_encabezado->fecha.'
                                                            </b>
                                                            <br/>
                                                            <b>Cliente:</b> '.$doc_encabezado->tercero_nombre_completo.'
                                                            <br/>
                                                            <b>NIT: &nbsp;&nbsp;</b> '.number_format( $doc_encabezado->numero_identificacion, 0, ',', '.').'
                                                        </div>',
                                            "atributos" => [],
                                            "definicion" => "",
                                            "requerido" => 0,
                                            "editable" => 1,
                                            "unico" => 0
                                        ] );

        $form_create = [
                        'url' => $this->modelo->url_form_create,
                        'campos' => $lista_campos
                    ];

        $url_action = 'web/'.$id.$this->variables_url;
        
        if ($this->modelo->url_form_create != '') {
            $url_action = $this->modelo->url_form_create.'/'.$id.$this->variables_url;
        }

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, 'Modificar: '.$doc_encabezado->documento_transaccion_prefijo_consecutivo );

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        return view('layouts.edit', compact('form_create','miga_pan','registro','archivo_js','url_action'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $modelo = Modelo::find($request->url_id_modelo);

        // Se obtinene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($id);

        // LLamar a los campos del modelo para verificar los que son requeridos
        // y los que son únicos
        $lista_campos = $modelo->campos->toArray();
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) {
            if ( $lista_campos[$i]['editable'] == 1 ) 
            { 
                if ($lista_campos[$i]['requerido']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'required']);
                }
                if ($lista_campos[$i]['unico']) 
                {
                    $this->validate($request,[$lista_campos[$i]['name']=>'unique:'.$registro->getTable().','.$lista_campos[$i]['name'].','.$id]);
                }
            }
            // Cuando se edita una transacción
            if ($lista_campos[$i]['name']=='movimiento') {
                $lista_campos[$i]['value']=1;
            }
        }

        $request['modificado_por'] = Auth::user()->email;
        $registro->fill( $request->all() );
        $registro->save();

        // Actualiza Registro de CxC
        $remision = DocumentosPendientes::where('core_tipo_transaccion_id',$registro->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$registro->core_tipo_doc_app_id)
                        ->where('consecutivo',$registro->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha,
                                    'fecha_vencimiento' => $request->fecha_vencimiento
                                ] );

        // Actualiza documento de inventario
        $remision = InvDocEncabezado::find($registro->remision_doc_encabezado_id);
        $remision->fill( $request->all() );
        $remision->save();

        // Actualiza MOVIMIENTO de inventario
        InvMovimiento::where('core_tipo_transaccion_id',$remision->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$remision->core_tipo_doc_app_id)
                        ->where('consecutivo',$remision->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha
                                ] );

        // Actualiza MOVIMIENTO de VENTAS
        Movimiento::where('core_tipo_transaccion_id',$registro->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$registro->core_tipo_doc_app_id)
                        ->where('consecutivo',$registro->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha,
                                    'fecha_vencimiento' => $request->fecha_vencimiento,
                                    'vendedor_id' => $request->vendedor_id,
                                    'orden_compras' => $request->orden_compras
                                ] );

        // Actualiza MOVIMIENTO de CONTABILIDAD para la factura y para la remisión

        ContabMovimiento::where('core_tipo_transaccion_id',$registro->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$registro->core_tipo_doc_app_id)
                        ->where('consecutivo',$registro->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha,
                                    'fecha_vencimiento' => $request->fecha_vencimiento,
                                    'detalle_operacion' => $request->descripcion
                                ] );

        ContabMovimiento::where('core_tipo_transaccion_id',$remision->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$remision->core_tipo_doc_app_id)
                        ->where('consecutivo',$remision->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha,
                                    'detalle_operacion' => $request->descripcion
                                ] );

        return redirect( 'ventas/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    // Parámetro enviados por GET
    public function consultar_clientes()
    {
        $campo_busqueda = Input::get('campo_busqueda');
        
        switch ( $campo_busqueda ) 
        {
            case 'descripcion':
                $operador = 'LIKE';
                $texto_busqueda = '%'.Input::get('texto_busqueda').'%';
                break;
            case 'numero_identificacion':
                $operador = 'LIKE';
                $texto_busqueda = Input::get('texto_busqueda').'%';
                break;
            
            default:
                # code...
                break;
        }

        $clientes = Cliente::leftJoin('core_terceros','core_terceros.id','=','vtas_clientes.core_tercero_id')
                                ->leftJoin('vtas_vendedores','vtas_vendedores.id','=','vtas_clientes.vendedor_id')
                                ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','vtas_clientes.condicion_pago_id')
                                ->leftJoin('vtas_listas_precios_encabezados','vtas_listas_precios_encabezados.id','=','vtas_clientes.lista_precios_id')
                                ->leftJoin('vtas_listas_dctos_encabezados','vtas_listas_dctos_encabezados.id','=','vtas_clientes.lista_descuentos_id')
                                ->leftJoin('inv_bodegas','inv_bodegas.id','=','vtas_clientes.inv_bodega_id')
                                ->where('vtas_clientes.estado','Activo')
                                ->where('core_terceros.'.$campo_busqueda,$operador,$texto_busqueda)
                                ->select('vtas_clientes.id AS cliente_id','vtas_clientes.liquida_impuestos','vtas_clientes.zona_id','vtas_clientes.clase_cliente_id','core_terceros.id AS core_tercero_id','core_terceros.descripcion AS nombre_cliente','core_terceros.numero_identificacion','vtas_vendedores.id AS vendedor_id','vtas_vendedores.equipo_ventas_id','inv_bodegas.id AS inv_bodega_id','vtas_condiciones_pago.dias_plazo','vtas_listas_precios_encabezados.id AS lista_precios_id','vtas_listas_dctos_encabezados.id AS lista_descuentos_id')
                                ->get()
                                ->take(7);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        foreach ($clientes as $linea) 
        {
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
            }

            $html .= '<a class="list-group-item list-group-item-cliente '.$clase.'" data-cliente_id="'.$linea->cliente_id.
                                '" data-nombre_cliente="'.$linea->nombre_cliente.
                                '" data-zona_id="'.$linea->zona_id.
                                '" data-clase_cliente_id="'.$linea->clase_cliente_id.
                                '" data-liquida_impuestos="'.$linea->liquida_impuestos.
                                '" data-core_tercero_id="'.$linea->core_tercero_id.
                                '" data-numero_identificacion="'.$linea->numero_identificacion.
                                '" data-vendedor_id="'.$linea->vendedor_id.
                                '" data-equipo_ventas_id="'.$linea->equipo_ventas_id.
                                '" data-inv_bodega_id="'.$linea->inv_bodega_id.
                                '" data-dias_plazo="'.$linea->dias_plazo.
                                '" data-lista_precios_id="'.$linea->lista_precios_id.
                                '" data-lista_descuentos_id="'.$linea->lista_descuentos_id.
                                '" > '.$linea->nombre_cliente.' ('.number_format($linea->numero_identificacion,0,',','.').') </a>';
        }
        $html .= '</div>';

        return $html;
    }
    


    // Parámetro enviados por GET
    public function consultar_existencia_producto()
    {
        $cliente_id = (int)Input::get('cliente_id');
        $bodega_id = (int)Input::get('bodega_id');
        $fecha = Input::get('fecha');
        $lista_precios_id = (int)Input::get('lista_precios_id');
        $producto_id = (int)Input::get('producto_id');
        
        $producto = InvProducto::where('inv_productos.id', $producto_id)
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.tipo',
                                            'inv_productos.descripcion',
                                            'inv_productos.precio_compra',
                                            'inv_productos.precio_venta')
                                ->get()
                                ->first();

        // Se convierte en array para manipular facilmente sus campos 
        if ( !is_null($producto) ) {
            $producto = $producto->toArray(); 
        }else{
            $producto = [];
        }

        // Si no está vacío el array $producto
        if( !empty($producto) )
        {
            $costo_promedio = InvCostoPromProducto::where('inv_bodega_id','=',$bodega_id)
                                    ->where('inv_producto_id','=',$producto['id'])
                                    ->value('costo_promedio');

            if ( ! ($costo_promedio>0) ) 
            {
                $costo_promedio = 0;
            }


            /*
                El precio de venta se trae de a cuerdo al parámetro de la configuración
                WARNING: Falta el manejo de los descuentos.
            */

            switch ( config('ventas')['modo_liquidacion_precio'] )
            {
                case 'lista_de_precios':
                    // Precios traido desde la lista de precios asociada al cliente.
                    $precio_unitario = ListaPrecioDetalle::get_precio_producto( $lista_precios_id, $fecha, $producto_id );
                    break;

                case 'ultimo_precio':
                    // Precios traido del movimiento de ventas. El último precio liquidado al cliente para ese producto.
                    $precio_unitario = Movimiento::get_ultimo_precio_producto( $cliente_id, $producto_id );
                    break;

                case 'precio_estandar_venta':
                    $precio_unitario = $producto['precio_venta'];
                    break;
                
                default:
                    # code...
                    break;
            }           

            $tasa_impuesto = Impuesto::get_tasa( $producto_id, 0, $cliente_id );

            
            $base_impuesto = $precio_unitario / ( 1 + $tasa_impuesto / 100 );
            $valor_impuesto = $precio_unitario - $base_impuesto;


            // Obtener existencia actual
            $existencia_actual = InvMovimiento::get_existencia_actual( $producto['id'], $bodega_id, $fecha );

            $producto = array_merge($producto,['costo_promedio'=>$costo_promedio]);

            $producto = array_merge($producto, [ 'existencia_actual' => $existencia_actual ],
                                                [ 'tipo' => $producto['tipo'] ],
                                                [ 'costo_promedio' => $costo_promedio ],
                                                [ 'precio_venta' => $precio_unitario ],
                                                [ 'base_impuesto' => $base_impuesto ],
                                                [ 'tasa_impuesto' => $tasa_impuesto ],
                                                [ 'valor_impuesto' => $valor_impuesto ]
                                    );
        }

        //print_r($producto);
        return $producto;
    }

    /*
        Proceso de eliminar FACTURA DE VENTAS
        Se eliminan los registros de:
            - cxc_documentos_pendientes (se debe verificar que no tenga un abono, sino se debe eliminar primero el abono) y su movimiento en contab_movimientos
            - inv_movimientos de la REMISIÓN y su contabilidad. Además se actualiza el estado a Anulado en inv_doc_registros e inv_doc_encabezados
            - vtas_movimientos y su contabilidad. Además se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public static function anular_factura(Request $request)
    {        
        $factura = FacturaPos::find( $request->factura_id );

        $array_wheres = ['core_empresa_id'=>$factura->core_empresa_id, 
            'core_tipo_transaccion_id' => $factura->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $factura->core_tipo_doc_app_id,
            'consecutivo' => $factura->consecutivo];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxcAbono::where('doc_cxc_transacc_id',$factura->core_tipo_transaccion_id)
                            ->where('doc_cxc_tipo_doc_id',$factura->core_tipo_doc_app_id)
                            ->where('doc_cxc_consecutivo',$factura->consecutivo)
                            ->count();

        if($cantidad != 0)
        {
            return redirect( 'ventas/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error','Factura NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería).');
        }

        $modificado_por = Auth::user()->email;

        // 1ro. Anular documento asociado de inventarios
        // Obtener las remisiones relacionadas con la factura y anularlas o dejarlas en estado Pendiente
        $ids_documentos_relacionados = explode( ',', $factura->remision_doc_encabezado_id );
        $cant_registros = count($ids_documentos_relacionados);
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $remision = InvDocEncabezado::find( $ids_documentos_relacionados[$i] );
            if ( !is_null($remision) )
            {
                if ( $request->anular_remision ) // anular_remision es tipo boolean
                {
                    InventarioController::anular_documento_inventarios( $remision->id );
                }else{
                    $remision->update(['estado'=>'Pendiente', 'modificado_por' => $modificado_por]);
                }    
            }
        }

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por cobrar
        CxcMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de compras
        Movimiento::where($array_wheres)->delete();
        // 5to. Se marcan como anulados los registros del documento
        DocRegistro::where( 'vtas_doc_encabezado_id', $factura->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 6to. Se marca como anulado el documento
        $factura->update(['estado'=>'Anulado', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por]);

        return redirect( 'ventas/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('flash_message','Factura de ventas ANULADA correctamente.');
        
    }    

    // Para los terceros que ya están creados
    public function tercero_a_cliente_create()
    {
        $general = new ModeloController();

        return $general->create();
    }

    public function tercero_a_cliente_store(Request $request)
    {
        // Ya el tercero está creado

        // Datos del Cliente
        $Cliente = new Cliente;
        $Cliente->fill( $request->all() );
        $Cliente->save();

        return redirect( 'vtas_clientes/'.$Cliente->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo )->with( 'flash_message','Registro CREADO correctamente.' );
    }


    // Petición AJAX. Parámetro enviados por GET
    public function get_formulario_edit_registro()
    {
        $linea_factura = DocRegistro::get_un_registro( Input::get('linea_registro_id') );

        $factura = FacturaPos::get_registro_impresion( $linea_factura->vtas_doc_encabezado_id );

        $remision = InvDocEncabezado::get_registro_impresion( $factura->remision_doc_encabezado_id );
        $linea_remision = InvDocRegistro::where( 'inv_doc_encabezado_id', $factura->remision_doc_encabezado_id )
                                    ->where( 'inv_producto_id', $linea_factura->producto_id )
                                    ->where( 'cantidad', $linea_factura->cantidad * -1 )
                                    ->get()
                                    ->first();
        
        $saldo_a_la_fecha = InvMovimiento::get_existencia_actual( $linea_remision->inv_producto_id, $linea_remision->inv_bodega_id, $remision->fecha );// - $linea_factura->cantidad;

        $id = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $id_transaccion = Input::get('id_transaccion');

        $producto = InvProducto::find( $linea_remision->inv_producto_id );

        $formulario = View::make( 'ventas.incluir.formulario_editar_registro', compact('linea_factura','linea_remision','remision','id','id_modelo','id_transaccion','saldo_a_la_fecha','producto') )->render();

        return $formulario;
    }

    public function doc_registro_guardar( Request $request )
    {
        $linea_registro = DocRegistro::find( $request->linea_factura_id );
        $doc_encabezado = FacturaPos::find( $linea_registro->vtas_doc_encabezado_id );

        // Verificar si la factura tiene recaudos, si tiene no se pueden modificar sus registros
        $recaudos = CxcAbono::where('doc_cxc_transacc_id',$doc_encabezado->core_tipo_transaccion_id)->where('doc_cxc_tipo_doc_id',$doc_encabezado->core_tipo_doc_app_id)->where('doc_cxc_consecutivo',$doc_encabezado->consecutivo)->get()->toArray();

        if( !empty($recaudos) )
        {
            return redirect( 'ventas/'.$doc_encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Los registros de la Factura NO pueden ser modificados. Factura tiene recaudos de cxc aplicados (Tesorería).');
        }

        // Se pasaron las validaciones
        $precio_unitario = $request->precio_unitario;
        $cantidad = $request->cantidad;
        $valor_total_descuento = $request->valor_total_descuento;
        $tasa_descuento = $request->tasa_descuento;

        $precio_total = $precio_unitario * $cantidad - $valor_total_descuento;

        $precio_venta = $precio_unitario - ( $valor_total_descuento / $cantidad );

        $base_impuesto = $precio_venta / ( 1 + $linea_registro->tasa_impuesto / 100);
        $valor_impuesto = $precio_venta - $base_impuesto;
        $base_impuesto_total = $base_impuesto * $cantidad;
        $valor_impuesto_total = $valor_impuesto * $cantidad;

        // 1. Actualizar total del encabezado de la factura
        $nuevo_total_encabezado = $doc_encabezado->valor_total - $linea_registro->precio_total + $precio_total;

        $doc_encabezado->update(
                                    ['valor_total' => $nuevo_total_encabezado]
                                );

        // 2. Actualiza total de la cuenta por cobrar
        DocumentosPendientes::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$doc_encabezado->consecutivo)
                    ->update( [ 
                                'valor_documento' => $nuevo_total_encabezado,
                                'saldo_pendiente' => $nuevo_total_encabezado
                            ] );

        // 3. Actualiza movimiento de ventas
        Movimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                        ->where('consecutivo',$doc_encabezado->consecutivo)
                        ->where('inv_producto_id',$linea_registro->inv_producto_id)
                        ->where('cantidad',$linea_registro->cantidad)
                        ->where('precio_unitario',$linea_registro->precio_unitario)
                        ->update( [
                                    'precio_unitario' => $precio_unitario,
                                    'cantidad' => $cantidad,
                                    'precio_total' => $precio_total,
                                    'base_impuesto' => $base_impuesto,
                                    'valor_impuesto' => $valor_impuesto,
                                    'base_impuesto_total' => $base_impuesto_total,
                                    'tasa_descuento' => $tasa_descuento,
                                    'valor_total_descuento' => $valor_total_descuento
                                ] );

        // 4. Actualizar movimiento contable del registro de la factura

        // Cartera. Con el total del documento
        $cta_x_cobrar_id = Cliente::get_cuenta_cartera( $doc_encabezado->cliente_id );
        ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('contab_cuenta_id',$cta_x_cobrar_id)
                    ->update( [ 
                                'valor_debito' => $nuevo_total_encabezado,
                                'valor_saldo' => $nuevo_total_encabezado
                            ] );

        // Contabilizar CR: Ingresos e Impuestos
        if ( $linea_registro->tasa_impuesto > 0 )
        {
            $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_ventas( $linea_registro->inv_producto_id );
            ContabMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                        ->where('consecutivo',$doc_encabezado->consecutivo)
                        ->where('inv_producto_id',$linea_registro->inv_producto_id)
                        ->where('cantidad',$linea_registro->cantidad)
                        ->where('valor_credito', ( $linea_registro->valor_impuesto * $linea_registro->cantidad * -1 ) )
                        ->where('contab_cuenta_id',$cta_impuesto_ventas_id)
                        ->update( [ 
                                    'valor_credito' => ($valor_impuesto_total * -1),
                                    'valor_saldo' => ($valor_impuesto_total * -1),
                                    'cantidad' => $cantidad,
                                    'base_impuesto' => $base_impuesto_total,
                                    'valor_impuesto' => $valor_impuesto_total
                                ] );
        }


        // Contabilizar Ingresos (CR)
        // La cuenta de ingresos se toma del grupo de inventarios
        $cta_ingresos_id = InvProducto::get_cuenta_ingresos( $linea_registro->inv_producto_id );
        ContabMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$linea_registro->cantidad)
                    ->where('valor_credito', ( $linea_registro->base_impuesto_total * -1 ) )
                    ->where('contab_cuenta_id',$cta_ingresos_id)
                    ->update( [ 
                                'valor_credito' => ($base_impuesto_total * -1),
                                'valor_saldo' => ($base_impuesto_total * -1),
                                'cantidad' => $cantidad,
                                'base_impuesto' => $base_impuesto_total,
                                'valor_impuesto' => $valor_impuesto_total
                            ] );


        // 5. Actualizar el registro del documento de inventario
        $cantidad_actual = $linea_registro->cantidad * -1; // Para inventarios la cantidad es negativa por ser una salida (Remisión)
        $cantidad = $request->cantidad * -1;
        $inv_doc_registro =InvDocRegistro::where('inv_doc_encabezado_id', $doc_encabezado->remision_doc_encabezado_id)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad', $cantidad_actual)
                    ->get()
                    ->first();

        $costo_total_actual = $inv_doc_registro->costo_total;
        $costo_unitario = $inv_doc_registro->costo_unitario;
        $costo_total = $costo_unitario * $cantidad;
        $inv_doc_registro->update( [
                                'cantidad' => $cantidad,
                                'costo_total' => $costo_total
                            ] );

        // 6. Actualiza movimiento de inventarios
        $inv_doc_encabezado = InvDocEncabezado::find( $doc_encabezado->remision_doc_encabezado_id );
        InvMovimiento::where('core_tipo_transaccion_id',$inv_doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$inv_doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$inv_doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$cantidad_actual)
                    ->update( [
                                'cantidad' => $cantidad,
                                'costo_total' => $costo_total
                            ] );

        // 7. Actualizar movimiento contable del registro del documento de inventario
        // Inventarios (DB)
        $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea_registro->inv_producto_id );
        ContabMovimiento::where('core_tipo_transaccion_id',$inv_doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$inv_doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$inv_doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$cantidad_actual)
                    ->where('contab_cuenta_id',$cta_inventarios_id)
                    ->update( [ 
                                'valor_debito' => $costo_total * -1,
                                'valor_saldo' => $costo_total * -1,
                                'cantidad' => $cantidad
                            ] );

        // Cta. Contrapartida (CR) Dada por el motivo de inventarios de la transaccion 
        // Motivos de inventarios y ventas: Costo de ventas
        // Motivos de compras: Cuentas por legalizar
        $cta_contrapartida_id = InvMotivo::find( $inv_doc_registro->inv_motivo_id )->cta_contrapartida_id;
        ContabMovimiento::where('core_tipo_transaccion_id',$inv_doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$inv_doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$inv_doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$cantidad_actual)
                    ->where('contab_cuenta_id',$cta_contrapartida_id)
                    ->update( [ 
                                'valor_credito' => $costo_total,
                                'valor_saldo' => $costo_total,
                                'cantidad' => $cantidad
                            ] );


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
                                    'valor_total_descuento' => $valor_total_descuento
                                ] );


        return redirect( 'ventas/'.$doc_encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','El registro de la Factura de ventas fue MODIFICADO correctamente.');
    }

    
    // Parámetro enviados por GET
    // Cuando se hace la Remisión y queda pendiente hacer la factura
    public function consultar_remisiones_pendientes()
    {
        $remisiones = InvDocEncabezado::get_documentos_por_transaccion( Input::get('inv_transaccion_id'), Input::get('core_tercero_id'), 'Pendiente' );

        $cliente = Cliente::where( 'core_tercero_id', Input::get('core_tercero_id') )->get()->first();

        $todos_los_productos = [];
        $i=0;
        foreach ($remisiones as $remision)
        {
            $registros_rm = InvDocRegistro::get_registros_impresion( $remision->id );

            foreach ($registros_rm as $un_registro)
            {
                $cantidad = $un_registro->cantidad * -1; // se cambia signo de la cantidad
                
                // El precio se trae de la lista de precios del cliente
                $precio_unitario = ListaPrecioDetalle::get_precio_producto( Input::get('lista_precios_id'), Input::get('fecha'), $un_registro->producto_id );

                $todos_los_productos[$i]['producto_descripcion'] = $un_registro->producto_id.' - '.$un_registro->producto_descripcion;
                $todos_los_productos[$i]['costo_unitario'] = $un_registro->costo_unitario;
                $todos_los_productos[$i]['precio_unitario'] = $precio_unitario;
                $todos_los_productos[$i]['tasa_impuesto'] = Impuesto::get_tasa( $un_registro->producto_id, 0, $cliente->id ).'%';
                $todos_los_productos[$i]['cantidad'] = $cantidad;
                $todos_los_productos[$i]['precio_total'] = $precio_unitario * $cantidad;
                $i++;
            }
        }

        if( empty( $remisiones->toArray() ) ){ return 'sin_registros'; }

        return View::make( 'ventas.incluir.remisiones_pendientes', compact('remisiones','todos_los_productos') )->render();
    }


    public function factura_remision_pendiente( Request $request )
    {
        $datos = $request->all();

        $doc_encabezado = CrudController::crear_nuevo_registro( $request, $request->url_id_modelo );

        $lineas_registros = json_decode( $request->lineas_registros );

        FacturaPosController::crear_lineas_registros( $datos, $doc_encabezado, $lineas_registros );

        return redirect('ventas/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    // Se crean los registros con base en los registros de la remisión o remisiones
    public static function crear_lineas_registros( $datos, $doc_encabezado, $lineas_registros )
    {
        $total_documento = 0;
        // Por cada remisión pendiente
        $cantidad_registros = count( $lineas_registros );
        $remision_doc_encabezado_id = '';
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_remision_id = (int)$lineas_registros[$i]->id_doc;

            $registros_remisiones = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_remision_id )->get();
            foreach ($registros_remisiones as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad * -1;

                // Los precios se deben traer de la lista de precios del cliente
                $precio_unitario = ListaPrecioDetalle::get_precio_producto( $datos['lista_precios_id'], $datos['fecha'], $un_registro->inv_producto_id );

                $cliente = Cliente::where( 'core_tercero_id', $un_registro->core_tercero_id )->get()->first();

                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, 0, $cliente->id );

                $precio_total = $precio_unitario * $cantidad;

                $base_impuesto = $precio_unitario / ( 1 + $tasa_impuesto / 100 );

                $base_impuesto_total = $base_impuesto * $cantidad;

                $linea_datos = [ 'vtas_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total ] +
                                [ 'base_impuesto' =>  $base_impuesto ] +
                                [ 'tasa_impuesto' => $tasa_impuesto ] +
                                [ 'valor_impuesto' => ( $precio_unitario - $base_impuesto ) ] +
                                [ 'base_impuesto_total' => $base_impuesto_total ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                DocRegistro::create( 
                                    $datos + 
                                    [ 'vtas_doc_encabezado_id' => $doc_encabezado->id ] +
                                    $linea_datos
                                );

                $datos['consecutivo'] = $doc_encabezado->consecutivo;
                Movimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // CONTABILIZAR
                $detalle_operacion = $datos['descripcion'];
                FacturaPosController::contabilizar_movimiento_credito( $datos + $linea_datos, $detalle_operacion );

                $total_documento += $precio_total;
            } // Fin por cada registro de la remisión

            // Marcar la remisión como facturada
            InvDocEncabezado::find( $doc_remision_id )->update( [ 'estado' => 'Facturada' ] );

            // Se va creando un listado de remisiones separadas por coma 
            if ($primera)
            {
                $remision_doc_encabezado_id = $doc_remision_id;
                $primera = false;
            }else{
                $remision_doc_encabezado_id .= ','.$doc_remision_id;
            }

        }

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->remision_doc_encabezado_id = $remision_doc_encabezado_id;
        $doc_encabezado->save();
        
        // Un solo registro contable débito
        $forma_pago = 'credito'; // esto se debe determinar de acuerdo a algún parámetro en la configuración, $datos['forma_pago']

        // Cartera ó Caja (DB)
        FacturaPosController::contabilizar_movimiento_debito( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion );

        // Crear registro del pago: cuenta por cobrar(cartera) o recaudo
        FacturaPosController::crear_registro_pago( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion );

        return true;
    }

    public function agregar_precio_lista( Request $request )
    {

        if ( (float)$request->precio == 0)
        {
            return redirect( 'web/'.$request->lista_precios_id.'?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('mensaje_error', 'Precio incorrecto. Por favor ingréselo nuevamente.');
        }

        ListaPrecioDetalle::create( $request->all() );

        return redirect( 'web/'.$request->lista_precios_id.'?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Precio agregado correctamente');
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

}