<?php

namespace App\Http\Controllers\VentasPos;

use App\Http\Controllers\Tesoreria\RecaudoController;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Inventarios\InventarioController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;
use App\Sistema\TipoTransaccion;

// Modelos
use App\Sistema\Modelo;
use App\Core\TipoDocApp;

use App\VentasPos\Services\AccumulationService;
use App\VentasPos\Services\InventoriesServices;
use App\VentasPos\Services\SalesServices;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvProducto;

use App\VentasPos\PreparaTransaccion;

use App\VentasPos\FacturaPos;
use App\VentasPos\DocRegistro;
use App\VentasPos\Movimiento;

use App\Ventas\VtasPedido;
use App\Ventas\Vendedor;

use App\VentasPos\Pdv;

use App\Ventas\Cliente;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\NotaCredito;

use App\Ventas\VtasMovimiento;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\CxP\CxpMovimiento;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoMotivo;

use App\Contabilidad\ContabMovimiento;
use App\Core\Services\ResolucionFacturacionService;
use App\Inventarios\InvGrupo;

class FacturaPosController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        $inv_motivo_id = 10;

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros($this->transaccion, $motivos));

        if (is_null($tabla)) {
            $tabla = '';
        }

        $user = Auth::user();

        $pdv = Pdv::find(Input::get('pdv_id'));

        $obj_msj_resolucion_facturacion = $this->get_msj_resolucion_facturacion( $pdv );
        $msj_resolucion_facturacion = '';
        if ( $obj_msj_resolucion_facturacion->status == 'error' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $obj_msj_resolucion_facturacion->message );
        }

        if ( $obj_msj_resolucion_facturacion->status == 'warning' )
        {
            $msj_resolucion_facturacion = $obj_msj_resolucion_facturacion->message;
        }

        $cliente = $pdv->cliente;
        $vendedor = $cliente->vendedor;
        
        $validar = $this->verificar_datos_por_defecto( $pdv );
        if ( $validar != 'ok' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $validar );
        }
        
        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');

        $lista_campos = $this->ajustar_campos($lista_campos,$pdv,$vendedor);

        $fecha = date('Y-m-d');
        if(config('ventas_pos.asignar_fecha_apertura_a_facturas'))
        {
            $fecha = $pdv->ultima_fecha_apertura();
        }
        $fecha_vencimiento = $pdv->cliente->fecha_vencimiento_pago( $fecha );

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo($this->modelo, '');
        
        $form_create = [
                            'url' => $acciones->store,
                            'campos' => $lista_campos
                        ];

        $id_transaccion = 8; // 8 = Recaudo cartera
        $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion('Recaudo cartera');
        $medios_recaudo = RecaudoController::get_medios_recaudo();
        $cajas = RecaudoController::get_cajas();
        $cuentas_bancarias = RecaudoController::get_cuentas_bancarias();

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Punto de ventas: ' . $pdv->descripcion);

        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        
        $productosTemp = $this->get_productos($pdv,$productos);
        
        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.tags_lista_items', compact('productosTemp'))->render();
        }
        
        // Para visualizar el listado de productos
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $this->generar_plantilla_factura($pdv);

        $pedido_id = 0;

        $lineas_registros = '<tbody></tbody>';

        $numero_linea = 1;

        $valor_subtotal = 0;
        $valor_descuento = 0;
        $valor_total_impuestos = 0;
        $valor_total_factura = 0;
        $total_efectivo_recibido = 0;

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $this->get_parametros_complemento_JSPrintManager($pdv);

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();
        
        $pdv_descripcion = $pdv->descripcion;
        $tipo_doc_app = $pdv->tipo_doc_app;

        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'tabla', 'pdv', 'inv_motivo_id', 'contenido_modal', 'vista_categorias_productos', 'plantilla_factura', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cliente', 'pedido_id', 'lineas_registros', 'numero_linea','valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'total_efectivo_recibido', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion','msj_resolucion_facturacion', 'pdv_descripcion','tipo_doc_app'));
    }

    /**
     * ALMACENA FACTURA POS - ES LLAMADO VÍA AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lineas_registros = json_decode($request->lineas_registros);
        $total_factura = $this->get_total_factura_from_arr_lineas_registros($lineas_registros);

        // Se Actualiza la variable $request via referencia
        $this->actualizar_campo_lineas_registros_medios_recaudos_request($request,$total_factura);

        // Crear documento de Ventas
        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);

        if ($doc_encabezado->core_tercero_id == 0)
        {
            $pdv = Pdv::find($doc_encabezado->pdv_id);
            $doc_encabezado->core_tercero_id = $pdv->cliente->tercero->id;
            $doc_encabezado->save();
        }

        // Crear Registros del documento de ventas
        $request['creado_por'] = Auth::user()->email;
        FacturaPosController::crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        $pedido = VtasPedido::find($request->pedido_id);
        if ( !is_null( $pedido ) )
        {
            if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                $todos_los_pedidos = $this->get_todos_los_pedidos_mesero_para_la_mesa($pedido);

                foreach ($todos_los_pedidos as $un_pedido) {
                    $un_pedido->ventas_doc_relacionado_id = $doc_encabezado->id;
                    $un_pedido->estado = 'Facturado';
                    $un_pedido->save(); 
                    
                    self::actualizar_cantidades_pendientes( $un_pedido, 'restar' );
                }
            }else{
                $pedido->ventas_doc_relacionado_id = $doc_encabezado->id;
                $pedido->estado = 'Facturado';
                $pedido->save();
                self::actualizar_cantidades_pendientes( $pedido, 'restar' );
            }
        }

        return $doc_encabezado->consecutivo;
    }

    /**
     *
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id);

        $doc_encabezado = app($this->transaccion->modelo_encabezados_documentos)->get_registro_impresion($id);
        $doc_registros = app($this->transaccion->modelo_registros_documentos)->get_registros_impresion($doc_encabezado->id);

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados($doc_encabezado);
        $empresa = $this->empresa;
        if ( !is_null($doc_encabezado->pdv) )
        {
            if ( $doc_encabezado->pdv->direccion != '' )
            {
                $empresa->direccion1 = $doc_encabezado->pdv->direccion;
                $empresa->telefono1 = $doc_encabezado->pdv->telefono;
                $empresa->email = $doc_encabezado->pdv->email;
            }
        }

        $id_transaccion = $this->transaccion->id;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad($doc_encabezado);

        // Datos de los abonos aplicados a la factura
        $abonos = CxcAbono::get_abonos_documento($doc_encabezado);

        // Datos de Notas Crédito aplicadas a la factura
        $notas_credito = NotaCredito::get_notas_aplicadas_factura($doc_encabezado->id);

        $documento_vista = '';

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo);

        $url_crear = $this->modelo->url_crear . $this->variables_url;

        $vista = 'ventas_pos.show';

        if ( !is_null(Input::get('vista') ) )
        {
            $vista = Input::get('vista');
        }

        $pedidos_padres = VtasDocEncabezado::where([
            ['ventas_doc_relacionado_id','=',$doc_encabezado->id]
        ])
        ->whereIn('core_tipo_transaccion_id',[42,60])
        ->get();

        return view($vista, compact('id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad', 'abonos', 'empresa', 'docs_relacionados', 'doc_registros', 'url_crear', 'id_transaccion', 'notas_credito','pedidos_padres'));
    }

    /*
        Imprimir
    */
    public function imprimir($id)
    {
        return $this->generar_documento_vista($id, 'ventas.formatos_impresion.pos');
    }

    /**
     * Prepara la vista para Editar una Factura POS
     */
    public function edit($id)
    {
        $this->set_variables_globales();

        // Se obtiene el registro a modificar del modelo
        $registro = app($this->modelo->name_space)->find($id); // Encabezado FActura POS

        $pdv = Pdv::find($registro->pdv_id);

        $obj_msj_resolucion_facturacion = $this->get_msj_resolucion_facturacion( $pdv );
        $msj_resolucion_facturacion = '';
        if ( $obj_msj_resolucion_facturacion->status == 'error' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $obj_msj_resolucion_facturacion->message );
        }

        if ( $obj_msj_resolucion_facturacion->status == 'warning' )
        {
            $msj_resolucion_facturacion = $obj_msj_resolucion_facturacion->message;
        }

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, $registro, 'edit');

        $doc_encabezado = FacturaPos::get_registro_impresion($id);

        $cantidad = count($lista_campos);

        $eid = '';

		if( config("configuracion.tipo_identificador") == 'NIT') { 
            $eid = number_format( $doc_encabezado->numero_identificacion, 0, ',', '.');
        }else { 
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
                                                                ' . $doc_encabezado->documento_transaccion_descripcion . '
                                                                <br/>
                                                                <b>No.</b> ' . $doc_encabezado->documento_transaccion_prefijo_consecutivo . '
                                                                <br/>
                                                                <b>Fecha:</b> ' . $doc_encabezado->fecha . '
                                                            </b>
                                                            <br/>
                                                            <b>Cliente:</b> ' . $doc_encabezado->tercero_nombre_completo . '
                                                            <br/>
                                                            <b>'.config("configuracion.tipo_identificador").' &nbsp;&nbsp;</b> ' . $eid. '
                                                        </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        //Personalización de la lista de campos
        foreach ($lista_campos as $key => $value)
        {
            switch ($value['name']){

                case 'cliente_input':
                    $lista_campos[$key]['value'] = $doc_encabezado->tercero_nombre_completo;
                    break;

                case 'vendedor_id':
                    $lista_campos[$key]['value'] = [$doc_encabezado->vendedor_id];
                    break;

                case 'core_tipo_doc_app_id':
                    $lista_campos[$key]['editable'] = 1;
                    $lista_campos[$key]['atributos'] = [];
                    $lbl_value = $lista_campos[$key]['opciones'][$lista_campos[$key]['value']];
                    $lista_campos[$key]['opciones'] = [
                        $lista_campos[$key]['value'] => $lbl_value
                    ];
                    break;

                case 'forma_pago':
                    $lista_campos[$key]['value'] = $doc_encabezado->condicion_pago;
                    $lista_campos[$key]['editable'] = 1;
                    $lista_campos[$key]['atributos'] = [];
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$key]['value'] = $doc_encabezado->fecha_vencimiento;
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$key]['opciones'] = [$pdv->bodega_default_id => $pdv->bodega->descripcion];
                    break;
                default:
                    # code...
                    break;
            }
        }

        $fecha = $doc_encabezado->fecha;
        $fecha_vencimiento = $doc_encabezado->fecha_vencimiento;

        $acciones = $this->acciones_basicas_modelo($this->modelo, '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'));

        $url_action = str_replace('id_fila', $registro->id, $acciones->update);

        $form_create = [
            'url' => $url_action,
            'campos' => $lista_campos
        ];

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Punto de ventas: ' . $pdv->descripcion . '.' . ' Modificar: ' . $doc_encabezado->documento_transaccion_prefijo_consecutivo);

        $archivo_js = app($this->modelo->name_space)->archivo_js;

        $motivos = ['10-salida' => 'Ventas POS'];
        $inv_motivo_id  = 10;

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros($this->transaccion, $motivos));

        if (is_null($tabla)) {
            $tabla = '';
        }

        $id_transaccion = 8; // 8 = Recaudo cartera
        $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion('Recaudo cartera');
        $medios_recaudo = RecaudoController::get_medios_recaudo();
        $cajas = RecaudoController::get_cajas();
        $cuentas_bancarias = RecaudoController::get_cuentas_bancarias();

        $numero_linea = count($registro->lineas_registros) + 1;

        $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($registro->lineas_registros);
        
        $cuerpo_tabla_medios_recaudos = $this->armar_cuerpo_tabla_medios_recaudos($registro);

        $vista_medios_recaudo = View::make('tesoreria.incluir.medios_recaudos', compact('id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cuerpo_tabla_medios_recaudos'))->render();

        $total_efectivo_recibido = $this->get_total_campo_lineas_registros(json_decode(str_replace("$", "", $registro->lineas_registros_medios_recaudos)), 'valor');
        //$total_efectivo_recibido = 0;
        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productosTemp = $this->get_productos($pdv,$productos);

        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.tags_lista_items', compact('productosTemp'))->render();
        }
        
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $this->generar_plantilla_factura($pdv);

        $redondear_centena = config('ventas_pos.redondear_centena');
        
        $cliente = $registro->cliente;
        $vendedor = $registro->vendedor;

        $pedido_id = 0;

        $valor_subtotal = number_format($registro->lineas_registros->sum('base_impuesto_total') + $registro->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_descuento = number_format( $registro->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_total_impuestos = number_format( $registro->lineas_registros->sum('precio_total') - $registro->lineas_registros->sum('base_impuesto_total'),'2',',','.');

        $valor_total_factura = $registro->lineas_registros->sum('precio_total');

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $this->get_parametros_complemento_JSPrintManager($pdv);
        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();

        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'registro', 'archivo_js', 'url_action', 'pdv', 'inv_motivo_id', 'tabla', 'productos', 'contenido_modal', 'plantilla_factura', 'redondear_centena', 'numero_linea', 'lineas_registros', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias', 'vista_medios_recaudo', 'total_efectivo_recibido','vista_categorias_productos','cliente', 'pedido_id', 'valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion', 'msj_resolucion_facturacion'));
    }

    public function crear_desde_pedido($pedido_id)
    {
        $this->set_variables_globales();

        // DATOS DE LINEAS DE REGISTROS DEL PEDIDO
        $pedido = VtasPedido::find($pedido_id);

        $numero_linea = count($pedido->lineas_registros) + 1;

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['10-salida' => 'Ventas POS'];

        $inv_motivo_id = 10;

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros(PreparaTransaccion::get_datos_tabla_ingreso_lineas_registros($this->transaccion, $motivos));

        if (is_null($tabla)) {
            $tabla = '';
        }

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos, 'create', null);

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo($this->modelo, '');

        $user = Auth::user();

        $pdv = Pdv::find(Input::get('pdv_id'));

        $obj_msj_resolucion_facturacion = $this->get_msj_resolucion_facturacion( $pdv );
        $msj_resolucion_facturacion = '';
        if ( $obj_msj_resolucion_facturacion->status == 'error' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $obj_msj_resolucion_facturacion->message );
        }

        if ( $obj_msj_resolucion_facturacion->status == 'warning' )
        {
            $msj_resolucion_facturacion = $obj_msj_resolucion_facturacion->message;
        }

        $cliente = $pedido->cliente;
        $vendedor = $pedido->vendedor;
        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++) {
            switch ($lista_campos[$i]['name']) {

                case 'core_tipo_doc_app_id':
                    $lista_campos[$i]['opciones'] = [$pdv->tipo_doc_app_default_id => $pdv->tipo_doc_app->prefijo . " - " . $pdv->tipo_doc_app->descripcion];
                    break;

                case 'cliente_input':
                    $lista_campos[$i]['value'] = $cliente->tercero->descripcion;
                    break;

                case 'vendedor_id':
                    $lista_campos[$i]['value'] = [$vendedor->id];
                    break;

                case 'fecha':
                    $lista_campos[$i]['value'] = $pdv->ultima_fecha_apertura();
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$i]['value'] = date('Y-m-d');
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$i]['opciones'] = [$pdv->bodega_default_id => $pdv->bodega->descripcion];
                    break;
                default:
                    # code...
                    break;
            }
        }

        $fecha = $pdv->ultima_fecha_apertura();
        $fecha_vencimiento = $pdv->cliente->fecha_vencimiento_pago( $pdv->ultima_fecha_apertura() );

        $form_create = [
            'url' => $acciones->store,
            'campos' => $lista_campos
        ];
        $id_transaccion = 8; // 8 = Recaudo cartera
        $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion('Recaudo cartera');
        $medios_recaudo = RecaudoController::get_medios_recaudo();
        $cajas = RecaudoController::get_cajas();
        $cuentas_bancarias = RecaudoController::get_cuentas_bancarias();
 
        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Punto de ventas: ' . $pdv->descripcion . '. Creación desde pedido.');

        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productosTemp = null;
        foreach ($productos as $pr){
            $pr->categoria = InvGrupo::find($pr->inv_grupo_id)->descripcion;
            $productosTemp[$pr->categoria][] = $pr;
        }
        $vista_categorias_productos = '';
        
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $this->generar_plantilla_factura($pdv);

        $redondear_centena = config('ventas_pos.redondear_centena');
        
        if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
            $todas_las_lineas_registros = $this->unificar_lineas_registros_pedidos($pedido);

            $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($todas_las_lineas_registros);
        }else{
            $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($pedido->lineas_registros);
        }

        $valor_subtotal = 0;
        $valor_descuento = 0;
        $valor_total_impuestos = 0;
        $valor_total_factura = 0;
        $total_efectivo_recibido = 0;

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $this->get_parametros_complemento_JSPrintManager($pdv);
        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();

        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'tabla', 'pdv', 'inv_motivo_id', 'contenido_modal', 'plantilla_factura', 'redondear_centena', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias', 'lineas_registros', 'numero_linea', 'pedido_id', 'cliente','vista_categorias_productos', 'valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'total_efectivo_recibido', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion', 'msj_resolucion_facturacion') );
    }

    /**
     * ACTUALIZA FACTURA POS
     *
     */
    public function update(Request $request, $id)
    {
        $lineas_registros = json_decode($request->lineas_registros);
        $total_factura = $this->get_total_factura_from_arr_lineas_registros($lineas_registros);

        $this->actualizar_campo_lineas_registros_medios_recaudos_request($request,$total_factura);

        $doc_encabezado = FacturaPos::find($id);
        $doc_encabezado->fecha = $request->fecha;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->forma_pago = $request->forma_pago;
        $doc_encabezado->fecha_vencimiento = $request->fecha_vencimiento;
        $doc_encabezado->vendedor_id = $request->vendedor_id;
        $doc_encabezado->lineas_registros_medios_recaudos = $request->lineas_registros_medios_recaudos;
        $doc_encabezado->valor_total = $total_factura;
        $doc_encabezado->modificado_por = Auth::user()->email;
        $doc_encabezado->save();

        // Borrar líneas de registros anteriores
        DocRegistro::where('vtas_pos_doc_encabezado_id', $doc_encabezado->id)->delete();

        // Borrar movimiento anterior
        Movimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
            ->where('consecutivo', $doc_encabezado->consecutivo)
            ->delete();

        // Crear nuevamente las líneas de registros
        $request['creado_por'] = Auth::user()->email;
        $request['modificado_por'] = Auth::user()->email;
        FacturaPosController::crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        return $doc_encabezado->consecutivo;
    }

    /*
        Proceso de eliminar FACTURA POS (Antes de acumulación)
    */
    public function anular_factura_pos($doc_encabezado_id)
    {
        $factura = FacturaPos::find($doc_encabezado_id);

        $modificado_por = Auth::user()->email;

        // Se elimina el Mov. Vtas. POS
        Movimiento::where('core_tipo_transaccion_id', $factura->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $factura->core_tipo_doc_app_id)
            ->where('consecutivo', $factura->consecutivo)
            ->delete();

        // Se marcan como anulados los registros del documento
        DocRegistro::where('vtas_pos_doc_encabezado_id', $factura->id)->update(['estado' => 'Anulado', 'modificado_por' => $modificado_por]);

        // Se marca como anulado el documento
        $factura->update(['estado' => 'Anulado', 'modificado_por' => $modificado_por]);

        $pedido = VtasDocEncabezado::where( 'ventas_doc_relacionado_id' , $factura->id )->get()->first();
        if( !is_null($pedido) )
        {
            if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                $todos_los_pedidos = $this->get_todos_los_pedidos_mesero_para_la_mesa($pedido);

                foreach ($todos_los_pedidos as $un_pedido) {
                    $un_pedido->estado = "Pendiente";
                    $un_pedido->ventas_doc_relacionado_id = 0;
                    $un_pedido->save();

                    self::actualizar_cantidades_pendientes( $un_pedido, 'sumar' );
                }
            }else{
                $pedido->estado = "Pendiente";
                $pedido->ventas_doc_relacionado_id = 0;
                $pedido->save();

                self::actualizar_cantidades_pendientes( $pedido, 'sumar' );
            }                
        }


        return 1;
    }

    public static function actualizar_cantidades_pendientes( $encabezado_pedido, $operacion )
    {
        $lineas_registros_pedido = $encabezado_pedido->lineas_registros;
        foreach( $lineas_registros_pedido AS $linea_pedido )
        {            
            if ( $operacion == 'restar' )
            {
                $linea_pedido->cantidad_pendiente = 0;
            }else{
                // sumar: al anular
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad;
            }
                
            $linea_pedido->save();
        }
    }

    public function anular_factura_acumulada(Request $request)
    {
        $factura = FacturaPos::find($request->factura_id);

        $array_wheres = [
            'core_empresa_id' => $factura->core_empresa_id,
            'core_tipo_transaccion_id' => $factura->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $factura->core_tipo_doc_app_id,
            'consecutivo' => $factura->consecutivo
        ];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxcAbono::where('doc_cxc_transacc_id', $factura->core_tipo_transaccion_id)
            ->where('doc_cxc_tipo_doc_id', $factura->core_tipo_doc_app_id)
            ->where('doc_cxc_consecutivo', $factura->consecutivo)
            ->count();

        if ($cantidad != 0) {
            return redirect('pos_factura/' . $request->factura_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('mensaje_error', 'Factura NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería).');
        }

        $modificado_por = Auth::user()->email;

        // 1ro. Anular documento asociado de inventarios
        // Obtener las remisiones relacionadas con la factura y anularlas o dejarlas en estado Pendiente
        $ids_documentos_relacionados = explode(',', $factura->remision_doc_encabezado_id);
        $cant_registros = count($ids_documentos_relacionados);
        for ($i = 0; $i < $cant_registros; $i++) {
            $remision = InvDocEncabezado::find($ids_documentos_relacionados[$i]);
            if (!is_null($remision)) {
                if ($request->anular_remision) // anular_remision es tipo boolean
                {
                    InventarioController::anular_documento_inventarios($remision->id);
                } else {
                    $remision->update(['estado' => 'Pendiente', 'modificado_por' => $modificado_por]);
                }
            }
        }

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por cobrar y de tesorería
        CxcMovimiento::where($array_wheres)->delete();
        TesoMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el movimiento de ventas POS y Ventas Estándar
        Movimiento::where($array_wheres)->delete();
        VtasMovimiento::where($array_wheres)->delete();

        // 5to. Se marcan como anulados los registros del documento
        DocRegistro::where('vtas_pos_doc_encabezado_id', $factura->id)->update(['estado' => 'Anulado', 'modificado_por' => $modificado_por]);

        // Si la factura se hizo desde un pedido
        $pedido = VtasDocEncabezado::where( 'ventas_doc_relacionado_id' , $factura->id )->get()->first();
        if( !is_null($pedido) )
        {
            if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                $todos_los_pedidos = $this->get_todos_los_pedidos_mesero_para_la_mesa($pedido);

                foreach ($todos_los_pedidos as $un_pedido) {
                    $un_pedido->estado = "Pendiente";
                    $un_pedido->ventas_doc_relacionado_id = 0;
                    $un_pedido->save();

                    self::actualizar_cantidades_pendientes( $un_pedido, 'sumar' );
                }
            }else{
                $pedido->estado = "Pendiente";
                $pedido->ventas_doc_relacionado_id = 0;
                $pedido->save();

                self::actualizar_cantidades_pendientes( $pedido, 'sumar' );
            }
        }

        // 6to. Se marca como anulado el documento
        $factura->update(['estado' => 'Anulado', 'remision_doc_encabezado_id' => '', 'modificado_por' => $modificado_por]);

        return redirect('pos_factura/' . $request->factura_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Factura de ventas ANULADA correctamente.');
    }

    /*
        VALIDAR EXISTENCIAS

        Esta es la funcion principal que se llama desde el boton ACUMULAR: RUTA GET pos_factura_validar_existencias

        => Hacer desarme automático de productos
        => Validar existencias de Items
    */
    public function validar_existencias( $pdv_id )
    {
        $obj_acumm_serv = new AccumulationService( $pdv_id );
        if( !$obj_acumm_serv->thereis_documents() )
        {
            return 'No hay documentos pendientes.';
        }
        
        // Un documento de desarme (MK) por acumulación
        $obj_acumm_serv->hacer_desarme_automatico();        
        
        if ( (int)config( 'ventas_pos.crear_ensamble_de_recetas' ) )
        {
            // Un documento de ENSAMBLE (MK) por cada Item Platillo vendido
            $obj_acumm_serv->hacer_preparaciones_recetas();
        }        

        if ( !(int)config( 'ventas_pos.validar_existencias_al_acumular' ) )
        {
            return 1;
        }

        $obj_invt_serv = new InventoriesServices();
        $lista_items_aux = $obj_invt_serv->resumen_cantidades_facturadas($pdv_id)->toArray();
        
        $lista_items = [];
        foreach( $lista_items_aux AS $linea )
        {
            $lista_items[$linea['inv_producto_id']] = $linea['cantidad_facturada'];
        }

        return $obj_invt_serv->tabla_items_existencias_negativas( $obj_acumm_serv->pos->bodega_default_id, $obj_acumm_serv->invoices->last()->fecha, $lista_items );

    }

    public function hacer_preparaciones_recetas($pdv_id)
    {
        $obj_acumm_serv = new AccumulationService( $pdv_id );

        // Un documento de ENSAMBLE (MK) por cada Item Platillo vendido
        $obj_acumm_serv->hacer_preparaciones_recetas();

    }

    /*
        ACUMULAR
        => Genera movimiento de ventas
        => Genera Documentos de Remisión y movimiento de inventarios
        => Genera Movimiento de Tesorería O CxC
        y
        CONTABILIZAR
        => Genera Movimiento Contable para:
            * Movimiento de Ventas (Ingresos e Impuestos)
            * Movimiento de Inventarios (Inventarios y Costos)
            * Movimiento de Tesorería (Caja y Bancos)
            * Movimiento de CxC (Cartera de clientes)
    */
    public function acumular_una_factura($factura_id)
    {
        $obj_acumm_serv = new AccumulationService( 0 );

        $obj_acumm_serv->accumulate_one_invoice($factura_id);

        return 1;
    }

    public function acumular_una_factura_individual($factura_id)
    {
        $obj_acumm_serv = new AccumulationService( 0 );

        $obj_acumm_serv->accumulate_one_invoice($factura_id);

        return redirect('pos_factura/' . $factura_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('flash_message', 'Factura Acumulada correctamente.');
    }   

    public function contabilizar_una_factura($factura_id)
    {
        $obj_acumm_serv = new AccumulationService( 0 );

        $obj_acumm_serv->accounting_one_invoice($factura_id);

        return 1;
    }

    public function form_registro_ingresos_gastos($pdv_id, $id_modelo, $id_transaccion)
    {
        $pdv = Pdv::find((int)$pdv_id);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $tipo_docs_app_id = $tipo_transaccion->tipos_documentos->first()->id;

        $fecha = date('Y-m-d');        
        if(config('ventas_pos.asignar_fecha_apertura_a_facturas'))
        {
            $fecha = $pdv->ultima_fecha_apertura();
        }

        $campos = (object)[
            'core_tipo_transaccion_id' => $id_transaccion,
            'core_tipo_doc_app_id' => $tipo_docs_app_id,
            'consecutivo' => 0,
            'fecha' => $fecha,
            'core_empresa_id' => Auth::user()->empresa_id,
            'teso_medio_recaudo_id' => 1,
            'teso_caja_id' => $pdv->caja_default_id,
            'teso_cuenta_bancaria_id' => 0,
            'estado' => 'Activo',
            'creado_por' => Auth::user()->email,
            'id_modelo' => $id_modelo
        ];

        return View::make('ventas_pos.form_registro_ingresos_gastos', compact('pdv', 'campos','id_transaccion'))->render();
    }

    /*
        Proceso especial para generar remisiones de documentos YA acumulados
    */
    public function generar_remisiones($pdv_id)
    {
        $pdv = Pdv::find($pdv_id);

        $encabezados_documentos = FacturaPos::where('pdv_id', $pdv_id)->where('estado', 'Acumulado')->get();

        foreach ($encabezados_documentos as $factura) {
            $lineas_registros = DocRegistro::where('vtas_pos_doc_encabezado_id', $factura->id)->get();

            // Crear Remisión y Mov. de inventarios
            $datos_remision = $factura->toArray();
            $datos_remision['inv_bodega_id'] = $pdv->bodega_default_id;

            $doc_remision = InventarioController::crear_encabezado_remision_ventas($datos_remision);

            InventarioController::crear_registros_remision_ventas($doc_remision, $lineas_registros);

            $factura->remision_doc_encabezado_id = $doc_remision->id;
            $factura->save();
        }
        return 1;
    }


    public function store_registro_ingresos_gastos(Request $request)
    {
        // $this->datos es una variable de 
        $this->datos = $request->all();
        $this->datos['core_tercero_id'] = $request->cliente_proveedor_id;
        $this->datos['descripcion'] = $request->detalle_operacion;

        $modelo = Modelo::find($request->id_modelo);

        // Guardar encabezado del documento
        $doc_encabezado = app($modelo->name_space)->create($this->datos);

        $valor_movimiento = $request->col_valor;

        // Si se está almacenando una transacción que maneja consecutivo
        if (isset($request->consecutivo) and isset($request->core_tipo_doc_app_id)) {
            // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual($request->core_empresa_id, $request->core_tipo_doc_app_id) + 1;

            // Se incementa el consecutivo para ese tipo de documento y la empresa
            TipoDocApp::aumentar_consecutivo($request->core_empresa_id, $request->core_tipo_doc_app_id);

            $doc_encabezado->consecutivo = $consecutivo;
            $doc_encabezado->valor_total = $valor_movimiento;
            $doc_encabezado->save();
        }

        // Guardar registro del documentos
        $tipo_transaccion = TipoTransaccion::find($request->core_tipo_transaccion_id);
        app($tipo_transaccion->modelo_registros_documentos)->create(
            ['teso_encabezado_id' => $doc_encabezado->id] +
                ['teso_motivo_id' => $request->campo_motivos] +
                ['teso_caja_id' => $request->teso_caja_id] +
                ['core_tercero_id' => $request->cliente_proveedor_id] +
                ['detalle_operacion' => $request->detalle_operacion] +
                ['valor' => $valor_movimiento] +
                ['estado' => 'Activo']
        );

        // Guardar movimiento de tesorería
        $motivo = TesoMotivo::find($request->campo_motivos);
        $valor_movimiento_teso = $valor_movimiento;
        if ($motivo->movimiento == 'salida') {
            $valor_movimiento_teso = $valor_movimiento * -1;
        }

        $this->datos['consecutivo'] = $doc_encabezado->consecutivo;
        app($tipo_transaccion->modelo_movimientos)->create(
            $this->datos +
                ['teso_motivo_id' => $motivo->id] +
                ['teso_caja_id' => $request->teso_caja_id] +
                ['teso_cuenta_bancaria_id' => 0] +
                ['valor_movimiento' => $valor_movimiento_teso] +
                ['estado' => 'Activo']
        );


        // Guardar contabilización de tesorería, siempre CAJA
        $contab_cuenta_id = TesoCaja::find($request->teso_caja_id)->contab_cuenta_id;
        $valor_debito = $valor_movimiento;
        $valor_credito = 0;
        if ($motivo->movimiento == 'salida') {
            $valor_debito = 0;
            $valor_credito = $valor_movimiento;
        }
        $this->contabilizar_registro($contab_cuenta_id, $request->detalle_operacion, $valor_debito, $valor_credito, $request->teso_caja_id, 0);

        // Guardar contabiización contrapartida
        $contab_cuenta_id = $motivo->contab_cuenta_id;
        $valor_debito = 0;
        $valor_credito = $valor_movimiento;
        if ($motivo->movimiento == 'salida') {
            $valor_debito = $valor_movimiento;
            $valor_credito = 0;
        }
        $this->contabilizar_registro($contab_cuenta_id, $request->detalle_operacion, $valor_debito, $valor_credito);

        // Guardar otros movimientos según motivo

        // Generar CxP a favor. Saldo negativo por pagar (a favor de la empresa)
        if ($motivo->teso_tipo_motivo == 'Anticipo proveedor') {
            $this->datos['valor_documento'] = $valor_movimiento * -1;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento * -1;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            CxpMovimiento::create($this->datos);
        }

        // Generar CxP porque se utilizó dinero de un agente externo (banco, coopertaiva, tarjeta de crédito).
        if ($motivo->teso_tipo_motivo == 'Prestamo financiero') {
            $this->datos['valor_documento'] = $valor_movimiento;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            CxpMovimiento::create($this->datos);
        }

        // Generar CxC por algún dinero prestado o anticipado a trabajadores o clientes.
        if ($motivo->teso_tipo_motivo == 'Pago anticipado') {
            $this->datos['valor_documento'] = $valor_movimiento;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            CxcMovimiento::create($this->datos);
        }

        // Generar CxC: movimiento de cartera de clientes
        if ($motivo->teso_tipo_motivo == 'Anticipo') {
            $this->datos['valor_documento'] = $valor_movimiento * -1;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento * -1;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            CxcMovimiento::create($this->datos);
        }

        return '<h4>Registro almacenado correctamente<br><span class="text-info">Documento: ' . $doc_encabezado->tipo_documento_app->prefijo . ' ' . $doc_encabezado->consecutivo . '</span></h4><hr><a class="btn-gmail" href="' . url('/') . '/tesoreria/pagos_imprimir/' . $doc_encabezado->id . '?id=3&id_modelo=' . $request->id_modelo . '&id_transaccion=' . $request->id_transaccion . '&formato_impresion_id=pos' . '" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i></a>';
    }

    public function get_etiquetas()
    {
        $parametros = config('ventas');

        $encabezado = '';

        if ($parametros['encabezado_linea_1'] != '') {
            $encabezado .= $parametros['encabezado_linea_1'];
        }

        if ($parametros['encabezado_linea_2'] != '') {
            $encabezado .= '<br>' . $parametros['encabezado_linea_2'];
        }

        if ($parametros['encabezado_linea_3'] != '') {
            $encabezado .= '<br>' . $parametros['encabezado_linea_3'];
        }


        $pie_pagina = '';

        if ($parametros['pie_pagina_linea_1'] != '') {
            $pie_pagina .= $parametros['pie_pagina_linea_1'];
        }

        if ($parametros['pie_pagina_linea_2'] != '') {
            $pie_pagina .= '<br>' . $parametros['pie_pagina_linea_2'];
        }

        if ($parametros['pie_pagina_linea_3'] != '') {
            $pie_pagina .= '<br>' . $parametros['pie_pagina_linea_3'];
        }

        return ['encabezado' => $encabezado, 'pie_pagina' => $pie_pagina];
    }

    public function unificar_lineas_registros_pedidos($pedido)
    {
        $todos_los_pedidos = $this->get_todos_los_pedidos_mesero_para_la_mesa($pedido);
        
        $todas_las_lineas_registros = [];
        foreach ($todos_los_pedidos as $pedido) {
            $lineas_registros = $pedido->lineas_registros;
            foreach ($lineas_registros as $linea) {
                $todas_las_lineas_registros[] = $linea;
            }
        }

        return $todas_las_lineas_registros;
    }

    public function set_catalogos( $pdv_id )
    {
        $pdv = Pdv::find( $pdv_id );
        $datos = [
                    'redondear_centena' => config('ventas_pos.redondear_centena'),
                    'productos' => InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id),
                    'precios' => ListaPrecioDetalle::get_precios_productos_de_la_lista( $pdv->cliente->lista_precios_id ),
                    'descuentos' => ListaDctoDetalle::get_descuentos_productos_de_la_lista( $pdv->cliente->lista_descuentos_id ),
                    'clientes' => Cliente::where( 'estado', 'Activo' )->get(),
                    'cliente_default' => array_merge( $pdv->cliente->tercero->toArray(), $pdv->cliente->toArray(), ['vendedor_descripcion'=> $pdv->cliente->vendedor->tercero->descripcion] ) ,
                    'forma_pago_default' => $pdv->cliente->forma_pago(),
                    'fecha_vencimiento_default' => $pdv->cliente->fecha_vencimiento_pago( date('Y-m-d') )
                ];
        
        return response()->json( $datos );
    }

    // Recontabilizar un documento dada su ID
    public static function recontabilizar_factura( $documento_id )
    {
        $documento = FacturaPos::find($documento_id);

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
                        ->where('consecutivo', $documento->consecutivo)
                        ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = DocRegistro::where('vtas_pos_doc_encabezado_id', $documento->id)->get();

        $total_documento = 0;
        $n = 1;
        $obj_sales_serv = new SalesServices();
        foreach ($registros_documento as $linea)
        {
            $detalle_operacion = 'Recontabilizado. ' . $linea->descripcion;
            $obj_sales_serv->contabilizar_movimiento_credito( $documento->toArray() + $linea->toArray(), $detalle_operacion);
            $total_documento += $linea->precio_total;
            $n++;
        }

        $forma_pago = $documento->forma_pago;

        $datos = $documento->toArray();
        $obj_sales_serv->contabilizar_movimiento_debito( $forma_pago, $datos, $datos['valor_total'], $detalle_operacion, $documento->pdv->caja_default_id);

        return redirect( 'pos_factura/' . $documento->id . '?id=20&id_modelo=230&id_transaccion=47' )->with('flash_message', 'Documento Recontabilizado.');
    }

    public function armar_cuerpo_tabla_lineas_registros($lineas_registros_documento)
    {
        $cuerpo_tabla_lineas_registros = '<tbody>';
        $i = 1;
        foreach ($lineas_registros_documento as $linea) {

            $cuerpo_tabla_lineas_registros .= '<tr class="linea_registro" data-numero_linea="' . $i . '"><td style="display: none;"><div class="inv_producto_id">' . $linea->inv_producto_id . '</div></td><td style="display: none;"><div class="precio_unitario">' . $linea->precio_unitario . '</div></td><td style="display: none;"><div class="base_impuesto">' . $linea->base_impuesto . '</div></td><td style="display: none;"><div class="tasa_impuesto">' . $linea->tasa_impuesto . '</div></td><td style="display: none;"><div class="valor_impuesto">' . $linea->valor_impuesto . '</div></td><td style="display: none;"><div class="base_impuesto_total">' . $linea->base_impuesto_total . '</div></td><td style="display: none;"><div class="cantidad">' . $linea->cantidad . '</div></td><td style="display: none;"><div class="precio_total">' . $linea->precio_total . '</div></td><td style="display: none;"><div class="tasa_descuento">' . $linea->tasa_descuento . '</div></td><td style="display: none;"><div class="valor_total_descuento">' . $linea->valor_total_descuento . '</div></td><td> &nbsp; </td><td> <span style="background-color:#F7B2A3;">' . $linea->inv_producto_id . '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' . $linea->item->descripcion . ' </div> </td><td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' . $linea->cantidad . '</div> </div>  (<div class="lbl_producto_unidad_medida" style="display: inline;">' . $linea->item->unidad_medida1 . '</div>) </td><td>  <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' . $linea->precio_unitario . '</div></div></td><td>' . $linea->tasa_descuento . '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' . number_format($linea->valor_total_descuento, '0', ',', '.') . '</div> ) </td><td><div class="lbl_tasa_impuesto" style="display: inline;">' . $linea->tasa_impuesto . '%</div></td><td> <div class="lbl_precio_total" style="display: inline;">$ ' . number_format($linea->precio_total, '0', ',', '.') . ' </div> </td> <td><button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button></td></tr>';
            $i++;
        }

        $cuerpo_tabla_lineas_registros .= '</tbody>';

        return $cuerpo_tabla_lineas_registros;
    }

    public function armar_cuerpo_tabla_medios_recaudos($doc_encabezado)
    {
        $cuerpo_tabla = '';
        $lineas_recaudos = json_decode($doc_encabezado->lineas_registros_medios_recaudos);

        if (!is_null($lineas_recaudos)) {
            foreach ($lineas_recaudos as $linea) {
                $medio_recaudo = explode('-', $linea->teso_medio_recaudo_id);
                $motivo = explode('-', $linea->teso_motivo_id);
                $caja = explode('-', $linea->teso_caja_id);
                $cuenta_bancaria = explode('-', $linea->teso_cuenta_bancaria_id);

                $cuerpo_tabla .= '<tr> <td> <span style="color:white;">' . $medio_recaudo[0] . '-</span><span>' . $medio_recaudo[1] . '</span></td>' .
                    '<td><span style="color:white;">' . $motivo[0] . '-</span><span>' . $motivo[1] . '</span></td>' .
                    '<td><span style="color:white;">' . $caja[0] . '-</span><span>' . $caja[1] . '</span></td>' .
                    '<td><span style="color:white;">' . $cuenta_bancaria[0] . '-</span><span>' . $cuenta_bancaria[1] . '</span></td>' .
                    '<td class="valor_total">' . $linea->valor . '</td>' .
                    '<td> <button type="button" class="btn btn-danger btn-xs btn_eliminar_linea_medio_recaudo"><i class="fa fa-btn fa-trash"></i></button> </td> </tr>';
            }
        }

        return $cuerpo_tabla;
    }

    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista($id, $ruta_vista)
    {
        $this->set_variables_globales();

        $this->doc_encabezado = app($this->transaccion->modelo_encabezados_documentos)->get_registro_impresion($id);

        $doc_registros = app($this->transaccion->modelo_registros_documentos)->get_registros_impresion($this->doc_encabezado->id);

        $doc_encabezado = $this->doc_encabezado;
        $empresa = $this->empresa;

        $pdv_descripcion = '';
        $tipo_doc_app = '';
        if ( $doc_encabezado->pdv != null )
        {
            $tipo_doc_app = $doc_encabezado->pdv->tipo_doc_app;
            if ( $doc_encabezado->pdv->direccion != '' )
            {
                $empresa->direccion1 = $doc_encabezado->pdv->direccion;
                $empresa->telefono1 = $doc_encabezado->pdv->telefono;
                $empresa->email = $doc_encabezado->pdv->email;
            }
        }

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)->where('estado', 'Activo')->get()->last();

        $etiquetas = $this->get_etiquetas();
            
        $datos_factura = (object)[
            'core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id,
            'lbl_consecutivo_doc_encabezado' => $doc_encabezado->consecutivo,
            'lbl_fecha' => $doc_encabezado->fecha,
            'lbl_hora' => '',
            'lbl_condicion_pago' => $doc_encabezado->condicion_pago,
            'lbl_fecha_vencimiento' => $doc_encabezado->fecha_vencimiento,
            'lbl_descripcion_doc_encabezado' => $doc_encabezado->descripcion,
            'lbl_total_factura' => '$' . number_format($doc_encabezado->valor_total,2,',','.'),
            'lbl_ajuste_al_peso' => '',
            'lbl_total_recibido' => '0',
            'lbl_total_cambio' => '',
            'lbl_creado_por_fecha_y_hora' => $doc_encabezado->created_at,
            'lineas_registros' => View::make( 'ventas.formatos_impresion.cuerpo_tabla_lineas_registros', compact('doc_registros') )->render(),
            'lineas_impuesto' => View::make( 'ventas.formatos_impresion.tabla_lineas_impuestos', compact('doc_registros') )->render()
        ];
    
        $cliente = $doc_encabezado->cliente;


        return View::make($ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'tipo_doc_app', 'cliente', 'pdv_descripcion', 'datos_factura'))->render();
    }

    public function generar_plantilla_factura($pdv)
    {
        $this->set_variables_globales();

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();

        $empresa = $this->empresa;
        if ( $pdv->direccion != '' )
        {
            $empresa->direccion1 = $pdv->direccion;
            $empresa->telefono1 = $pdv->telefono;
            $empresa->email = $pdv->email;
        }

        $etiquetas = $this->get_etiquetas();

        $plantilla_factura_pos_default = config('ventas_pos.plantilla_factura_pos_default');
        if ($pdv->plantilla_factura_pos_default != null && $pdv->plantilla_factura_pos_default != '') {
            $plantilla_factura_pos_default = $pdv->plantilla_factura_pos_default;
        }

        $datos_factura = (object)[
            'core_tipo_transaccion_id' => '',
            'lbl_consecutivo_doc_encabezado' => '',
            'lbl_fecha' => '',
            'lbl_hora' => '',
            'lbl_condicion_pago' => '',
            'lbl_fecha_vencimiento' => '',
            'lbl_descripcion_doc_encabezado' => '',
            'lbl_total_factura' => '',
            'lbl_ajuste_al_peso' => '',
            'lbl_total_recibido' => '0',
            'lbl_total_cambio' => '',
            'lbl_creado_por_fecha_y_hora' => '',
            'lineas_registros' => '',
            'lineas_impuesto' => ''
        ];

        $cliente = $pdv->cliente;
        $tipo_doc_app = $pdv->tipo_doc_app;
        $pdv_descripcion = $pdv->descripcion;

        return View::make('ventas_pos.formatos_impresion.' . $plantilla_factura_pos_default, compact('empresa', 'resolucion', 'etiquetas', 'pdv_descripcion', 'cliente', 'tipo_doc_app', 'plantilla_factura_pos_default','datos_factura'))->render();
    }

    public function get_total_factura_from_arr_lineas_registros($lineas_registros)
    {
        $total_factura = 0;
        foreach ($lineas_registros as $linea) {
            $total_factura += (float)$linea->precio_total;
        }
        return $total_factura;
    }

    public function get_todos_los_pedidos_mesero_para_la_mesa($pedido)
    {
        return VtasPedido::where(
                            [
                                ['cliente_id','=',$pedido->cliente_id],
                                ['vendedor_id','=',$pedido->vendedor_id],
                                ['estado','=','Pendiente']
                            ]
                        )
                ->get();
    }

    // $request es actualizado por referencia
    public function actualizar_campo_lineas_registros_medios_recaudos_request(&$request_2,$total_factura)
    {
        $lineas_registros_medios_recaudos = json_decode($request_2->lineas_registros_medios_recaudos, true); // true convierte en un array asociativo al JSON

        array_pop($lineas_registros_medios_recaudos); // eliminar ultimo elemento del array

        $medios_recaudos = json_encode($lineas_registros_medios_recaudos);

        if ($medios_recaudos == "[]") // Si no se envian medios de pago, se utiliza efectivo por defecto
        {
            $pdv = Pdv::find($request_2->pdv_id);

            $request_2['lineas_registros_medios_recaudos'] = '[{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"' . $pdv->caja_default_id . '-' . $pdv->caja->descripcion . '","teso_cuenta_bancaria_id":"0-","valor":"$' . $total_factura . '"}]';
        } else {           
            $request_2['lineas_registros_medios_recaudos'] = $medios_recaudos;
        }
    }

    /*
        Crea los registros de un documento.
        No Devuelve nada.
    */
    public static function crear_registros_documento(Request $request, $doc_encabezado, array $lineas_registros)
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);

        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            if ( (int)$lineas_registros[$i]->inv_producto_id == 0)
            {
                continue; // Evitar guardar registros con productos NO validos
            }
            
            $linea_datos = ['vtas_motivo_id' => (int)$request->inv_motivo_id] +
                            ['inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id] +
                            ['precio_unitario' => (float)$lineas_registros[$i]->precio_unitario] +
                            ['cantidad' => (float)$lineas_registros[$i]->cantidad] +
                            ['precio_total' => (float)$lineas_registros[$i]->precio_total] +
                            ['base_impuesto' => (float)$lineas_registros[$i]->base_impuesto] +
                            ['tasa_impuesto' => (float)$lineas_registros[$i]->tasa_impuesto] +
                            ['valor_impuesto' => (float)$lineas_registros[$i]->valor_impuesto] +
                            ['base_impuesto_total' => (float)$lineas_registros[$i]->base_impuesto_total] +
                            ['tasa_descuento' => (float)$lineas_registros[$i]->tasa_descuento] +
                            ['valor_total_descuento' => (float)$lineas_registros[$i]->valor_total_descuento] +
                            ['creado_por' => Auth::user()->email] +
                            ['estado' => 'Pendiente'] +
                            ['vtas_pos_doc_encabezado_id' => $doc_encabezado->id];

            DocRegistro::create($linea_datos);

            $datos['consecutivo'] = $doc_encabezado->consecutivo;

            Movimiento::create(
                                $datos +
                                $linea_datos
                            );

            $total_documento += (float)$lineas_registros[$i]->precio_total;
        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();

        return 0;
    }

    public function get_parametros_complemento_JSPrintManager($pdv)
    {
        $usar_complemento_JSPrintManager = 0;

        if ($pdv->usar_complemento_JSPrintManager != null) {
            $usar_complemento_JSPrintManager = $pdv->usar_complemento_JSPrintManager;
        }

        return (object)[
            'usar_complemento_JSPrintManager' => $usar_complemento_JSPrintManager,
            'enviar_impresion_directamente_a_la_impresora' => $pdv->enviar_impresion_directamente_a_la_impresora,
            'impresora_principal_por_defecto' => $pdv->impresora_principal_por_defecto,
            'impresora_cocina_por_defecto' => $pdv->impresora_cocina_por_defecto,
        ];
    }

    public function get_productos($pdv,$productos)
    {
        $items_en_lista_precios = ListaPrecioDetalle::where('lista_precios_id',$pdv->cliente->lista_precios_id)->get()->pluck('inv_producto_id')->toArray();

        $productosTemp = null;
        foreach ($productos as $pr)
        {
            $grupo_inventario = InvGrupo::find($pr->inv_grupo_id);

            if (!$grupo_inventario->mostrar_en_pagina_web) {
                continue;
            }

            if ((int)config('ventas_pos.mostrar_solo_items_con_precios_en_lista_cliente_default')) {
                if (!in_array($pr->id,$items_en_lista_precios)) {
                    continue;
                }
            }            
            
            if ( is_null($grupo_inventario) )
            {
                return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', 'El producto ' . $pr->descripcion . ' no tiene un grupo de inventario válido.' );
            }

            $pr->categoria = $grupo_inventario->descripcion;
            $productosTemp[$pr->categoria][] = $pr;
        }

        return $productosTemp;
    }

    public function ajustar_campos($lista_campos,$pdv,$vendedor)
    {
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($this->transaccion->id, $this->transaccion, $lista_campos, $cantidad_campos, 'create', null);

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantidad_campos; $i++)
        {
            switch ($lista_campos[$i]['name']) {

                case 'core_tipo_doc_app_id':
                    $lista_campos[$i]['opciones'] = [$pdv->tipo_doc_app_default_id => $pdv->tipo_doc_app->prefijo . " - " . $pdv->tipo_doc_app->descripcion];
                    break;

                case 'cliente_input':
                    $lista_campos[$i]['value'] = $pdv->cliente->tercero->descripcion;
                    break;

                case 'vendedor_id':
                    $lista_campos[$i]['value'] = [$vendedor->id];
                    break;

                case 'forma_pago':
                    $lista_campos[$i]['value'] = $pdv->cliente->forma_pago( date('Y-m-d') );
                    break;

                case 'fecha':
                    $lista_campos[$i]['value'] = $pdv->ultima_fecha_apertura();
                    break;

                case 'fecha_vencimiento':
                    $lista_campos[$i]['value'] = $pdv->cliente->fecha_vencimiento_pago( $pdv->ultima_fecha_apertura() );
                    break;

                case 'inv_bodega_id':
                    $lista_campos[$i]['opciones'] = [$pdv->bodega_default_id => $pdv->bodega->descripcion];
                    break;
                            
                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }

    public function verificar_datos_por_defecto( $pdv )
    {
        if ( is_null( $pdv->cliente ) ) {
            return 'El punto de ventas NO tiene asociado un Cliente por defecto.';
        }

        if ( is_null( $pdv->bodega ) ) {
            return 'El punto de ventas NO tiene asociada una Bodega por defecto.';
        }

        if ( is_null( $pdv->caja ) ) {
            return 'El punto de ventas NO tiene asociada una Caja por defecto.';
        }

        if ( is_null( $pdv->cajero ) ) {
            return 'El punto de ventas NO tiene asociado un Cajero por defecto.';
        }

        if ( is_null( $pdv->tipo_doc_app ) ) {
            return 'El punto de ventas NO tiene asociado un Tipo de documento por defecto.';
        }

        return 'ok';
    }

    public function get_msj_resolucion_facturacion( $pdv )
    {
        return (new ResolucionFacturacionService())->validate_resolucion_facturacion($pdv->tipo_doc_app, $pdv->core_empresa_id);
    }
}