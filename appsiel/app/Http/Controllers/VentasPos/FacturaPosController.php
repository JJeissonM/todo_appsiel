<?php

namespace App\Http\Controllers\VentasPos;

use App\Http\Controllers\Tesoreria\RecaudoController;
use Illuminate\Http\Request;

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
use App\Sistema\Services\ModeloService;
use App\Tesoreria\TesoCuentaBancaria;
use App\Ventas\Services\PrintServices;
use App\VentasPos\Services\AccountingServices;
use App\VentasPos\Services\CrudService;
use App\VentasPos\Services\DatafonoService;
use App\VentasPos\Services\RecipeServices;
use App\VentasPos\Services\TipService;
use App\VentasPos\Services\FacturaPosService;

class FacturaPosController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    public function create()
    {
        $pdv = Pdv::find(Input::get('pdv_id'));
        $factura_pos_service = new FacturaPosService();

        $validar = $factura_pos_service->verificar_datos_por_defecto( $pdv );
        if ( $validar != 'ok' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $validar );
        }

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

        /**
         * Validar resolución de Facturación
         */
        $msj_resolucion_facturacion = $factura_pos_service->get_msj_resolucion_facturacion( $pdv );        
        if ( $msj_resolucion_facturacion->status == 'error' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $msj_resolucion_facturacion->message );
        }
        $msj_resolucion_facturacion = $msj_resolucion_facturacion->message;

        /**
         * Asignar campos por defecto
         */
        $cliente = $pdv->cliente;
        $vendedor = $cliente->vendedor;

        $modelo_service = new ModeloService();
        
        $lista_campos = $modelo_service->get_campos_modelo($this->modelo, '', 'create');

        $lista_campos = $factura_pos_service->ajustar_campos( $lista_campos, $pdv, $vendedor, $this->transaccion);

        $fecha = date('Y-m-d');
        if(config('ventas_pos.asignar_fecha_apertura_a_facturas'))
        {
            $fecha = $pdv->ultima_fecha_apertura();
        }
        $fecha_vencimiento = $pdv->cliente->fecha_vencimiento_pago( $fecha );

        //$modelo_controller = new ModeloController;
        $acciones = $modelo_service->acciones_basicas_modelo($this->modelo, '');
        
        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $id_transaccion = 8; // 8 = Recaudo cartera
        $motivos = TesoMotivo::opciones_campo_select_tipo_transaccion('Recaudo cartera');
        $medios_recaudo = RecaudoController::get_medios_recaudo();

        if ( $user->hasRole('Cajero PDV') || $user->hasRole('Cajero Junior') ) {
            $cajas =  [ $pdv->caja->id => $pdv->caja->descripcion ];
        }else{
            $cajas =  TesoCaja::opciones_campo_select();
        }
        
        $cuentas_bancarias = TesoCuentaBancaria::opciones_campo_select();

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Punto de ventas: ' . $pdv->descripcion);

        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productos = $productos->sortBy('precio_venta');
        
        $productosTemp = $factura_pos_service->get_productos($pdv,$productos);
        
        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.componentes.tactil.lista_items', compact('productosTemp'))->render();
        }
        
        // Para visualizar el listado de productos
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $factura_pos_service->generar_plantilla_factura($pdv, $this->empresa);

        $pedido_id = 0;

        $lineas_registros = '<tbody></tbody>';

        $numero_linea = 1;

        $valor_subtotal = 0;
        $valor_descuento = 0;
        $valor_total_impuestos = 0;

        $valor_sub_total_factura = 0;
        $valor_lbl_propina = 0;
        $valor_lbl_datafono = 0;

        $valor_total_factura = 0;
        $total_efectivo_recibido = 0;

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $factura_pos_service->get_parametros_complemento_JSPrintManager($pdv);

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();
        
        $pdv_descripcion = $pdv->descripcion;
        $tipo_doc_app = $pdv->tipo_doc_app;

        $medios_pago = null;

        $resolucion_facturacion_electronica = $factura_pos_service->get_resolucion_facturacion_electronica();

        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'tabla', 'pdv', 'inv_motivo_id', 'contenido_modal', 'vista_categorias_productos', 'plantilla_factura', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cliente', 'pedido_id', 'lineas_registros', 'numero_linea','valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'total_efectivo_recibido', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion','msj_resolucion_facturacion', 'pdv_descripcion','tipo_doc_app', 'valor_sub_total_factura' , 'valor_lbl_propina', 'valor_lbl_datafono', 'medios_pago', 'resolucion_facturacion_electronica'));
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

        // Crear documento de Ventas
        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);

        if ((int)config('inventarios.manejar_platillos_con_contorno')) {
            $lineas_registros = (new RecipeServices)->cambiar_items_con_contornos($lineas_registros);
        }

        // Crear Registros del documento de ventas
        FacturaPosController::crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        if ( $request->pedido_id != 0) {
            $pedido = VtasPedido::find($request->pedido_id);
            if ( $pedido != null )
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
        }

        return response()->json( $this->build_json_doc_encabezado($doc_encabezado), 200);
    }    

    public function build_json_doc_encabezado($doc_encabezado)
    {
        return [
            'doc_encabezado_documento_transaccion_descripcion' => $doc_encabezado->tipo_documento_app->descripcion,
            'consecutivo' => $doc_encabezado->consecutivo,
            'doc_encabezado_documento_transaccion_prefijo_consecutivo' => $doc_encabezado->tipo_documento_app->prefijo . ' ' . $doc_encabezado->consecutivo,
            'doc_encabezado_fecha' => $doc_encabezado->fecha,
            'doc_encabezado_tercero_nombre_completo' => $doc_encabezado->cliente->tercero->descripcion,
            'doc_encabezado_vendedor_descripcion' => $doc_encabezado->vendedor->tercero->descripcion,
            'cantidad_total_productos' => count($doc_encabezado->lineas_registros),
            'doc_encabezado_descripcion' => $doc_encabezado->descripcion
        ];        
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

        if ($doc_encabezado->core_tipo_transaccion_id == 47 ) { // POS
            
            $registros_tesoreria = json_decode(str_replace("$", "", $doc_encabezado->lineas_registros_medios_recaudos));

            $medios_pago = View::make('ventas_pos.incluir.show_medios_pago', compact('registros_tesoreria'))->render();
        }else{
            $registros_tesoreria = TesoMovimiento::get_registros_un_documento( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo );
            $medios_pago = View::make('tesoreria.incluir.show_medios_pago', compact('registros_tesoreria'))->render();
        }       

        return view($vista, compact('id', 'botones_anterior_siguiente', 'miga_pan', 'documento_vista', 'doc_encabezado', 'registros_contabilidad', 'abonos', 'empresa', 'docs_relacionados', 'doc_registros', 'url_crear', 'id_transaccion', 'notas_credito','pedidos_padres', 'medios_pago'));
    }

    /*
        Imprimir
    */
    public function imprimir($id)
    {
        $print_service = new PrintServices();

        return $print_service->generar_documento_vista( $id, 'ventas.formatos_impresion.pos' );
    }

    /**
     * Prepara la vista para Editar una Factura POS
     */
    public function edit($id)
    {
        $this->set_variables_globales();

        // Se obtiene el registro a modificar del modelo
        $factura = app($this->modelo->name_space)->find($id); // Encabezado FActura POS

        $pdv = Pdv::find($factura->pdv_id);
        $user = Auth::user();

        $factura_pos_service = new FacturaPosService();

        $msj_resolucion_facturacion = $factura_pos_service->get_msj_resolucion_facturacion( $pdv );        
        if ( $msj_resolucion_facturacion->status == 'error' )
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $msj_resolucion_facturacion->message );
        }
        $msj_resolucion_facturacion = $msj_resolucion_facturacion->message;

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, $factura, 'edit');

        $doc_encabezado = FacturaPos::get_registro_impresion($id);

        $cantidad = count($lista_campos);

        $crud_serv = new CrudService();
        $lista_campos = $crud_serv->custom_fields_for_edit($lista_campos, $doc_encabezado, $pdv);

        $fecha = $doc_encabezado->fecha;
        $fecha_vencimiento = $doc_encabezado->fecha_vencimiento;

        $acciones = $this->acciones_basicas_modelo($this->modelo, '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'));

        $url_action = str_replace('id_fila', $factura->id, $acciones->update);

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

        if ( $user->hasRole('Cajero PDV') || $user->hasRole('Cajero Junior') ) {
            $cajas =  [ $pdv->caja->id => $pdv->caja->descripcion ];
        }else{
            $cajas =  TesoCaja::opciones_campo_select();
        }  

        $cuentas_bancarias = TesoCuentaBancaria::opciones_campo_select();

        $numero_linea = count($factura->lineas_registros) + 1;

        $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($factura->lineas_registros);
        
        $cuerpo_tabla_medios_recaudos = $this->armar_cuerpo_tabla_medios_recaudos($factura);

        $vista_medios_recaudo = View::make('tesoreria.incluir.medios_recaudos', compact('id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cuerpo_tabla_medios_recaudos'))->render();
        
        $total_efectivo_recibido = $this->get_total_campo_lineas_registros( json_decode(str_replace("$", "", $factura->lineas_registros_medios_recaudos) ), 'valor');
        
        //$total_efectivo_recibido = 0;
        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productos = $productos->sortBy('precio_venta');
        $productosTemp = $factura_pos_service->get_productos($pdv,$productos);

        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.componentes.tactil.lista_items', compact('productosTemp'))->render();
        }
        
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $factura_pos_service->generar_plantilla_factura($pdv, $this->empresa);

        $redondear_centena = config('ventas_pos.redondear_centena');
        
        $cliente = $factura->cliente;
        $vendedor = $factura->vendedor;

        $pedido_id = 0;

        $valor_subtotal = number_format($factura->lineas_registros->sum('base_impuesto_total') + $factura->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_descuento = number_format( $factura->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_total_impuestos = number_format( $factura->lineas_registros->sum('precio_total') - $factura->lineas_registros->sum('base_impuesto_total'),'2',',','.');

        // Para las propinas
        $valor_sub_total_factura = $factura->lineas_registros->sum('precio_total');
        $valor_lbl_propina = (new TipService())->get_tip_amount($factura);
        $valor_total_factura = $valor_sub_total_factura + $valor_lbl_propina;

        // Para Datafono
        $valor_sub_total_factura = $factura->lineas_registros->sum('precio_total');
        $valor_lbl_datafono = (new DatafonoService())->get_datafono_amount($factura);
        $valor_total_factura = $valor_sub_total_factura + $valor_lbl_datafono;

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $factura_pos_service->get_parametros_complemento_JSPrintManager($pdv);
        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();

        $medios_pago = null;

        $resolucion_facturacion_electronica = $factura_pos_service->get_resolucion_facturacion_electronica();
        
        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'factura', 'archivo_js', 'url_action', 'pdv', 'inv_motivo_id', 'tabla', 'productos', 'contenido_modal', 'plantilla_factura', 'redondear_centena', 'numero_linea', 'lineas_registros', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias', 'vista_medios_recaudo', 'total_efectivo_recibido','vista_categorias_productos','cliente', 'pedido_id', 'valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion', 'msj_resolucion_facturacion', 'valor_sub_total_factura', 'valor_lbl_propina', 'valor_lbl_datafono', 'medios_pago','resolucion_facturacion_electronica'));
    }

    /**
     * ACTUALIZA FACTURA POS
     *
     */
    public function update(Request $request, $id)
    {
        $lineas_registros = json_decode($request->lineas_registros);
        $total_factura = $this->get_total_factura_from_arr_lineas_registros($lineas_registros);

        if ((int)config('inventarios.manejar_platillos_con_contorno')) {
            $lineas_registros = (new RecipeServices)->cambiar_items_con_contornos($lineas_registros);
        }

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

        return response()->json( $this->build_json_doc_encabezado($doc_encabezado), 200);
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
        if( $pedido != null )
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

    public function borrar_propina($doc_encabezado_id)
    {
        $factura = FacturaPos::find($doc_encabezado_id);

        $lineas_recaudos = json_decode($factura->lineas_registros_medios_recaudos);

        $string_lineas_registros_medios_recaudos = '[';
        
        if ( !is_null($lineas_recaudos) )
        {
            $es_el_primero = true;

            foreach ($lineas_recaudos as $linea)
            {
                if ( (int)explode("-", $linea->teso_motivo_id)[0] == (int)config('ventas_pos.motivo_tesoreria_propinas') ) {
                    continue;
                };
                
                if ($es_el_primero) {
                    $string_lineas_registros_medios_recaudos .= '{"teso_medio_recaudo_id":"' . $linea->teso_medio_recaudo_id . '","teso_motivo_id":"' . $linea->teso_motivo_id . '","teso_caja_id":"' . $linea->teso_caja_id . '","teso_cuenta_bancaria_id":"' . $linea->teso_cuenta_bancaria_id . '","valor":"' . $linea->valor . '"}';
                    $es_el_primero = false;
                }else{
                    $string_lineas_registros_medios_recaudos .= ',{"teso_medio_recaudo_id":"' . $linea->teso_medio_recaudo_id . '","teso_motivo_id":"' . $linea->teso_motivo_id . '","teso_caja_id":"' . $linea->teso_caja_id . '","teso_cuenta_bancaria_id":"' . $linea->teso_cuenta_bancaria_id . '","valor":"' . $linea->valor . '"}';
                }
            }
        }

        $string_lineas_registros_medios_recaudos .= ']';

        $modificado_por = Auth::user()->email;

        $factura->update([
            'lineas_registros_medios_recaudos' => $string_lineas_registros_medios_recaudos, 
            'modificado_por' => $modificado_por]
        );

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
        if( $pedido != null )
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
        // El costo promedio del item se llama desde inv_productos
        $pdv = Pdv::find( $pdv_id );
        $datos = [
                    'redondear_centena' => config('ventas_pos.redondear_centena'),
                    'productos' => InvProducto::get_datos_basicos('', 'Activo', null, null),
                    'precios' => ListaPrecioDetalle::get_precios_productos_de_la_lista( $pdv->cliente->lista_precios_id ),
                    'descuentos' => ListaDctoDetalle::get_descuentos_productos_de_la_lista( null ),
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

        if ((new AccountingServices())->recontabilizar_factura( $documento_id )) {
            return redirect( 'pos_factura/' . $documento_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('flash_message', 'Documento Recontabilizado.');
        }
        
    }

    public function armar_cuerpo_tabla_lineas_registros($lineas_registros_documento)
    {
        $cuerpo_tabla_lineas_registros = '<tbody>';
        $i = 1;
        foreach ($lineas_registros_documento as $linea) {
            
            if ( $linea->item == null) {
                continue;
            }
            
            $cuerpo_tabla_lineas_registros .= '<tr class="linea_registro" data-numero_linea="' . $i . '"><td style="display: none;"><div class="inv_producto_id">' . $linea->inv_producto_id . '</div></td><td style="display: none;"><div class="precio_unitario">' . $linea->precio_unitario . '</div></td><td style="display: none;"><div class="base_impuesto">' . $linea->base_impuesto . '</div></td><td style="display: none;"><div class="tasa_impuesto">' . $linea->tasa_impuesto . '</div></td><td style="display: none;"><div class="valor_impuesto">' . $linea->valor_impuesto . '</div></td><td style="display: none;"><div class="base_impuesto_total">' . $linea->base_impuesto_total . '</div></td><td style="display: none;"><div class="cantidad">' . $linea->cantidad . '</div></td><td style="display: none;"><div class="precio_total">' . $linea->precio_total . '</div></td><td style="display: none;"><div class="tasa_descuento">' . $linea->tasa_descuento . '</div></td><td style="display: none;"><div class="valor_total_descuento">' . $linea->valor_total_descuento . '</div></td><td> &nbsp; </td><td> <span style="background-color:#F7B2A3;">' . $linea->inv_producto_id . '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' . $linea->item->descripcion . ' </div> </td><td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' . $linea->cantidad . '</div> </div>  (<div class="lbl_producto_unidad_medida" style="display: inline;">' . $linea->item->unidad_medida1 . '</div>) </td><td>  <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar." id="elemento_modificar_precio_unitario"> ' . $linea->precio_unitario . '</div></div></td><td>' . $linea->tasa_descuento . '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' . number_format($linea->valor_total_descuento, '0', ',', '.') . '</div> ) </td><td><div class="lbl_tasa_impuesto" style="display: inline;">' . $linea->tasa_impuesto . '%</div></td><td> <div class="lbl_precio_total" style="display: inline;">$ ' . number_format($linea->precio_total, '0', ',', '.') . ' </div> </td> <td><button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button></td></tr>';
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

                if (!isset($medio_recaudo[0]) || !isset($motivo[0]) || !isset($caja[0]) || !isset($cuenta_bancaria[0])) {
                    continue;
                }
                
                if (!isset($medio_recaudo[1]) || !isset($motivo[1]) || !isset($caja[1]) || !isset($cuenta_bancaria[1])) {
                    continue;
                }

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
}