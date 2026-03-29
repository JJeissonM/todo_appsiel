<?php

namespace App\Http\Controllers\VentasPos;

use App\Http\Requests\VentasPos\StoreFacturaPosRequest;
use App\Http\Requests\VentasPos\UpdateFacturaPosRequest;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

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

use App\Ventas\ResolucionFacturacion;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\NotaCredito;

use App\Ventas\VtasMovimiento;

use App\CxC\CxcMovimiento;
use App\CxC\CxcAbono;

use App\CxP\CxpMovimiento;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoMotivo;

use App\Sistema\Services\ModeloService;
use App\Ventas\Services\CxCServices;
use App\Ventas\Services\PrintServices;
use App\VentasPos\Services\AccountingServices;
use App\VentasPos\Services\CrudService;
use App\VentasPos\Services\CxCService;
use App\VentasPos\Services\DatafonoService;
use App\VentasPos\Services\RecipeServices;
use App\VentasPos\Services\TipService;
use App\VentasPos\Services\FacturaPosService;
use App\VentasPos\Services\InvoicingService;
use App\VentasPos\Services\PosPaymentModalService;
use App\VentasPos\Services\TreasuryService;

class FacturaPosController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    public function create()
    {
        $user = Auth::user(); // Solo para verificar que la sesión esté activa.

        if($user == null)
        {
            return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', 'La sesión se cerró inesperadamente. Por favor, ingrese nuevamente su usuario y contraseña.' );
        }

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
        }else{
            $tabla_dibujada = $tabla->dibujar();
        }
        
        /**
         * Validar resolución de Facturación
         */
        $msj_resolucion_facturacion = '';
        if( (int)config('ventas_pos.modulo_fe_activo') )
        {
            $msj_resolucion_facturacion = $factura_pos_service->get_msj_resolucion_facturacion( $pdv );
        
            if ( $msj_resolucion_facturacion->status == 'error' )
            {
                return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $msj_resolucion_facturacion->message );
            }
            $msj_resolucion_facturacion = $msj_resolucion_facturacion->message;
        }

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
        $motivos =  $factura_pos_service->get_motivos_tesoreria();
        $payment_modal_service = new PosPaymentModalService();
        $payment_modal_data = $payment_modal_service->buildData();
        $medios_recaudo = $payment_modal_data['medios_recaudo'];
        $cajas = $payment_modal_data['cajas'];
        $cuentas_bancarias = $payment_modal_data['cuentas_bancarias'];
        $usar_modal_botones_medios_pago = $payment_modal_data['usar_modal_botones'];
        $modal_botones_medios_pago_data = $payment_modal_data['modal_botones_data'];

        $miga_pan = [
                  [ 
                    'url' => $this->app->app.'?id='.$this->app->id,
                    'etiqueta' => $this->app->descripcion
                    ],
                  [ 
                    'url' => 'NO',
                    'etiqueta' => 'Punto de ventas: ' . $pdv->descripcion
                    ]
                ];

        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productos = $productos->sortBy('precio_venta');
        
        $productosTemp = $factura_pos_service->get_productos($pdv,$productos);
        
        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.componentes.tactil.lista_items', compact('productosTemp'))->render();
        }
        
        // Para visualizar el listado de productos (Lupa)
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $factura_pos_service->generar_plantilla_factura($pdv, $this->empresa);

        if ( $pdv->maneja_impoconsumo ) {
            $tabla_dibujada = str_replace('IVA','INC',$tabla_dibujada);
            $plantilla_factura = str_replace('IVA','INC',$plantilla_factura);
        }

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
        $valor_ajuste_al_peso = 0;
        $valor_total_bolsas = 0;
        $valor_total_cambio = 0;

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $factura_pos_service->get_parametros_complemento_JSPrintManager($pdv);

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();
        
        $pdv_descripcion = $pdv->descripcion;
        $tipo_doc_app = $pdv->tipo_doc_app;

        $medios_pago = null;

        $resolucion_facturacion_electronica = $factura_pos_service->get_resolucion_facturacion_electronica();

        $precio_bolsa = $factura_pos_service->get_precio_bolsa($pdv->cliente->lista_precios_id);

        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'tabla_dibujada', 'pdv', 'inv_motivo_id', 'contenido_modal', 'vista_categorias_productos', 'plantilla_factura', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cliente', 'pedido_id', 'lineas_registros', 'numero_linea','valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'total_efectivo_recibido', 'valor_ajuste_al_peso', 'valor_total_cambio', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion','msj_resolucion_facturacion', 'pdv_descripcion','tipo_doc_app', 'valor_sub_total_factura' , 'valor_lbl_propina', 'valor_lbl_datafono', 'medios_pago', 'resolucion_facturacion_electronica', 'precio_bolsa', 'valor_total_bolsas', 'usar_modal_botones_medios_pago', 'modal_botones_medios_pago_data'));
    }

    /**
     * ALMACENA FACTURA POS - ES LLAMADO VÍA AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $email = Auth::user()->email; // Solo para verificar que la sesión esté activa. Si se cerró la sesión, Laravel lanza una excepción

        $this->aplicar_excedente_transferencia_como_otros_recaudos($request);

        $request_id = $this->get_request_id_from_request($request);
        $request_uniqid = (string)$request->get('uniqid', '');
        $log_context = $this->build_pos_log_context($request, $request_id, $request_uniqid);

        if ( $request_uniqid != '' )
        {
            $doc_existente = $this->find_existing_doc_by_uniqid($request_uniqid, $request);
            if ( $doc_existente != null ) {
                $response = $this->build_json_doc_encabezado($doc_existente);
                $response['reused_uniqid'] = 1;
                $response['request_id'] = $request_id;
                Log::warning('POS_SAVE_REUSED_UNIQID', $log_context + [ 'doc_id' => $doc_existente->id ]);
                return response()->json( $response, 200);
            }
        }

        $lineas_registros = json_decode($request->lineas_registros);

        $acumular_factura = false;
        if ((int)config('ventas_pos.acumular_facturas_en_tiempo_real') ) {
            $acumular_factura = true;
        }

        $crear_cruce_con_anticipos = false;
        $crear_abonos = false; // Cuando es credito y se ingresa alguna línea de Medio de pago
        if ( $request->object_anticipos != 'null' && $request->object_anticipos != '' )
        {
            $request['forma_pago'] = 'credito'; // Si hay anticipos, se asume que es crédito

            $acumular_factura = true;
            $crear_cruce_con_anticipos = true; // Si hay anticipos, se crea el cruce con los anticipos
            $crear_abonos = true; // Si hay anticipos, se crean los abonos
        }

        $todos_los_pedidos = collect([]);

        DB::beginTransaction();
        try {
            if ((int)$request->pedido_id != 0) {
                $pedido = VtasPedido::where('id', (int)$request->pedido_id)->lockForUpdate()->first();

                if ( is_null($pedido) || $pedido->estado != 'Pendiente' || (int)$pedido->ventas_doc_relacionado_id != 0 ) {
                    DB::rollBack();
                    Log::warning('POS_SAVE_PEDIDO_INVALID', $log_context + [ 'pedido_id' => (int)$request->pedido_id ]);
                    return response()->json([
                        'status' => 'warning',
                        'message' => 'El pedido ya no esta disponible para facturar.',
                        'request_id' => $request_id
                    ], 409);
                }

                if ((int)config('ventas_pos.agrupar_pedidos_por_cliente') == 1) {
                    $todos_los_pedidos = VtasPedido::where('cliente_id', $pedido->cliente_id)
                        ->where('estado', 'Pendiente')
                        ->where('ventas_doc_relacionado_id', 0)
                        ->whereIn('core_tipo_transaccion_id', [42, 60])
                        ->lockForUpdate()
                        ->get();

                    if ($todos_los_pedidos->isEmpty()) {
                        DB::rollBack();
                        Log::warning('POS_SAVE_PEDIDOS_EMPTY', $log_context + [ 'pedido_id' => (int)$request->pedido_id ]);
                        return response()->json([
                            'status' => 'warning',
                            'message' => 'No hay pedidos pendientes para facturar en este cliente.',
                            'request_id' => $request_id
                        ], 409);
                    }
                } else {
                    $todos_los_pedidos = collect([$pedido]);
                }
            }

            // Crear documento de Ventas
            try {
                $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);
            } catch (QueryException $e) {
                $is_duplicate_uniqid = ($e->getCode() == '23000') && (strpos($e->getMessage(), "for key 'uniqid'") !== false);
                $is_duplicate_uniqid = $is_duplicate_uniqid || (($e->getCode() == '23000') && (strpos($e->getMessage(), 'Duplicate entry') !== false) && (strpos($e->getMessage(), 'uniqid') !== false));

                if ( $is_duplicate_uniqid && $request_uniqid != '' )
                {
                    $doc_existente = $this->find_existing_doc_by_uniqid($request_uniqid, $request);
                    if ( $doc_existente != null ) {
                        DB::rollBack();
                        $response = $this->build_json_doc_encabezado($doc_existente);
                        $response['reused_uniqid'] = 1;
                        $response['request_id'] = $request_id;
                        Log::warning('POS_SAVE_DUPLICATE_RECOVERED', $log_context + [ 'doc_id' => $doc_existente->id ]);
                        return response()->json( $response, 200);
                    }
                }

                throw $e;
            }

            if ((int)config('inventarios.manejar_platillos_con_contorno')) {
                $lineas_registros = (new RecipeServices)->cambiar_items_con_contornos($lineas_registros);
            }

            // Crear Registros del documento de ventas
            FacturaPosController::crear_registros_documento($request, $doc_encabezado, $lineas_registros);

            if ( $request->pedido_id != 0) {
                foreach ($todos_los_pedidos as $un_pedido) {
                    $un_pedido->ventas_doc_relacionado_id = $doc_encabezado->id;
                    $un_pedido->estado = 'Facturado';
                    $this->guardar_pedido_sin_tocar_updated_at($un_pedido);

                    self::actualizar_cantidades_pendientes( $un_pedido, 'restar' );
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('POS_SAVE_EXCEPTION', $log_context + [ 'error' => $e->getMessage() ]);
            throw $e;
        }

        if($acumular_factura)
        {
            $obj_acumm_serv = new AccumulationService( $doc_encabezado->pdv_id );

            // Realizar preparaciones de recetas
            $obj_acumm_serv->hacer_preparaciones_recetas( 'Creado por factura POS ' . $doc_encabezado->get_label_documento(), $doc_encabezado->fecha);

            // Realizar desarme automático
            $obj_acumm_serv->hacer_desarme_automatico( 'Creado por factura POS ' . $doc_encabezado->get_label_documento(), $doc_encabezado->fecha);

            $obj_acumm_serv->accumulate_one_invoice( $doc_encabezado->id );
        }

        if( $crear_cruce_con_anticipos )
        {
            (new CxCService())->crear_cruce_con_anticipos( $doc_encabezado, $request->object_anticipos );
        }

        if ( $crear_abonos) {
            $datos = $doc_encabezado->toArray();
            (new TreasuryService())->crear_abonos_documento( $doc_encabezado, $datos['lineas_registros_medios_recaudos'] );
        }

        $response = $this->build_json_doc_encabezado($doc_encabezado);
        $response['request_id'] = $request_id;
        Log::info('POS_SAVE_SUCCESS', $log_context + [ 'doc_id' => $doc_encabezado->id, 'consecutivo' => $doc_encabezado->consecutivo ]);
        return response()->json( $response, 200);
    }

    /**
     * 
     */
    public function get_doc_encabezado_por_uniqid( $uniqid )
    {
        $query = FacturaPos::where('uniqid', $uniqid);
        if ( Auth::check() ) {
            $query->where('core_empresa_id', Auth::user()->empresa_id);
        }
        $doc_encabezado = $query->get()->first();

        if ( $doc_encabezado == null ) {
            return 'null'; // No existe. Todo Bien.
        }

        $response = $this->build_json_doc_encabezado($doc_encabezado);
        $response['request_id'] = $this->get_request_id_from_request();
        return response()->json( $response, 200);
    }

    /**
     * 
     */
    public function build_json_doc_encabezado($doc_encabezado)
    {
        $empresa = $doc_encabezado->empresa;
        $pdv = $doc_encabezado->pdv;

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();

        $arr_resolucion = [];
        if ( $resolucion != null ) {
            $arr_resolucion = $resolucion->toArray();
        }

        if ( $pdv->direccion != '' )
        {
            $empresa->direccion1 = $pdv->direccion;
            $empresa->telefono1 = $pdv->telefono;
            $empresa->email = $pdv->email;
        }

        $empresa->url_logo = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/logos_empresas/' . $empresa->imagen;

        $etiquetas = (new PrintServices())->get_etiquetas();

        return [
            'id' => $doc_encabezado->id,
            'doc_encabezado_documento_transaccion_descripcion' => $doc_encabezado->tipo_documento_app->descripcion,
            'consecutivo' => $doc_encabezado->consecutivo,
            'doc_encabezado_documento_transaccion_prefijo_consecutivo' => $doc_encabezado->tipo_documento_app->prefijo . ' ' . $doc_encabezado->consecutivo,
            'doc_encabezado_fecha' => $doc_encabezado->fecha,
            'doc_encabezado_forma_pago' => $doc_encabezado->forma_pago,
            'doc_encabezado_valor_total' => (float)$doc_encabezado->valor_total,
            'doc_encabezado_tercero_nombre_completo' => $doc_encabezado->cliente->tercero->descripcion,
            'doc_encabezado_vendedor_descripcion' => $doc_encabezado->vendedor->tercero->descripcion,
            'cantidad_total_productos' => count($doc_encabezado->lineas_registros),
            'doc_encabezado_descripcion' => $doc_encabezado->descripcion,
            'empresa' => array_merge($empresa->toArray(),[
                'descripcion_tipo_documento_identidad' => $empresa->tipo_doc_identidad->abreviatura,
                'descripcion_ciudad' => $empresa->ciudad->descripcion,
            ]),
            'etiquetas' => $etiquetas,
            'resolucion' => $arr_resolucion,
            'cliente_info' => array_merge( $doc_encabezado->cliente->tercero->toArray(), [
                'descripcion_tipo_documento_identidad' => $doc_encabezado->cliente->tercero->tipo_doc_identidad->abreviatura,
                'descripcion_ciudad' => $doc_encabezado->cliente->tercero->ciudad->descripcion,
            ]),
            'saldo_pendiente_cxc' => (new CxCServices())->get_movimiento_documentos_pendientes_fecha_corte($doc_encabezado->cliente->core_tercero_id, $doc_encabezado->fecha),
            'lbl_creado_por_fecha_y_hora' => explode('@', $doc_encabezado->creado_por)[0]
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

        $factura_pos_service = new FacturaPosService();
        
        $msj_resolucion_facturacion = '';
        if( (int)config('ventas_pos.modulo_fe_activo') )
        {
            $msj_resolucion_facturacion = $factura_pos_service->get_msj_resolucion_facturacion( $pdv );        
            if ( $msj_resolucion_facturacion->status == 'error' )
            {
                return redirect( 'ventas_pos?id=' . Input::get('id') )->with('mensaje_error', $msj_resolucion_facturacion->message );
            }
            $msj_resolucion_facturacion = $msj_resolucion_facturacion->message;
        }

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
        }else{
            $tabla_dibujada = $tabla->dibujar();
        }

        $id_transaccion = 8; // 8 = Recaudo cartera
        $motivos =  $factura_pos_service->get_motivos_tesoreria();
        $payment_modal_service = new PosPaymentModalService();
        $payment_modal_data = $payment_modal_service->buildData();
        $medios_recaudo = $payment_modal_data['medios_recaudo'];
        $cajas = $payment_modal_data['cajas'];
        $cuentas_bancarias = $payment_modal_data['cuentas_bancarias'];
        $usar_modal_botones_medios_pago = $payment_modal_data['usar_modal_botones'];
        $modal_botones_medios_pago_data = $payment_modal_data['modal_botones_data'];

        $numero_linea = count($factura->lineas_registros) + 1;

        $lineas_registros = $this->armar_cuerpo_tabla_lineas_registros($factura->lineas_registros);
        
        $cuerpo_tabla_medios_recaudos = '';
        if ($factura->forma_pago != 'credito') {
            $cuerpo_tabla_medios_recaudos = $this->armar_cuerpo_tabla_medios_recaudos($factura);
        }

        $vista_medios_recaudo = View::make('tesoreria.incluir.medios_recaudos', compact('id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias','cuerpo_tabla_medios_recaudos', 'usar_modal_botones_medios_pago', 'modal_botones_medios_pago_data'))->render();
        
        $total_efectivo_recibido = $factura->total_efectivo_recibido;
        $valor_ajuste_al_peso = $factura->valor_ajuste_al_peso;
        $valor_total_cambio = $factura->valor_total_cambio;
        $valor_total_bolsas = $factura->valor_total_bolsas;
        
        $productos = InvProducto::get_datos_basicos('', 'Activo', null, $pdv->bodega_default_id);
        $productos = $productos->sortBy('precio_venta');
        $productosTemp = $factura_pos_service->get_productos($pdv,$productos);

        $vista_categorias_productos = '';
        if (config('ventas_pos.activar_ingreso_tactil_productos') == 1) {
            $vista_categorias_productos = View::make('ventas_pos.componentes.tactil.lista_items', compact('productosTemp'))->render();
        }
        
        $contenido_modal = View::make('ventas_pos.lista_items', compact('productos'))->render();

        $plantilla_factura = $factura_pos_service->generar_plantilla_factura($pdv, $this->empresa);        

        if ( $pdv->maneja_impoconsumo ) {
            $tabla_dibujada = str_replace('IVA','INC',$tabla_dibujada);
            $plantilla_factura = str_replace('IVA','INC',$plantilla_factura);
        }

        $redondear_centena = config('ventas_pos.redondear_centena');
        
        $cliente = $factura->cliente;
        $vendedor = $factura->vendedor;

        $pedido_id = 0;

        $valor_subtotal = number_format($factura->lineas_registros->sum('base_impuesto_total') + $factura->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_descuento = number_format( $factura->lineas_registros->sum('valor_total_descuento'),'2',',','.');

        $valor_total_impuestos = number_format( $factura->lineas_registros->sum('precio_total') - $factura->lineas_registros->sum('base_impuesto_total'),'2',',','.');

        $valor_sub_total_factura = $factura->lineas_registros->sum('precio_total');
        $valor_total_factura = $valor_sub_total_factura;

        // Para las propinas
        $valor_lbl_propina = (new TipService())->get_tip_amount($factura);
        $valor_total_factura += $valor_lbl_propina;

        // Para Datafono
        $valor_lbl_datafono = (new DatafonoService())->get_datafono_amount($factura);
        $valor_total_factura += $valor_lbl_datafono;

        $vendedores = Vendedor::where('estado','Activo')->get();

        $params_JSPrintManager = $factura_pos_service->get_parametros_complemento_JSPrintManager($pdv);
        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id', $pdv->tipo_doc_app_default_id)->where('estado', 'Activo')->get()->last();

        $medios_pago = null;

        $resolucion_facturacion_electronica = $factura_pos_service->get_resolucion_facturacion_electronica();

        $precio_bolsa = $factura_pos_service->get_precio_bolsa($pdv->cliente->lista_precios_id);

        return view('ventas_pos.crud_factura', compact('form_create', 'miga_pan', 'factura', 'archivo_js', 'url_action', 'pdv', 'inv_motivo_id', 'tabla_dibujada', 'productos', 'contenido_modal', 'plantilla_factura', 'redondear_centena', 'numero_linea', 'lineas_registros', 'id_transaccion', 'motivos', 'medios_recaudo', 'cajas', 'cuentas_bancarias', 'vista_medios_recaudo', 'total_efectivo_recibido','valor_ajuste_al_peso','valor_total_cambio','vista_categorias_productos','cliente', 'pedido_id', 'valor_subtotal', 'valor_descuento', 'valor_total_impuestos', 'valor_total_factura', 'vendedores','vendedor','fecha','fecha_vencimiento', 'params_JSPrintManager','resolucion', 'msj_resolucion_facturacion', 'valor_sub_total_factura', 'valor_lbl_propina', 'valor_lbl_datafono', 'medios_pago','resolucion_facturacion_electronica', 'precio_bolsa', 'valor_total_bolsas', 'usar_modal_botones_medios_pago', 'modal_botones_medios_pago_data'));
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

        $doc_encabezado = FacturaPos::with('lineas_registros')->find($id);

        $datos_antes = [
            'fecha' => $doc_encabezado->fecha,
            'descripcion' => $doc_encabezado->descripcion,
            'cliente_id' => (int)$doc_encabezado->cliente_id,
            'core_tercero_id' => (int)$doc_encabezado->core_tercero_id,
            'forma_pago' => $doc_encabezado->forma_pago,
            'fecha_vencimiento' => $doc_encabezado->fecha_vencimiento,
            'vendedor_id' => (int)$doc_encabezado->vendedor_id,
            'lineas_registros_medios_recaudos' => (string)$doc_encabezado->lineas_registros_medios_recaudos,
            'valor_total' => (float)$doc_encabezado->valor_total,
            'total_efectivo_recibido' => (float)$doc_encabezado->total_efectivo_recibido,
            'valor_ajuste_al_peso' => (float)$doc_encabezado->valor_ajuste_al_peso,
            'valor_total_bolsas' => (float)$doc_encabezado->valor_total_bolsas,
            'valor_total_cambio' => (float)$doc_encabezado->valor_total_cambio
        ];

        $lineas_antes = $doc_encabezado->lineas_registros->map(function ($linea) {
            return [
                'inv_producto_id' => (int)$linea->inv_producto_id,
                'cantidad' => (float)$linea->cantidad,
                'precio_unitario' => (float)$linea->precio_unitario,
                'precio_total' => (float)$linea->precio_total,
                'tasa_descuento' => (float)$linea->tasa_descuento,
                'tasa_impuesto' => (float)$linea->tasa_impuesto,
                'base_impuesto_total' => (float)$linea->base_impuesto_total
            ];
        })->values()->toArray();

        $doc_encabezado->fecha = $request->fecha;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->cliente_id = (int)$request->cliente_id;
        $doc_encabezado->core_tercero_id = (int)$request->core_tercero_id;
        $doc_encabezado->forma_pago = $request->forma_pago;
        $doc_encabezado->fecha_vencimiento = $request->fecha_vencimiento;
        $doc_encabezado->vendedor_id = $request->vendedor_id;
        $doc_encabezado->lineas_registros_medios_recaudos = $request->lineas_registros_medios_recaudos;
        $doc_encabezado->valor_total = $total_factura;
        $doc_encabezado->modificado_por = Auth::user()->email;
        $doc_encabezado->total_efectivo_recibido = $request->total_efectivo_recibido;
        $doc_encabezado->valor_ajuste_al_peso = $request->valor_ajuste_al_peso;
        $doc_encabezado->valor_total_bolsas = $request->valor_total_bolsas;
        $doc_encabezado->valor_total_cambio = $request->valor_total_cambio;
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

        if ($doc_encabezado->estado != 'Pendiente') {
            $result = (new AccountingServices())->reconstruir_movimientos_y_recontabilizar_factura($doc_encabezado->id);

            if ($result->status != 'success') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Factura actualizada, pero no se pudieron sincronizar movimientos: ' . $result->message
                ], 422);
            }
        }

        // Mantener consistencia de encabezado después de reconstrucción/recontabilización
        $doc_encabezado = FacturaPos::find($doc_encabezado->id);
        $doc_encabezado->fecha = $request->fecha;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->cliente_id = (int)$request->cliente_id;
        $doc_encabezado->core_tercero_id = (int)$request->core_tercero_id;
        $doc_encabezado->forma_pago = $request->forma_pago;
        $doc_encabezado->fecha_vencimiento = $request->fecha_vencimiento;
        $doc_encabezado->vendedor_id = $request->vendedor_id;
        $doc_encabezado->lineas_registros_medios_recaudos = $request->lineas_registros_medios_recaudos;
        $doc_encabezado->valor_total = $total_factura;
        $doc_encabezado->modificado_por = Auth::user()->email;
        $doc_encabezado->total_efectivo_recibido = $request->total_efectivo_recibido;
        $doc_encabezado->valor_ajuste_al_peso = $request->valor_ajuste_al_peso;
        $doc_encabezado->valor_total_bolsas = $request->valor_total_bolsas;
        $doc_encabezado->valor_total_cambio = $request->valor_total_cambio;
        $doc_encabezado->save();

        $this->sincronizar_cxc_credito_desde_encabezado($doc_encabezado);

        $datos_despues = [
            'fecha' => $doc_encabezado->fecha,
            'descripcion' => $doc_encabezado->descripcion,
            'cliente_id' => (int)$doc_encabezado->cliente_id,
            'core_tercero_id' => (int)$doc_encabezado->core_tercero_id,
            'forma_pago' => $doc_encabezado->forma_pago,
            'fecha_vencimiento' => $doc_encabezado->fecha_vencimiento,
            'vendedor_id' => (int)$doc_encabezado->vendedor_id,
            'lineas_registros_medios_recaudos' => (string)$doc_encabezado->lineas_registros_medios_recaudos,
            'valor_total' => (float)$doc_encabezado->valor_total,
            'total_efectivo_recibido' => (float)$doc_encabezado->total_efectivo_recibido,
            'valor_ajuste_al_peso' => (float)$doc_encabezado->valor_ajuste_al_peso,
            'valor_total_bolsas' => (float)$doc_encabezado->valor_total_bolsas,
            'valor_total_cambio' => (float)$doc_encabezado->valor_total_cambio
        ];

        $lineas_despues = DocRegistro::where('vtas_pos_doc_encabezado_id', $doc_encabezado->id)
            ->get()
            ->map(function ($linea) {
                return [
                    'inv_producto_id' => (int)$linea->inv_producto_id,
                    'cantidad' => (float)$linea->cantidad,
                    'precio_unitario' => (float)$linea->precio_unitario,
                    'precio_total' => (float)$linea->precio_total,
                    'tasa_descuento' => (float)$linea->tasa_descuento,
                    'tasa_impuesto' => (float)$linea->tasa_impuesto,
                    'base_impuesto_total' => (float)$linea->base_impuesto_total
                ];
            })
            ->values()
            ->toArray();

        $this->registrar_auditoria_edicion($request, $doc_encabezado, $datos_antes, $datos_despues, $lineas_antes, $lineas_despues);

        $response = $this->build_json_doc_encabezado($doc_encabezado);
        $response['request_id'] = $this->get_request_id_from_request($request);
        return response()->json( $response, 200);
    }

    protected function sincronizar_cxc_credito_desde_encabezado($doc_encabezado)
    {
        if ($doc_encabezado->forma_pago != 'credito') {
            return;
        }

        $cxc = CxcMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
            ->where('consecutivo', $doc_encabezado->consecutivo)
            ->first();

        if (is_null($cxc)) {
            return;
        }

        $nuevo_valor_documento = (float)$doc_encabezado->valor_total + (float)$doc_encabezado->valor_ajuste_al_peso + (float)$doc_encabezado->valor_total_bolsas;

        $cxc->core_tercero_id = $doc_encabezado->core_tercero_id;
        $cxc->fecha = $doc_encabezado->fecha;
        $cxc->fecha_vencimiento = $doc_encabezado->fecha_vencimiento;
        $cxc->valor_documento = $nuevo_valor_documento;
        $cxc->saldo_pendiente = $nuevo_valor_documento - (float)$cxc->valor_pagado;
        $cxc->save();
    }

    protected function find_existing_doc_by_uniqid($uniqid, Request $request = null)
    {
        if (trim((string)$uniqid) == '') {
            return null;
        }

        $query = FacturaPos::where('uniqid', $uniqid);

        if ( !is_null($request) ) {
            $core_empresa_id = (int)$request->get('core_empresa_id');
            $cajero_id = (int)$request->get('cajero_id');
            $pdv_id = (int)$request->get('pdv_id');

            if ($core_empresa_id > 0) {
                $query->where('core_empresa_id', $core_empresa_id);
            }
            if ($cajero_id > 0) {
                $query->where('cajero_id', $cajero_id);
            }
            if ($pdv_id > 0) {
                $query->where('pdv_id', $pdv_id);
            }
        }

        return $query->first();
    }

    protected function get_request_id_from_request(Request $request = null)
    {
        if ( is_null($request) ) {
            $request = request();
        }

        $request_id = trim((string)$request->get('request_id', ''));
        if ($request_id == '') {
            $request_id = (string)Str::uuid();
        }

        return substr($request_id, 0, 120);
    }

    protected function build_pos_log_context(Request $request, $request_id, $request_uniqid)
    {
        return [
            'request_id' => (string)$request_id,
            'uniqid' => (string)$request_uniqid,
            'draft_id' => (string)$request->get('draft_id', ''),
            'tab_instance_id' => (string)$request->get('tab_instance_id', ''),
            'pdv_id' => (int)$request->get('pdv_id'),
            'cajero_id' => (int)$request->get('cajero_id'),
            'core_empresa_id' => (int)$request->get('core_empresa_id'),
            'user_id' => Auth::id(),
            'ip' => (string)$request->ip(),
            'user_agent' => substr((string)$request->header('User-Agent', ''), 0, 255)
        ];
    }
    protected function registrar_auditoria_edicion(Request $request, $doc_encabezado, $datos_antes, $datos_despues, $lineas_antes, $lineas_despues)
    {
        if (!Schema::hasTable('vtas_pos_facturas_ediciones_auditoria')) {
            return;
        }

        $cambios = [];
        foreach ($datos_despues as $campo => $valor_despues) {
            $valor_antes = isset($datos_antes[$campo]) ? $datos_antes[$campo] : null;

            if ((string)$valor_antes !== (string)$valor_despues) {
                $cambios[$campo] = [
                    'antes' => $valor_antes,
                    'despues' => $valor_despues
                ];
            }
        }

        if (json_encode($lineas_antes) !== json_encode($lineas_despues)) {
            $cambios['lineas_registros'] = [
                'cantidad_antes' => count($lineas_antes),
                'cantidad_despues' => count($lineas_despues)
            ];
        }

        DB::table('vtas_pos_facturas_ediciones_auditoria')->insert([
            'vtas_pos_doc_encabezado_id' => $doc_encabezado->id,
            'core_tipo_doc_app_id' => $doc_encabezado->core_tipo_doc_app_id,
            'consecutivo' => $doc_encabezado->consecutivo,
            'editado_por' => Auth::user()->email,
            'editado_en' => date('Y-m-d H:i:s'),
            'ip' => $request->ip(),
            'user_agent' => (string)$request->header('User-Agent', ''),
            'cambios' => json_encode($cambios, JSON_UNESCAPED_UNICODE),
            'datos_antes' => json_encode($datos_antes, JSON_UNESCAPED_UNICODE),
            'datos_despues' => json_encode($datos_despues, JSON_UNESCAPED_UNICODE),
            'lineas_antes' => json_encode($lineas_antes, JSON_UNESCAPED_UNICODE),
            'lineas_despues' => json_encode($lineas_despues, JSON_UNESCAPED_UNICODE),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
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
                    $this->guardar_pedido_sin_tocar_updated_at($un_pedido);

                    self::actualizar_cantidades_pendientes( $un_pedido, 'sumar' );
                }
            }else{
                $pedido->estado = "Pendiente";
                $pedido->ventas_doc_relacionado_id = 0;
                $this->guardar_pedido_sin_tocar_updated_at($pedido);

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

    /**
     * Desde la Vista Show
     */
    public function anular_factura_acumulada(Request $request)
    {
        $factura_pos_service = new FacturaPosService();
        if ( $factura_pos_service->factura_tiene_abonos_cxc( $request->factura_id )->status ) 
        {
            return redirect('pos_factura/' . $request->factura_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('mensaje_error', 'Factura NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería).');
        }

        // Anular factura con todos sus movimientos y remisión
        $factura_pos_service->anular_factura_contabilizada( $request->factura_id, $request->anular_remision );

        return redirect('pos_factura/' . $request->factura_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion)->with('flash_message', 'Factura de ventas ANULADA correctamente.');
    }

    /**
     * Desdeel boton Consultar facturas en el Create/Edit de Factura POS
     */
    public function anular_factura_contabilizada($factura_id)
    {
        $factura_pos_service = new FacturaPosService();

        $factura_tiene_abonos_cxc = $factura_pos_service->factura_tiene_abonos_cxc( $factura_id );
        
        if ( $factura_tiene_abonos_cxc->status )
        {

            return response()->json( 
                                    [
                                        'status' => 'error',
                                        'message' => 'Factura NO puede ser eliminada. Se le han hecho Recaudos de CXC (Tesorería).'
                                    ], 
                                    200
                                );
        }    

        // Anular factura con todos sus movimientos y remisión
        $factura_pos_service->anular_factura_contabilizada( $factura_id, true );

        return response()->json( 
                                [
                                    'status' => 'success',
                                    'message' => 'Factura de ventas ANULADA correctamente.'
                                ], 
                                200
                            );
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
        
        // 1. Un documento de desarme (MK) por acumulación
        $obj_acumm_serv->hacer_desarme_automatico();        
        
        // 2. Un documento de ENSAMBLE (MK) por cada Item Platillo vendido
        if ( (int)config( 'ventas_pos.crear_ensamble_de_recetas' ) )
        {
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

    // Llamado desde la vista Show (Boton de accion)
    public function acumular_una_factura_individual($factura_id)
    {
        $invoice = FacturaPos::find($factura_id);

        $obj_acumm_serv = new AccumulationService( $invoice->pdv_id );  
        
        // Realizar preparaciones de recetas
        $obj_acumm_serv->hacer_preparaciones_recetas( 'Creado por factura POS ' . $invoice->get_label_documento(), $invoice->fecha );

        // Realizar desarme automático
        $obj_acumm_serv->hacer_desarme_automatico( 'Creado por factura POS ' . $invoice->get_label_documento(), $invoice->fecha );

        $validation = $this->validar_existencias( $invoice->pdv_id );
        if( $validation != 1 )
        {
            return redirect('pos_factura/' . $factura_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('mensaje_error', $validation);
        }

        // Acumular y Contabilizar
        $obj_acumm_serv->accumulate_one_invoice($factura_id);

        return redirect('pos_factura/' . $factura_id . '?id=20&id_modelo=230&id_transaccion=47' )->with('flash_message', 'Factura Acumulada correctamente.');
    }

    /**
     * 
     */
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

    /**
     * 
     */
    public function store_registro_ingresos_gastos(Request $request)
    {
        // $this->datos es una variable de 
        $this->datos = $request->all();
        $this->datos['core_tercero_id'] = $request->cliente_proveedor_id;
        $this->datos['descripcion'] = $request->detalle_operacion;

        
        $detalle_operacion = $request->detalle_operacion;

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
        if ($motivo->teso_tipo_motivo == 'anticipo-proveedor') {
            $this->datos['valor_documento'] = $valor_movimiento * -1;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento * -1;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            $this->datos['detalle'] = $detalle_operacion;
            CxpMovimiento::create($this->datos);
        }

        // Generar CxP porque se utilizó dinero de un agente externo (banco, coopertaiva, tarjeta de crédito).
        if ($motivo->teso_tipo_motivo == 'prestamo-recibido') {
            $this->datos['valor_documento'] = $valor_movimiento;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            $this->datos['detalle'] = $detalle_operacion;
            CxpMovimiento::create($this->datos);
        }

        // Generar CxC por algún dinero prestado o anticipado a trabajadores o clientes.
        if ($motivo->teso_tipo_motivo == 'prestamo-entregado') {
            $this->datos['valor_documento'] = $valor_movimiento;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            $this->datos['detalle'] = $detalle_operacion;
            CxcMovimiento::create($this->datos);
        }

        // Generar CxC: movimiento de cartera de clientes
        if ($motivo->teso_tipo_motivo == 'anticipo-clientes') {
            $this->datos['valor_documento'] = $valor_movimiento * -1;
            $this->datos['valor_pagado'] = 0;
            $this->datos['saldo_pendiente'] = $valor_movimiento * -1;
            $this->datos['fecha_vencimiento'] = $this->datos['fecha'];
            $this->datos['estado'] = 'Pendiente';
            $this->datos['detalle'] = $detalle_operacion;
            CxcMovimiento::create($this->datos);
        }

        return '<h4>Registro almacenado correctamente<br><span class="text-info">Documento: ' . $doc_encabezado->tipo_documento_app->prefijo . ' ' . $doc_encabezado->consecutivo . '</span></h4><hr><a class="btn-gmail" href="' . url('/') . '/tesoreria/pagos_imprimir/' . $doc_encabezado->id . '?id=3&id_modelo=' . $request->id_modelo . '&id_transaccion=' . $request->id_transaccion . '&formato_impresion_id=pos' . '" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i></a>';
    }

    /**
     * 
     */
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
        return (new CrudService())->set_catalogos( $pdv_id );
    }

    public function reconstruir_movimientos_factura($documento_id)
    {
        if (!Auth::user()->can('vtas_recontabilizar')) {
            abort(403, 'No autorizado.');
        }

        $result = (new AccountingServices())->reconstruir_movimientos_y_recontabilizar_factura($documento_id);
        if ($result->status == 'error') {
            return redirect('pos_factura/' . $documento_id . '?id=20&id_modelo=230&id_transaccion=47')->with('mensaje_error', $result->message);
        }

        $summary = $result->summary;

        $mensaje = 'Proceso de reconstrucción y recontabilización ejecutado.<br>';
        $mensaje .= 'Documento: ' . $summary['documento'] . '<br>';
        $mensaje .= 'Líneas procesadas: ' . $summary['lineas_procesadas'] . ' (actualizadas: ' . $summary['lineas_actualizadas'] . ')<br>';
        $mensaje .= 'Total factura: $' . number_format($summary['total_anterior'], 0, ',', '.') . ' -> $' . number_format($summary['total_nuevo'], 0, ',', '.') . '<br>';
        $mensaje .= 'Movimientos POS: eliminados ' . $summary['mov_pos_eliminados'] . ', creados ' . $summary['mov_pos_creados'] . '<br>';
        $mensaje .= 'Movimientos Ventas: eliminados ' . $summary['mov_vtas_eliminados'] . ', creados ' . $summary['mov_vtas_creados'] . '<br>';
        $mensaje .= 'Contabilidad: eliminados ' . $summary['contab_eliminados'] . ', creados ' . $summary['contab_creados'] . '<br>';

        if ($summary['teso_lineas_afectadas'] > 0) {
            $mensaje .= 'Tesorería: líneas afectadas ' . $summary['teso_lineas_afectadas'] . ', valor total $' . number_format($summary['teso_valor_anterior'], 0, ',', '.') . ' -> $' . number_format($summary['teso_valor_nuevo'], 0, ',', '.') . '<br>';
        }

        if ($summary['cxc_afectado'] == 1) {
            $mensaje .= 'CxC: valor documento $' . number_format($summary['cxc_valor_anterior'], 0, ',', '.') . ' -> $' . number_format($summary['cxc_valor_nuevo'], 0, ',', '.') . '<br>';
        }

        if (!empty($summary['observaciones'])) {
            $mensaje .= '<br><b>Advertencias:</b><br>' . implode('<br>', $summary['observaciones']);
        }

        return redirect('pos_factura/' . $documento_id . '?id=20&id_modelo=230&id_transaccion=47')->with('flash_message', $mensaje);
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

            $item_descripcion = $linea->item->descripcion . $linea->item->get_talla() . $linea->item->get_codigo_proveedor();
            
            $cuerpo_tabla_lineas_registros .= '<tr class="linea_registro" data-numero_linea="' . $i . '"><td style="display: none;"><div class="inv_producto_id">' . $linea->inv_producto_id . '</div></td><td style="display: none;"><div class="precio_unitario">' . $linea->precio_unitario . '</div></td><td style="display: none;"><div class="base_impuesto">' . $linea->base_impuesto . '</div></td><td style="display: none;"><div class="tasa_impuesto">' . $linea->tasa_impuesto . '</div></td><td style="display: none;"><div class="valor_impuesto">' . $linea->valor_impuesto . '</div></td><td style="display: none;"><div class="base_impuesto_total">' . $linea->base_impuesto_total . '</div></td><td style="display: none;"><div class="cantidad">' . $linea->cantidad . '</div></td><td style="display: none;"><div class="precio_total">' . $linea->precio_total . '</div></td><td style="display: none;"><div class="tasa_descuento">' . $linea->tasa_descuento . '</div></td><td style="display: none;"><div class="valor_total_descuento">' . $linea->valor_total_descuento . '</div></td><td> &nbsp; </td><td> <span style="background-color:#F7B2A3;">' . $linea->inv_producto_id . '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' . $item_descripcion . ' </div> (<div class="lbl_producto_unidad_medida" style="display: inline;">' . $linea->item->get_unidad_medida1() . '</div>)  </td><td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' . $linea->cantidad . '</div> </div> </td><td>  <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar." id="elemento_modificar_precio_unitario"> ' . $linea->precio_unitario . '</div></div></td><td>' . $linea->tasa_descuento . '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' . number_format($linea->valor_total_descuento, '0', ',', '.') . '</div> ) </td><td><div class="lbl_tasa_impuesto" style="display: inline;">' . $linea->tasa_impuesto . '%</div></td><td> <div class="lbl_precio_total" style="display: inline;">$ ' . number_format($linea->precio_total, '0', ',', '.') . ' </div> </td> <td><button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button></td></tr>';
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
                                ['estado','=','Pendiente']
                            ]
                        )
                ->where('ventas_doc_relacionado_id', 0)
                ->whereIn('core_tipo_transaccion_id', [42, 60])
                ->get();
    }

    protected function guardar_pedido_sin_tocar_updated_at($pedido)
    {
        $pedido->timestamps = false;
        $pedido->save();
        $pedido->timestamps = true;
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

    protected function aplicar_excedente_transferencia_como_otros_recaudos(Request &$request)
    {
        if (!(int)config('ventas_pos.aplicar_redondeo_adicional_transferencia')) {
            return;
        }

        $valor_total_cambio = (float)$request->get('valor_total_cambio', 0);
        if ($valor_total_cambio <= 0) {
            return;
        }

        $lineas_recaudos = json_decode($request->lineas_registros_medios_recaudos, true);
        if (!is_array($lineas_recaudos) || empty($lineas_recaudos)) {
            return;
        }

        $motivo_otros_recaudos = TesoMotivo::where('core_empresa_id', (int)$request->core_empresa_id)
            ->where('teso_tipo_motivo', 'otros-recaudos')
            ->where('movimiento', 'entrada')
            ->where('estado', 'Activo')
            ->orderBy('id', 'ASC')
            ->first();

        if (is_null($motivo_otros_recaudos)) {
            return;
        }

        $index_linea_transferencia = null;
        foreach ($lineas_recaudos as $index => $linea) {
            if (!is_array($linea)) {
                continue;
            }

            $teso_medio_recaudo_id = isset($linea['teso_medio_recaudo_id']) ? explode('-', $linea['teso_medio_recaudo_id'])[0] : 0;
            $teso_caja_id = isset($linea['teso_caja_id']) ? explode('-', $linea['teso_caja_id'])[0] : 0;
            $valor_linea = isset($linea['valor']) ? (float)str_replace('$', '', $linea['valor']) : 0;

            // Excluye anticipos y toma solo lineas por banco/transferencia (caja = 0)
            if ((int)$teso_medio_recaudo_id != 0 && (int)$teso_caja_id == 0 && $valor_linea > 0) {
                $index_linea_transferencia = $index;
                break;
            }
        }

        if (is_null($index_linea_transferencia)) {
            return;
        }

        $linea_transferencia = $lineas_recaudos[$index_linea_transferencia];
        $valor_transferencia = (float)str_replace('$', '', $linea_transferencia['valor']);
        $excedente = min($valor_total_cambio, $valor_transferencia);

        if ($excedente <= 0) {
            return;
        }

        $lineas_recaudos[$index_linea_transferencia]['valor'] = '$' . ($valor_transferencia - $excedente);
        if ((float)str_replace('$', '', $lineas_recaudos[$index_linea_transferencia]['valor']) <= 0) {
            unset($lineas_recaudos[$index_linea_transferencia]);
        }

        $lineas_recaudos[] = [
            'teso_medio_recaudo_id' => $linea_transferencia['teso_medio_recaudo_id'],
            'teso_motivo_id' => $motivo_otros_recaudos->id . '-' . $motivo_otros_recaudos->descripcion,
            'teso_caja_id' => $linea_transferencia['teso_caja_id'],
            'teso_cuenta_bancaria_id' => $linea_transferencia['teso_cuenta_bancaria_id'],
            'valor' => '$' . $excedente
        ];

        $request['lineas_registros_medios_recaudos'] = json_encode(array_values($lineas_recaudos));
        $request['valor_total_cambio'] = 0;
    }

    /*
        Crea los registros de un documento.
        No Devuelve nada.
    */
    public static function crear_registros_documento(Request $request, $doc_encabezado, array $lineas_registros)
    {
        $invoicing_service = new InvoicingService();

        // Lineas de registros
        $invoicing_service->crear_registros_documento_pos($request, $doc_encabezado, $lineas_registros);

        $doc_encabezado->valor_total = $doc_encabezado->lineas_registros->sum('precio_total');
        $doc_encabezado->save();

        // Movimiento
        $invoicing_service->crear_movimiento_pos($doc_encabezado);

        return 0;
    }
}








