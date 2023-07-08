<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\EmailController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Sistema\Modelo;
use App\Core\Tercero;
use App\Core\EncabezadoDocumentoTransaccion;
use App\Core\TransaccionOtrosCampos;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\RemisionVentas;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\InvMotivo;

use App\Ventas\VtasTransaccion;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Ventas\Cliente;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\ListaDctoDetalle;
use App\Ventas\NotaCredito;

use App\CxC\DocumentosPendientes;
use App\CxC\CxcAbono;

use App\Ventas\Services\TreasuryServices;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\RegistrosMediosPago;

use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\Impuesto;

use App\Matriculas\FacturaAuxEstudiante;
use App\Ventas\Services\DocumentHeaderService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class VentaController extends TransaccionController
{
    protected $doc_encabezado;

    /* El método index() está en TransaccionController */

    
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = [ '10-salida' => 'Ventas POS' ];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros( VtasTransaccion::get_datos_tabla_ingreso_lineas_registros( $this->transaccion, $motivos ) );

        $item_sugerencia_cliente = '';
        if ( !is_null( Input::get('cliente_id') ) )
        {
            //$cliente = Cliente::find( (int)Input::get('cliente_id') );
            $cliente = Cliente::leftJoin('core_terceros','core_terceros.id','=','vtas_clientes.core_tercero_id')
                                ->leftJoin('vtas_vendedores','vtas_vendedores.id','=','vtas_clientes.vendedor_id')
                                ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','vtas_clientes.condicion_pago_id')
                                ->leftJoin('vtas_listas_precios_encabezados','vtas_listas_precios_encabezados.id','=','vtas_clientes.lista_precios_id')
                                ->leftJoin('vtas_listas_dctos_encabezados','vtas_listas_dctos_encabezados.id','=','vtas_clientes.lista_descuentos_id')
                                ->leftJoin('inv_bodegas','inv_bodegas.id','=','vtas_clientes.inv_bodega_id')
                                ->where('vtas_clientes.estado','Activo')
                                ->where( 'vtas_clientes.id', (int)Input::get('cliente_id') )
                                ->select(
                                            'vtas_clientes.id',
                                            'vtas_clientes.id AS cliente_id',
                                            'vtas_clientes.liquida_impuestos',
                                            'vtas_clientes.zona_id',
                                            'vtas_clientes.clase_cliente_id',
                                            'core_terceros.id AS core_tercero_id',
                                            'core_terceros.descripcion',
                                            'core_terceros.numero_identificacion',
                                            'core_terceros.direccion1',
                                            'core_terceros.telefono1',
                                            'core_terceros.email',
                                            'vtas_vendedores.id AS vendedor_id',
                                            'vtas_vendedores.equipo_ventas_id',
                                            'inv_bodegas.id AS inv_bodega_id',
                                            'vtas_condiciones_pago.dias_plazo',
                                            'vtas_listas_precios_encabezados.id AS lista_precios_id',
                                            'vtas_listas_dctos_encabezados.id AS lista_descuentos_id'
                                        )
                                ->get()
                                ->first();

            $item_sugerencia_cliente = $this->get_linea_item_sugerencia( $cliente, 'active', 1, 0 );
        }

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'ventas.create', $tabla, $item_sugerencia_cliente );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datos = $request->all(); // Datos originales
        
        $lineas_registros = json_decode($request->lineas_registros);

        $registros_medio_pago = new RegistrosMediosPago;

        $campo_lineas_recaudos = $registros_medio_pago->depurar_tabla_registros_medios_recaudos( $request->all()['lineas_registros_medios_recaudo'],self::get_total_documento_desde_lineas_registros( $lineas_registros ) );

        // TRES TRANSACCIONES

        // 1ra. Crear documento de salida de inventarios (REMISIÓN)
        $remision = new RemisionVentas;
        $documento_remision = $remision->crear_nueva( $request->all() );

        // 2da. Crear documento de Ventas
        $request['remision_doc_encabezado_id'] = $documento_remision->id;
        $request['estado'] = 'Activo';
        $doc_encabezado = TransaccionController::crear_encabezado_documento($request, $request->url_id_modelo);

        // 3ra. Crear Registro del documento de ventas
        $request['creado_por'] = Auth::user()->email;
        $request['registros_medio_pago'] = $registros_medio_pago->get_datos_ids( $campo_lineas_recaudos );
        VentaController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        $modelo = Modelo::find( $request->url_id_modelo );

        if( isset($request->abono) && $request->forma_pago == 'credito' )
        {
            // Create Account Receivable Payment (Recaudo de CxC)
            $abono = (float)$request->abono;
            if ($abono > $doc_encabezado->valor_total) {
                $abono = $doc_encabezado->valor_total;
            }
            if ( $abono != 0 )
            {
                $obj_trea_serv = new TreasuryServices();
                $obj_trea_serv->create_account_receivable_payment_from_invoice($doc_encabezado,$abono,$request['lineas_registros_medios_recaudo']);
            }            
        }

        /*
            Tareas adicionales de almacenamiento (guardar en otras tablas, crear otros modelos, etc.)
        */

        if (method_exists(app($modelo->name_space), 'store_adicional'))
        {
            // Aquí mismo se puede hacer el return
            app($modelo->name_space)->store_adicional($datos, $doc_encabezado);

            return redirect( 'factura_medica/'.$doc_encabezado->id.'?id='.$datos['url_id'].'&id_modelo='.$datos['url_id_modelo'].'&id_transaccion='.$datos['url_id_transaccion'] );
            
        }else{
            return redirect('ventas/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
        }
    }

    
    public static function get_total_documento_desde_lineas_registros( array $lineas_registros )
    {
        $total_documento = 0;

        $cantidad_registros = count($lineas_registros);
        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            $total_documento += (float)$lineas_registros[$i]->precio_total;
        } // Fin por cada registro

        return $total_documento;        
    }

    


    // Se crean los registros con base en los registros de la remisión o remisiones
    public static function get_total_documento_desde_lineas_registros_desde_remision( $datos, $lineas_registros )
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

                $datos_precio_descuento = self::get_datos_precio_descuento( $datos['lista_precios_id'], $datos['fecha'], $un_registro->inv_producto_id, InvDocEncabezado::find($doc_remision_id) );
                
                $precio_unitario = $datos_precio_descuento->precio_unitario;

                $valor_unitario_descuento = $precio_unitario * ( $datos_precio_descuento->tasa_descuento / 100 );

                $precio_venta_unitario = $precio_unitario - $valor_unitario_descuento;

                $precio_total = $precio_venta_unitario * $cantidad;

                $total_documento += $precio_total;
            } // Fin por cada registro de la remisión
        }

        return $total_documento;
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
            if ( (int)$lineas_registros[$i]->cantidad == 0 ) {
                continue;
            }

            $linea_datos = [ 'vtas_motivo_id' => (int)$lineas_registros[$i]->inv_motivo_id ] +
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
                            [ 'estado' => 'Activo' ];

            VtasDocRegistro::create( 
                                    $datos + 
                                    [ 'vtas_doc_encabezado_id' => $doc_encabezado->id ] +
                                    $linea_datos
                                );

            $datos['consecutivo'] = $doc_encabezado->consecutivo;
            VtasMovimiento::create( 
                                    $datos +
                                    $linea_datos
                                );

            // CONTABILIZAR INGRESOS
            $detalle_operacion = '';
            if (isset($datos['descripcion'])) {
                $detalle_operacion = $datos['descripcion'];
            }
            
            VentaController::contabilizar_movimiento_credito( $datos + $linea_datos, $detalle_operacion );

            $total_documento += (float)$lineas_registros[$i]->precio_total;

        } // Fin por cada registro

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->save();

        // Un solo registro contable débito
        $forma_pago = $request->forma_pago; // esto se debe determinar de acuerdo a algún parámetro en la configuración, $datos['forma_pago']

        // Cartera ó Caja (DB)
        VentaController::contabilizar_movimiento_debito( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion );

        // Crear registro del pago: cuenta por cobrar(cartera) o recaudo
        VentaController::crear_registro_pago( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion );
        
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

            if ( is_null($cta_x_cobrar_id) || $cta_x_cobrar_id == 0 )
            {
                $cta_x_cobrar_id = (int)config('configuracion.cta_cartera_default');
            }
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

    // Contabilizar Ingresos de ventas e Impuestos
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
            DocumentosPendientes::create( $datos );
        }

        if ( $forma_pago == 'contado')
        {
            $teso_movimiento = new TesoMovimiento();
            $teso_movimiento->almacenar_registro_pago_contado( $datos, $datos['registros_medio_pago'], 'entrada', $total_documento );
        }
    }

    /**
     *
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $encabezado_documento = app( $this->transaccion->modelo_encabezados_documentos )->find( $id );

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $id );
        
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $doc_encabezado->id );

        $doc_encabezado->documento_transaccion_prefijo_consecutivo = $this->get_documento_transaccion_prefijo_consecutivo( $doc_encabezado );

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados( $doc_encabezado );
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

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $registros_tesoreria = TesoMovimiento::get_registros_un_documento( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo )->first();
        $medios_pago = View::make('tesoreria.incluir.show_medios_pago', compact('registros_tesoreria'))->render();

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

        return view( $vista, compact( 'id', 'botones_anterior_siguiente', 'miga_pan', 'encabezado_documento', 'documento_vista', 'doc_encabezado', 'registros_contabilidad','abonos','empresa','docs_relacionados','doc_registros','url_crear','id_transaccion','notas_credito','medios_pago') );
    }

    public function get_documento_transaccion_prefijo_consecutivo( $doc_encabezado )
    {
        if( (int)config('ventas.longitud_consecutivo_factura') == 0 )
        {
            return $doc_encabezado->documento_transaccion_prefijo_consecutivo;
        }

        $consecutivo = $doc_encabezado->consecutivo;
        $largo = (int)config('ventas.longitud_consecutivo_factura') - strlen($doc_encabezado->consecutivo);
        for ($i=0; $i < $largo; $i++)
        { 
            $consecutivo = '0' . $consecutivo;
        }

        return $doc_encabezado->tipo_documento_app->prefijo . ' ' . $consecutivo;
    }

    /*
        Imprimir
    */
    public function imprimir( $id )
    {
        $documento_vista = $this->generar_documento_vista( $id, 'ventas.formatos_impresion.'.Input::get('formato_impresion_id') );

        if ( Input::get('formato_impresion_id') == 'pos') {
            return $documento_vista;
        }

        // Se prepara el PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream( $this->doc_encabezado->documento_transaccion_descripcion.' - '.$this->doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }

    public function show_ventana_imprimir( $id )
    {
        return $this->generar_documento_vista( $id, 'ventas.formatos_impresion.'.Input::get('formato_impresion_id') );
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

        $email_destino = $tercero->email;
        if ( $this->doc_encabezado->contacto_cliente_id != 0 )
        {
            $email_destino = $this->doc_encabezado->contacto_cliente->tercero->email;
        }

        $vec = EmailController::enviar_por_email_documento( $this->empresa->descripcion, $email_destino, $asunto, $cuerpo_mensaje, $documento_vista );

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
        $doc_encabezado->documento_transaccion_prefijo_consecutivo = $this->get_documento_transaccion_prefijo_consecutivo( $doc_encabezado );
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

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)->where('estado','Activo')->get()->last();

        $etiquetas = $this->get_etiquetas();

        $abonos = CxcAbono::get_abonos_documento( $doc_encabezado );

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados( $doc_encabezado );

        $otroscampos = TransaccionOtrosCampos::where('core_tipo_transaccion_id',$this->doc_encabezado->core_tipo_transaccion_id)->get()->first();

        return View::make( $ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'abonos', 'docs_relacionados', 'otroscampos' ) )->render();
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

        $doc_encabezado = VtasDocEncabezado::get_registro_impresion( $id );

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxcAbono::where('doc_cxc_transacc_id',$doc_encabezado->core_tipo_transaccion_id)
                            ->where('doc_cxc_tipo_doc_id',$doc_encabezado->core_tipo_doc_app_id)
                            ->where('doc_cxc_consecutivo',$doc_encabezado->consecutivo)
                            ->count();

        if($cantidad != 0)
        {
            return redirect( 'ventas/'.$id.'?id='. Input::get('id') .'&id_modelo='. Input::get('id_modelo') .'&id_transaccion='. Input::get('id_transaccion')  )->with('mensaje_error','Factura NO puede ser modificada. Se le han hecho Recaudos de CXC (Tesorería).');
        }

        $cantidad = count( $lista_campos );

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
                                                                <br/>
                                                                <b>Fecha:</b> '.$doc_encabezado->fecha.'
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
        DocumentosPendientes::where('core_tipo_transaccion_id',$registro->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$registro->core_tipo_doc_app_id)
                        ->where('consecutivo',$registro->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha,
                                    'fecha_vencimiento' => $request->fecha_vencimiento
                                ] );

        // Actualiza movimiento de Tesorería
        TesoMovimiento::where('core_tipo_transaccion_id',$registro->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$registro->core_tipo_doc_app_id)
                        ->where('consecutivo',$registro->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha
                                ] );;

        // Actualiza documento de inventario
        $remision = InvDocEncabezado::find($registro->remision_doc_encabezado_id);
        if ( !is_null( $remision ) )
        {
            $remision->fill( $request->all() );
            $remision->save();

            // Actualiza MOVIMIENTO de inventario
            InvMovimiento::where('core_tipo_transaccion_id',$remision->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id',$remision->core_tipo_doc_app_id)
                            ->where('consecutivo',$remision->consecutivo)
                            ->update( [ 
                                        'fecha' => $request->fecha
                                    ] );
            ContabMovimiento::where('core_tipo_transaccion_id',$remision->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id',$remision->core_tipo_doc_app_id)
                            ->where('consecutivo',$remision->consecutivo)
                            ->update( [ 
                                        'fecha' => $request->fecha,
                                        'detalle_operacion' => $request->descripcion
                                    ] );
        }
            

        // Actualiza MOVIMIENTO de VENTAS
        VtasMovimiento::where('core_tipo_transaccion_id',$registro->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$registro->core_tipo_doc_app_id)
                        ->where('consecutivo',$registro->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha,
                                    'fecha_vencimiento' => $request->fecha_vencimiento,
                                    'vendedor_id' => $request->vendedor_id,
                                    'orden_compras' => $request->orden_compras
                                ] );

        // Actualiza MOVIMIENTO de CONTABILIDAD para la factura
        ContabMovimiento::where('core_tipo_transaccion_id',$registro->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$registro->core_tipo_doc_app_id)
                        ->where('consecutivo',$registro->consecutivo)
                        ->update( [ 
                                    'fecha' => $request->fecha,
                                    'fecha_vencimiento' => $request->fecha_vencimiento,
                                    'detalle_operacion' => $request->descripcion
                                ] );


        return redirect( 'ventas/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion );
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
                                ->leftJoin('vtas_condiciones_pago','vtas_condiciones_pago.id','=','vtas_clientes.condicion_pago_id')
                                ->leftJoin('vtas_listas_precios_encabezados','vtas_listas_precios_encabezados.id','=','vtas_clientes.lista_precios_id')
                                ->leftJoin('vtas_listas_dctos_encabezados','vtas_listas_dctos_encabezados.id','=','vtas_clientes.lista_descuentos_id')
                                ->leftJoin('inv_bodegas','inv_bodegas.id','=','vtas_clientes.inv_bodega_id')
                                ->where('vtas_clientes.estado','Activo')
                                ->where('core_terceros.'.$campo_busqueda,$operador,$texto_busqueda)
                                ->select(
                                            'vtas_clientes.id',
                                            'vtas_clientes.id AS cliente_id',
                                            'vtas_clientes.liquida_impuestos',
                                            'vtas_clientes.zona_id',
                                            'vtas_clientes.clase_cliente_id',
                                            'vtas_clientes.vendedor_id',
                                            'core_terceros.id AS core_tercero_id',
                                            'core_terceros.descripcion',
                                            'core_terceros.numero_identificacion',
                                            'core_terceros.direccion1',
                                            'core_terceros.telefono1',
                                            'core_terceros.email',
                                            'inv_bodegas.id AS inv_bodega_id',
                                            'vtas_condiciones_pago.dias_plazo',
                                            'vtas_listas_precios_encabezados.id AS lista_precios_id',
                                            'vtas_listas_dctos_encabezados.id AS lista_descuentos_id'
                                        )
                                ->get()
                                ->take(7);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        $ultimo_item = 0;
        $num_item = 1;
        $cantidad_datos = count( $clientes->toArray() );
        foreach ($clientes as $linea) 
        {
            $primer_item = 0;
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
                $primer_item = 1;
            }

            if ( $num_item == $cantidad_datos )
            {
                $ultimo_item = 1;
            }

            $html .= $this->get_linea_item_sugerencia( $linea, $clase, $primer_item, $ultimo_item );

            $num_item++;
        }

        // Linea crear nuevo registro
        $modelo_id = 138; // App\Ventas\Clientes
        $html .= '<a href="'.url('vtas_clientes/create?id=13&id_modelo='.$modelo_id.'&id_transaccion').'" target="_blank" class="list-group-item list-group-item-sugerencia list-group-item-info" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nuevo </a>';

        $html .= '</div>';

        return $html;
    }

    public function get_linea_item_sugerencia( Cliente $linea, $clase, $primer_item, $ultimo_item )
    {
        $html = '<a class="list-group-item list-group-item-cliente '.$clase.'" data-cliente_id="'.$linea->cliente_id.
                                '" data-primer_item="'.$primer_item.
                                '" data-accion="na" '.
                                '" data-ultimo_item="'.$ultimo_item; // Esto debe ser igual en todas las busquedas

        $html .=            '" data-nombre_cliente="'.$linea->descripcion.
                            '" data-zona_id="'.$linea->zona_id.
                            '" data-clase_cliente_id="'.$linea->clase_cliente_id.
                            '" data-liquida_impuestos="'.$linea->liquida_impuestos.
                            '" data-core_tercero_id="'.$linea->core_tercero_id.
                            '" data-direccion1="'.$linea->direccion1.
                            '" data-telefono1="'.$linea->telefono1.
                            '" data-numero_identificacion="'.$linea->numero_identificacion.
                            '" data-vendedor_id="'.$linea->vendedor_id.
                            '" data-vendedor_descripcion="'.$linea->vendedor->tercero->descripcion.
                            '" data-equipo_ventas_id="0'.
                            '" data-inv_bodega_id="'.$linea->inv_bodega_id.
                            '" data-email="'.$linea->email.
                            '" data-dias_plazo="'.$linea->dias_plazo.
                            '" data-lista_precios_id="'.$linea->lista_precios_id.
                            '" data-lista_descuentos_id="'.$linea->lista_descuentos_id.
                            '" > '.$linea->descripcion.' ('.number_format($linea->numero_identificacion,0,',','.').') </a>';
        return $html;
    }
    


    // Parámetro enviados por GET
    public function consultar_existencia_producto()
    {
        $cliente_id = (int)Input::get('cliente_id');
        $bodega_id = (int)Input::get('bodega_id');
        $fecha = Input::get('fecha');
        $lista_precios_id = (int)Input::get('lista_precios_id');
        $lista_descuentos_id = (int)Input::get('lista_descuentos_id');
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
            $costo_promedio = InvCostoPromProducto::get_costo_promedio($bodega_id, $producto['id'] );

            /*
                El precio de venta se trae de a cuerdo al parámetro de la configuración
            */
            $descuento_unitario = 0;
            $precio_unitario = 0;
            switch ( config('ventas')['modo_liquidacion_precio'] )
            {
                case 'lista_de_precios':
                    // Precios traido desde la lista de precios asociada al cliente.
                    $precio_unitario = ListaPrecioDetalle::get_precio_producto( $lista_precios_id, $fecha, $producto_id );
                    $descuento_unitario = ListaDctoDetalle::get_descuento_producto( $lista_descuentos_id, $fecha, $producto_id );

                    break;

                case 'ultimo_precio':
                    // Precios traido del movimiento de ventas. El último precio liquidado al cliente para ese producto.
                    $precio_unitario = VtasMovimiento::get_ultimo_precio_producto( $cliente_id, $producto_id );
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
                                                [ 'descuento_unitario' => $descuento_unitario ],
                                                [ 'base_impuesto' => $base_impuesto ],
                                                [ 'tasa_impuesto' => $tasa_impuesto ],
                                                [ 'valor_impuesto' => $valor_impuesto ]
                                    );
        }

        return $producto;
    }

    public static function anular_factura(Request $request)
    {
        $doc_header_serv = new DocumentHeaderService();
        $result = $doc_header_serv->cancel_document_by_id( $request->factura_id, $request->anular_remision );
        
        return redirect( 'ventas/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with($result->status,$result->message);
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

    public function tercero_a_cliente_create_direct($tercero_id,$url_redirect)
    {
        $parametros_ventas = config('ventas');

        $datos = [
            'core_tercero_id' => $tercero_id,
            'encabezado_dcto_pp_id' => 0,
            'clase_cliente_id' => $parametros_ventas['clase_cliente_id'],
            'lista_precios_id' => $parametros_ventas['lista_precios_id'],
            'lista_descuentos_id' => $parametros_ventas['lista_descuentos_id'],
            'vendedor_id' => $parametros_ventas['vendedor_id'],
            'inv_bodega_id' => $parametros_ventas['inv_bodega_id'],
            'zona_id' => $parametros_ventas['zona_id'],
            'liquida_impuestos' => 1,
            'condicion_pago_id' => $parametros_ventas['condicion_pago_id'],
            'cupo_credito' => 0,
            'bloquea_por_cupo' => 0,
            'bloquea_por_mora' => 0,
            'estado' => 'Activo'
        ];

        // Datos del Cliente
        $Cliente = new Cliente;
        $Cliente->fill( $datos );
        $Cliente->save();

        $arr = explode('-', $url_redirect);

        return redirect( $arr[0] . '/' . $arr[1] . '/' . $arr[2] . '?' . $arr[3] . '&' . $arr[4] . '&' . $arr[5] )->with( 'flash_message','Tercero CREADO como Cliente correctamente.' );
    }


    // Petición AJAX. Parámetro enviados por GET
    public function get_formulario_edit_registro()
    {
        $linea_factura = VtasDocRegistro::get_un_registro( Input::get('linea_registro_id') );

        $factura = VtasDocEncabezado::get_registro_impresion( $linea_factura->vtas_doc_encabezado_id );

        $remision = InvDocEncabezado::get_registro_impresion( $factura->remision_doc_encabezado_id );
        
        if ( is_null($remision) )
        {
            $remision = (object)['fecha'=>'0000-00-00'];
            $linea_remision = (object)['inv_bodega_id'=>0,'inv_motivo_id'=>0];
            $saldo_a_la_fecha = 0;
        }else{
            $linea_remision = $remision->lineas_registros->where('inv_producto_id', $linea_factura->producto_id)
                                                        ->where('cantidad', $linea_factura->cantidad * -1)
                                                        ->first();
            $saldo_a_la_fecha = InvMovimiento::get_existencia_actual( $linea_remision->inv_producto_id, $linea_remision->inv_bodega_id, $remision->fecha );
        }

        $id = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $id_transaccion = Input::get('id_transaccion');

        $formulario = View::make( 'ventas.incluir.formulario_editar_registro', compact('linea_factura','linea_remision','remision','id','id_modelo','id_transaccion','saldo_a_la_fecha') )->render();

        return $formulario;
    }

    public function doc_registro_guardar( Request $request )
    {
        $linea_registro = VtasDocRegistro::find( $request->linea_factura_id );
        $doc_encabezado = VtasDocEncabezado::find( $linea_registro->vtas_doc_encabezado_id );
        
        // Verificar si la factura tiene recaudos, si tiene no se pueden modificar sus registros
        $recaudos = CxcAbono::where('doc_cxc_transacc_id',$doc_encabezado->core_tipo_transaccion_id)->where('doc_cxc_tipo_doc_id',$doc_encabezado->core_tipo_doc_app_id)->where('doc_cxc_consecutivo',$doc_encabezado->consecutivo)->get()->toArray();

        if( !empty($recaudos) )
        {
            return redirect( 'ventas/'.$doc_encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Los registros de la Factura NO pueden ser modificados. Factura tiene recaudos de cxc aplicados (Tesorería).');
        }

        $viejo_total_encabezado = $doc_encabezado->valor_total;

        $precio_unitario = $request->precio_unitario; // IVA incluido

        $cantidad = $request->cantidad;
        $valor_total_descuento = $request->valor_total_descuento;
        $tasa_descuento = $request->tasa_descuento;

        $valor_unitario_descuento = $precio_unitario * ( $tasa_descuento / 100 );

        $precio_total = ($precio_unitario - $valor_unitario_descuento) * $cantidad;

        $precio_venta_unitario = $precio_unitario - $valor_unitario_descuento;

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

        // 2. Actualiza total de la cuenta por cobrar o Tesorería
        DocumentosPendientes::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$doc_encabezado->consecutivo)
                    ->update( [ 
                                'valor_documento' => $nuevo_total_encabezado,
                                'saldo_pendiente' => $nuevo_total_encabezado
                            ] );

        TesoMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$doc_encabezado->consecutivo)
                    ->update( [ 
                                'valor_movimiento' => $nuevo_total_encabezado * -1
                            ] );

        // 3. Actualiza movimiento de ventas
        VtasMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
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

        // Contabilizar DB: Cartera o Tesoreria. Con el total del documento
        ContabMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                        ->where('consecutivo',$doc_encabezado->consecutivo)
                        ->where( 'valor_credito', 0)
                        ->where( 'valor_debito', $viejo_total_encabezado )
                        ->update( [ 
                                    'valor_debito' => $nuevo_total_encabezado,
                                    'valor_saldo' => $nuevo_total_encabezado
                                ] );
                

        // Contabilizar CR: Ingresos e Impuestos
        if ( $linea_registro->tasa_impuesto > 0 )
        {
            $cta_impuesto_ventas_id = InvProducto::get_cuenta_impuesto_ventas( $linea_registro->inv_producto_id );

            $valor_anterior_credito = $linea_registro->valor_impuesto * $linea_registro->cantidad * -1;
            $mov_contab = ContabMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                        ->where('consecutivo',$doc_encabezado->consecutivo)
                        ->where('inv_producto_id',$linea_registro->inv_producto_id)
                        ->where('cantidad',$linea_registro->cantidad)
                        ->whereBetween('valor_credito', [ $valor_anterior_credito - 10, $valor_anterior_credito + 10] )
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
        $inv_doc_encabezado = InvDocEncabezado::find( $doc_encabezado->remision_doc_encabezado_id );
        if ( !is_null($inv_doc_encabezado) )
        {
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
        }

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

        // 6. Si es una factura de Estudiante
        $factura_estudiante = FacturaAuxEstudiante::where('vtas_doc_encabezado_id',$doc_encabezado->id)->get()->first();
        if (!is_null($factura_estudiante))
        {
            $registro_cartera = $factura_estudiante->cartera_estudiante;
            $registro_cartera->valor_cartera = $nuevo_total_encabezado;
            $registro_cartera->saldo_pendiente = $nuevo_total_encabezado;
            $registro_cartera->save();
        }

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
                
                $datos_precio_descuento = $this->get_precio_unitario_remision( Input::get('lista_precios_id'), Input::get('fecha'), $un_registro->producto_id, $remision );
                
                $precio_unitario = $datos_precio_descuento->precio_unitario;

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

    // Este metodo no se puede llamar desde un metodo static
    public function get_precio_unitario_remision( $lista_precios_id, $fecha, $inv_producto_id, $remision )
    {
        // El precio se trae de la lista de precios del cliente
        $precio_unitario = ListaPrecioDetalle::get_precio_producto( $lista_precios_id, $fecha, $inv_producto_id );
        
        $tasa_descuento = 0;
        $descuento = 0;

        // Si la remision se genero desde un pedido se llama el precio unitario del pedido
        if ( $remision->vtas_doc_encabezado_origen_id != 0 )
        {
            if ( !is_null( $remision->documento_ventas_padre() ) )
            {
                $lineas_pedido = $remision->documento_ventas_padre()->lineas_registros;

                foreach( $lineas_pedido AS $linea_pedido )
                {
                    if ( $linea_pedido->inv_producto_id == $inv_producto_id )
                    {
                        $precio_unitario = $linea_pedido->precio_unitario;
                        $tasa_descuento = $linea_pedido->tasa_descuento;
                        return (object)[ 'precio_unitario' => $precio_unitario, 'tasa_descuento' => $tasa_descuento ];
                    }
                }
            }
        }
        
        return (object)[ 'precio_unitario' => $precio_unitario, 'tasa_descuento' => $tasa_descuento ];
    }

    public static function get_datos_precio_descuento( $lista_precios_id, $fecha, $inv_producto_id, $remision )
    {
        // El precio se trae de la lista de precios del cliente
        $precio_unitario = ListaPrecioDetalle::get_precio_producto( $lista_precios_id, $fecha, $inv_producto_id );
        $tasa_descuento = 0;

        // Si la remisión se genero desde un pedido
        if ( $remision->vtas_doc_encabezado_origen_id != 0 )
        {
            if ( !is_null( $remision->documento_ventas_padre()) )
            {
                $lineas_pedido = $remision->documento_ventas_padre()->lineas_registros;

                foreach( $lineas_pedido AS $linea_pedido )
                {
                    if ( $linea_pedido->inv_producto_id == $inv_producto_id )
                    {
                        $precio_unitario = $linea_pedido->precio_unitario;
                        $tasa_descuento = $linea_pedido->tasa_descuento;

                        return (object)[ 'precio_unitario' => $precio_unitario, 'tasa_descuento' => $tasa_descuento ];
                    }
                }

            }
        }

        return (object)[ 'precio_unitario' => $precio_unitario, 'tasa_descuento' => $tasa_descuento ];
    }


    /*
        Este metodo se llama desde la vista create de ventas via POST
    */
    public function factura_remision_pendiente( Request $request )
    {
        $datos = $request->all();

        $encabezado_documento = new EncabezadoDocumentoTransaccion( $request->url_id_modelo );
        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        $lineas_registros = json_decode( $request->lineas_registros );

        $registros_medio_pago = new RegistrosMediosPago;
        $campo_lineas_recaudos = $registros_medio_pago->depurar_tabla_registros_medios_recaudos( $request->all()['lineas_registros_medios_recaudo'],self::get_total_documento_desde_lineas_registros_desde_remision( $datos ,$lineas_registros ) );        
        $datos['registros_medio_pago'] = $registros_medio_pago->get_datos_ids( $campo_lineas_recaudos );

        VentaController::crear_lineas_registros( $datos, $doc_encabezado, $lineas_registros );

        return redirect('ventas/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    public function totales_remisiones_seleccionadas( Request $request )
    {
        $lineas_registros = json_decode( $request->lineas_registros_remisiones );

        $base_impuesto_total = 0;
        $total_impuesto = 0;
        $descuento = 0;
        $precio_total = 0;
        $valor_total_descuento = 0;
        $cantidad_registros = count( $lineas_registros );
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_remision_id = (int)$lineas_registros[$i]->id_doc;

            $registros_remisiones = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_remision_id )->get();
            foreach ($registros_remisiones as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad * -1;

                $datos_precio_descuento = $this->get_precio_unitario_remision( $request->lista_precios_id2, $request->fecha2, $un_registro->inv_producto_id, InvDocEncabezado::find($doc_remision_id) );

                $precio_unitario = $datos_precio_descuento->precio_unitario;

                // El descuento se calcula cuando el precio tiene el IVA incluido
                $valor_unitario_descuento = $precio_unitario * ( $datos_precio_descuento->tasa_descuento / 100 );
                $valor_total_descuento += ( $valor_unitario_descuento * $cantidad);

                $precio_venta = $precio_unitario - $valor_unitario_descuento;

                $precio_total += $precio_venta * $cantidad;

                $cliente = Cliente::where( 'core_tercero_id', $un_registro->core_tercero_id )->get()->first();

                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, 0, $cliente->id );

                $base_impuesto = $precio_venta / ( 1 + $tasa_impuesto / 100 );

                $valor_impuesto = $base_impuesto * ($tasa_impuesto / 100);

                $base_impuesto_total += $base_impuesto * $cantidad;

                $total_impuesto += ($valor_impuesto * $cantidad);                

            } // Fin por cada registro de la remisión
        }

        $subtotal = $precio_total - $total_impuesto + $valor_total_descuento;

        return [ 
                    $subtotal,
                    $total_impuesto,
                    $precio_total,
                    $valor_total_descuento
                ];
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

                // Los PRECIOS se deben traer de la lista de precios del cliente 
                //$precio_unitario = ListaPrecioDetalle::get_precio_producto( $datos['lista_precios_id'], $datos['fecha'], $un_registro->inv_producto_id );

                $datos_precio_descuento = self::get_datos_precio_descuento( $datos['lista_precios_id'], $datos['fecha'], $un_registro->inv_producto_id, InvDocEncabezado::find($doc_remision_id) );
                
                $precio_unitario = $datos_precio_descuento->precio_unitario;

                $valor_unitario_descuento = $precio_unitario * ( $datos_precio_descuento->tasa_descuento / 100 );

                $valor_total_descuento = $valor_unitario_descuento * $cantidad;

                $precio_venta_unitario = $precio_unitario - $valor_unitario_descuento;

                $cliente = Cliente::where( 'core_tercero_id', $un_registro->core_tercero_id )->get()->first();

                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, 0, $cliente->id );

                $precio_total = $precio_venta_unitario * $cantidad;

                $base_impuesto = $precio_venta_unitario / ( 1 + $tasa_impuesto / 100 );

                $valor_impuesto = $precio_venta_unitario - $base_impuesto;

                $base_impuesto_total = $base_impuesto * $cantidad;

                $linea_datos = [ 'vtas_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total ] +
                                [ 'base_impuesto' =>  $base_impuesto ] +
                                [ 'tasa_impuesto' => $tasa_impuesto ] +
                                [ 'valor_impuesto' => $valor_impuesto ] +
                                [ 'base_impuesto_total' => $base_impuesto_total ] +
                                [ 'tasa_descuento' => $datos_precio_descuento->tasa_descuento ] +
                                [ 'valor_total_descuento' => $valor_total_descuento ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                VtasDocRegistro::create( 
                                    $datos + 
                                    [ 'vtas_doc_encabezado_id' => $doc_encabezado->id ] +
                                    $linea_datos
                                );

                $datos['consecutivo'] = $doc_encabezado->consecutivo;
                VtasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );

                // CONTABILIZAR
                $detalle_operacion = $datos['descripcion'];
                VentaController::contabilizar_movimiento_credito( $datos + $linea_datos, $detalle_operacion );

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
        $forma_pago = $doc_encabezado->forma_pago;

        // Cartera ó Caja (DB)
        VentaController::contabilizar_movimiento_debito( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion );

        // Crear registro del pago: cuenta por cobrar(cartera) o recaudo
        VentaController::crear_registro_pago( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion );

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