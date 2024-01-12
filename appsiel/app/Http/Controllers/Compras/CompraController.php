<?php

namespace App\Http\Controllers\Compras;

use Illuminate\Http\Request;

use App\Http\Controllers\Inventarios\InventarioController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\Sistema\ModeloController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Core\Empresa;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvCostoPromProducto;

use App\Compras\ComprasTransaccion;
use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasMovimiento;
use App\Compras\NotaCredito;
use App\Compras\Proveedor;

use App\Ventas\ResolucionFacturacion;

use App\Contabilidad\ContabMovimiento;

use App\CxP\DocumentosPendientes;
use App\CxP\CxpMovimiento;
use App\CxP\CxpAbono;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoCuentaBancaria;

use App\Contabilidad\Impuesto;
use App\Inventarios\Services\AverageCost;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class CompraController extends TransaccionController
{
    /* El método index() está en TransaccionController */

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de compras
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        // Enviar valores predeterminados
        // WARNING!!!! Este motivo es de INVENTARIOS
        $motivos = ['11-entrada'=>'Compras nacionales'];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = new TablaIngresoLineaRegistros( ComprasTransaccion::get_datos_tabla_ingreso_lineas_registros( $this->transaccion, $motivos ) );

        return $this->crear( $this->app, $this->modelo, $this->transaccion, 'compras.create', $tabla );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $lineas_registros_originales = json_decode( $request->all()['lineas_registros'] );
        $registros_medio_pago = new RegistrosMediosPago;
        
        $campo_lineas_recaudos = $registros_medio_pago->depurar_tabla_registros_medios_recaudos( $request->all()['lineas_registros_medios_recaudo'],self::get_total_documento_desde_lineas_registros( $lineas_registros_originales ) );

        // 1ro. Crear documento de ENTRADA de inventarios (REMISIÓN)
        // WARNING. HECHO MANUALMENTE
        $request['entrada_almacen_id'] = $this->crear_entrada_almacen( $request );

        // 2do. Crear encabezado del documento
        $doc_encabezado = $this->crear_encabezado_documento($request, $request->url_id_modelo);

        // 3ro. Crear líneas de registros del documento
        $request['creado_por'] = Auth::user()->email;
        $request['registros_medio_pago'] = $registros_medio_pago->get_datos_ids( $campo_lineas_recaudos );
        CompraController::crear_registros_documento( $request, $doc_encabezado );

        return redirect('compras/'.$doc_encabezado->id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    /*
        Este método crea el documento de salida de inventarios de los productos vendidos (Remisión de compras)
        WARNING: Se asignan manualmente algunos campos de a tablas inv_doc_inventarios  
    */
    public function crear_entrada_almacen(Request $request)
    {
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('compras');

        // Modelo del encabezado del documento
        $ea_modelo_id = $parametros['ea_modelo_id'];
        $ea_tipo_transaccion_id = $parametros['ea_tipo_transaccion_id'];
        $ea_tipo_doc_app_id = $parametros['ea_tipo_doc_app_id'];
        
        $lineas_registros = json_decode($request->lineas_registros);
        
        // Se crea el documento, se cambia temporalmente el tipo de transacción y el tipo_doc_app
        $tipo_transaccion_id_original = $request['core_tipo_transaccion_id'];
        $core_tipo_doc_app_id_original = $request['core_tipo_doc_app_id'];

        $request['core_tipo_transaccion_id'] = $ea_tipo_transaccion_id;
        $request['core_tipo_doc_app_id'] = $ea_tipo_doc_app_id;
        $request['estado'] = 'Facturada';
        $entrada_almacen_id = InventarioController::crear_documento($request, $lineas_registros, $ea_modelo_id);

        // Se revierten los datos cambiados
        $request['core_tipo_transaccion_id'] = $tipo_transaccion_id_original;
        $request['core_tipo_doc_app_id'] = $core_tipo_doc_app_id_original;
        $request['estado'] = 'Activo';

        return $entrada_almacen_id;
    }


    /*
        Crea los registros, el movimiento y la contabilización de un documento. 
        Todas estas operaciones se crean juntas porque se almacenena en cada iteración de las lineas de registros
        No Devuelve nada
    */
    public static function crear_registros_documento( Request $request, $doc_encabezado )
    {
        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        $datos = $request->all();

        // Se crean los registro con base en el documento de inventario ya creado
        // lineas_registros solo tiene el ID del documentos de inventario
        // entrada_almacen_id también puede ser el ID de una devolución en compras o varias separadas por coma
        $lineas_registros = [(object)[ 'id_doc' => $doc_encabezado->entrada_almacen_id ]];

        CompraController::crear_lineas_registros_compras( $datos, $doc_encabezado, $lineas_registros );

        return true;
    }


    public static function get_total_documento_desde_lineas_registros( $lineas_registros )
    {
        $total_documento = 0;
        
        $cantidad_registros = count( $lineas_registros );
        
        $entrada_almacen_id = '';
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $total_documento += (float)$lineas_registros[$i]->precio_total;
        }
        return $total_documento;
    }

    // Se crean los registros con base en los registros de las entradas de almacén
    public static function crear_lineas_registros_compras( $datos, $doc_encabezado, $lineas_registros )
    {
        $total_documento = 0;
        // Por cada entrada de almacén pendiente
        $cantidad_registros = count( $lineas_registros );

        $lineas_registros_originales = json_decode( $datos['lineas_registros'] );
        
        $entrada_almacen_id = '';
        $primera = true;
        for ($i=0; $i < $cantidad_registros ; $i++)
        {
            $doc_entrada_id = (int)$lineas_registros[$i]->id_doc;

            $registros_entrada = InvDocRegistro::where( 'inv_doc_encabezado_id', $doc_entrada_id )->get();

            $linea = 0;
            $linea_datos = [];
            $detalle_operacion = '';
            foreach ($registros_entrada as $un_registro)
            {
                // Nota: $un_registro contiene datos de inventarios 
                $cantidad = $un_registro->cantidad;
                $total_base_impuesto = $un_registro->costo_total;

                $tasa_impuesto = Impuesto::get_tasa( $un_registro->inv_producto_id, $doc_encabezado->proveedor_id, 0 );

                // El costo_unitario se guardó con los descuentos restados
                $precio_unitario = $un_registro->costo_unitario * ( 1 + $tasa_impuesto  / 100 );

                $precio_total = $precio_unitario * $cantidad;

                $tasa_descuento = 0;
                $valor_total_descuento = 0;
                if ( isset( $lineas_registros_originales[ $linea ]->tasa_descuento ) )
                {
                    $tasa_descuento = $lineas_registros_originales[ $linea ]->tasa_descuento;
                    $valor_total_descuento = $lineas_registros_originales[ $linea ]->valor_total_descuento;
                }

                $linea_datos = [ 'inv_bodega_id' => $un_registro->inv_bodega_id ] +
                                [ 'inv_motivo_id' => $un_registro->inv_motivo_id ] +
                                [ 'inv_producto_id' => $un_registro->inv_producto_id ] +
                                [ 'precio_unitario' => $precio_unitario ] +
                                [ 'cantidad' => $cantidad ] +
                                [ 'precio_total' => $precio_total ] +
                                [ 'base_impuesto' =>  $total_base_impuesto ] +
                                [ 'tasa_impuesto' => $tasa_impuesto ] +
                                [ 'valor_impuesto' => ( $precio_total - $total_base_impuesto ) ] +
                                [ 'tasa_descuento' => $tasa_descuento ] +
                                [ 'valor_total_descuento' => $valor_total_descuento ] +
                                [ 'creado_por' => Auth::user()->email ] +
                                [ 'estado' => 'Activo' ];

                
                ComprasDocRegistro::create( 
                                        $datos + 
                                        [ 'compras_doc_encabezado_id' => $doc_encabezado->id ] +
                                        $linea_datos
                                    );

                $datos['consecutivo'] = $doc_encabezado->consecutivo;
                ComprasMovimiento::create( 
                                        $datos +
                                        $linea_datos
                                    );
                
                // Contabilizar
                $detalle_operacion = $datos['descripcion'];

                CompraController::contabilizar_movimiento_debito( $datos + $linea_datos, $detalle_operacion );

                $total_documento += $precio_total;

                $linea++;
            } // Fin por cada registro de la entrada

            // Marcar la entrada como facturada
            $record = InvDocEncabezado::find( $doc_entrada_id );
            if ($record != null) {
                $record->estado = 'Facturada';
                $record->save();
            }

            // Se va creando un listado de entradas separadas por coma 
            if ($primera)
            {
                $entrada_almacen_id = $doc_entrada_id;
                $primera = false;
            }else{
                $entrada_almacen_id .= ','.$doc_entrada_id;
            }

        }

        $doc_encabezado->valor_total = $total_documento;
        $doc_encabezado->entrada_almacen_id = $entrada_almacen_id;
        $doc_encabezado->save();

        // Un solo registro de la cuenta por pagar (CR)
        $forma_pago = $datos['forma_pago']; // esto se debe determinar de acuerdo a algún parámetro en la configuración, $datos['forma_pago']

        CompraController::contabilizar_movimiento_credito( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion );

        // Crear registro del pago: cuenta por pagar o pago de tesorería
        CompraController::crear_registro_pago( $forma_pago, $datos + $linea_datos, $total_documento, $detalle_operacion ); 

        return true;
    }

    public static function contabilizar_movimiento_debito( $datos, $detalle_operacion )
    {
        // IVA descontable (DB)
        // Si se ha liquidado impuestos en la transacción
        if ( isset( $datos['tasa_impuesto'] ) && $datos['tasa_impuesto'] > 0 )
        {
            $cta_impuesto_compras_id = InvProducto::get_cuenta_impuesto_compras( $datos['inv_producto_id'] );
            ContabilidadController::contabilizar_registro2( $datos, $cta_impuesto_compras_id, $detalle_operacion, abs( $datos['valor_impuesto'] ), 0);
        }

        $producto = InvProducto::find( $datos['inv_producto_id'] );
        $motivo = InvMotivo::find( $datos['inv_motivo_id'] );

        if ( $producto->tipo == 'producto')
        {
            // Se toma la cuenta del motivo
            $cta_contrapartida_id = $motivo->cta_contrapartida_id;
        }else{
            // Se toma la cuenta del Grupo de Inventarios
            $cta_contrapartida_id = InvProducto::get_cuenta_inventarios( $producto->id );
        }

            
        ContabilidadController::contabilizar_registro2( $datos, $cta_contrapartida_id, $detalle_operacion, abs( $datos['base_impuesto'] ), 0);
    }

    public static function contabilizar_movimiento_credito( $forma_pago, $datos, $total_documento, $detalle_operacion )
    {
        /*
            Se crea un SOLO registro contable de la cuenta por pagar (Crédito) o la tesorería (Contado)
            WARNING. Esto debe ser un parámetro de la configuración. Si se quiere llevar la factura contado a la caja directamente o si se causa una cuenta por pagar
        */

        if ( $forma_pago == 'credito')
        {
            // Contabilizar Cta. Por Pagar (CR)

            // Se resetean estos campos del registro
            $datos['inv_producto_id'] = 0;
            $datos['cantidad '] = 0;
            $datos['tasa_impuesto'] = 0;
            $datos['base_impuesto'] = 0;
            $datos['valor_impuesto'] = 0;
            $datos['inv_bodega_id'] = 0;

            $cxp_id = Proveedor::get_cuenta_por_pagar( $datos['proveedor_id'] );
            ContabilidadController::contabilizar_registro2( $datos, $cxp_id, $detalle_operacion, 0, abs($total_documento) );
        }

        if ( $forma_pago == 'contado')
        {
            $caja = TesoCaja::get()->first();

            if ( empty( $datos['registros_medio_pago'] ) )
            {
                $cta_caja_id = $caja->contab_cuenta_id;
                ContabilidadController::contabilizar_registro2( $datos, $cta_caja_id, $detalle_operacion, 0, abs($total_documento), $caja->id, 0 );

            }else{

                // WARNING!!! Por ahora solo se está aceptando un solo medio de pago
                $contab_cuenta_id = $caja->contab_cuenta_id;

                $registros_medio_pago = $datos['registros_medio_pago'];
                foreach ($registros_medio_pago as $linea_registro_medio_pago) {
                    
                    $teso_caja_id = $linea_registro_medio_pago['teso_caja_id'];

                    if ($teso_caja_id != 0)
                    {
                        $contab_cuenta_id = TesoCaja::find( $teso_caja_id )->contab_cuenta_id;
                    }

                    $teso_cuenta_bancaria_id = $linea_registro_medio_pago['teso_cuenta_bancaria_id'];
                    if ($teso_cuenta_bancaria_id != 0)
                    {
                        $contab_cuenta_id = TesoCuentaBancaria::find( $teso_cuenta_bancaria_id )->contab_cuenta_id;
                    }

                    ContabilidadController::contabilizar_registro2( $datos, $contab_cuenta_id, $detalle_operacion, 0, $linea_registro_medio_pago['valor_recaudo'], $teso_caja_id, $teso_cuenta_bancaria_id );
                }                
            }

        }

    }

    public static function crear_registro_pago( $forma_pago, $datos, $total_documento, $detalle_operacion )
    {
        if ( $forma_pago == 'credito')
        {
            // Cargar a los registros de cuentas por pagar
            $datos['modelo_referencia_tercero_index'] = 'App\Compras\Proveedor';
            $datos['referencia_tercero_id'] = $datos['proveedor_id'];
            $datos['valor_documento'] = $total_documento;
            $datos['valor_pagado'] = 0;
            $datos['saldo_pendiente'] = $total_documento;
            $datos['estado'] = 'Pendiente';
            DocumentosPendientes::create( $datos );
        }
        
        if ( $forma_pago == 'contado')
        {
            if (!isset($datos['registros_medio_pago']) )
            {
                $datos['registros_medio_pago'] = [];
            }
            $teso_movimiento = new TesoMovimiento();
            $teso_movimiento->almacenar_registro_pago_contado( $datos, $datos['registros_medio_pago'], 'salida', $total_documento );
        }
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

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );

        $doc_encabezado = ComprasDocEncabezado::get_registro_impresion( $id );
        $docs_relacionados = ComprasDocEncabezado::get_documentos_relacionados( $doc_encabezado );

        $doc_registros = ComprasDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $empresa = $this->empresa;

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $registros_tesoreria = TesoMovimiento::get_registros_un_documento( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo )->first();
        $medios_pago = View::make('tesoreria.incluir.show_medios_pago', compact('registros_tesoreria'))->render();

        // Datos de los PAGOS aplicados a la factura
        $abonos = CxpAbono::get_abonos_documento( $doc_encabezado );

        // Datos de Notas Crédito aplicadas a la factura
        $notas_credito = NotaCredito::get_notas_aplicadas_factura( $doc_encabezado->id );

        $documento_vista = '';

        $id_transaccion = $doc_encabezado->core_tipo_transaccion_id;

        $miga_pan = $this->get_array_miga_pan( $this->app, $this->modelo, $doc_encabezado->documento_transaccion_prefijo_consecutivo );

        $modelo_controller = New ModeloController();
        $acciones = $modelo_controller->acciones_basicas_modelo( $this->modelo, $this->variables_url );

        $url_crear = $acciones->create;
        
        $vista = 'compras.show';

        if( !is_null( Input::get('vista') ) )
        {
            $vista = Input::get('vista');
        }

        return view( $vista, compact( 'id', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan','doc_encabezado', 'doc_registros', 'registros_contabilidad', 'abonos', 'notas_credito', 'empresa', 'docs_relacionados','url_crear','medios_pago') );
    }

    /*
        Imprimir
    */
    public function imprimir( $id )
    {
        $doc_encabezado = ComprasDocEncabezado::get_registro_impresion( $id );

        $doc_registros = ComprasDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $empresa = Empresa::find( $doc_encabezado->core_empresa_id );

        $registros_contabilidad = TransaccionController::get_registros_contabilidad( $doc_encabezado );

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)->where('estado','Activo')->get()->last(); 

        $documento_vista = View::make( 'compras.formatos_impresion.'.Input::get('formato_impresion_id'), compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad', 'resolucion' ) )->render();

        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = 'Letter';//array(0,0,50,800);//'A4';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');        
    }
    
    // Parámetro enviados por GET
    public function consultar_proveedores()
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

        $proveedores = Proveedor::leftJoin('core_terceros','core_terceros.id','=','compras_proveedores.core_tercero_id')->leftJoin('compras_condiciones_pago','compras_condiciones_pago.id','=','compras_proveedores.condicion_pago_id')->where('compras_proveedores.estado','Activo')->where('core_terceros.'.$campo_busqueda,$operador,$texto_busqueda)->select('compras_proveedores.id AS proveedor_id','compras_proveedores.liquida_impuestos','compras_proveedores.clase_proveedor_id','core_terceros.id AS core_tercero_id','core_terceros.descripcion AS nombre_proveedor','core_terceros.numero_identificacion','compras_proveedores.inv_bodega_id','compras_condiciones_pago.dias_plazo')->get()->take( 7 );

        $html = '<div class="list-group">';
        $es_el_primero = true;
        $ultimo_item = 0;
        $num_item = 1;
        $cantidad_proveedores = count( $proveedores->toArray() );
        foreach ($proveedores as $linea) 
        {
            $primer_item = 0;
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
                $primer_item = 1;
            }

            if ( $num_item == $cantidad_proveedores)
            {
                $ultimo_item = 1;
            }

            $html .= '<a class="list-group-item list-group-item-proveedor '.$clase.'" data-proveedor_id="'.$linea->proveedor_id.
                                '" data-primer_item="'.$primer_item.
                                '" data-ultimo_item="'.$ultimo_item.
                                '" data-nombre_proveedor="'.$linea->nombre_proveedor.
                                '" data-clase_proveedor_id="'.$linea->clase_proveedor_id.
                                '" data-liquida_impuestos="'.$linea->liquida_impuestos.
                                '" data-core_tercero_id="'.$linea->core_tercero_id.
                                '" data-numero_identificacion="'.$linea->numero_identificacion.
                                '" data-inv_bodega_id="'.$linea->inv_bodega_id.
                                '" data-dias_plazo="'.$linea->dias_plazo.
                                '" > '.$linea->nombre_proveedor.' ('.number_format($linea->numero_identificacion,0,',','.').') </a>';
            $num_item++;
        }

        // Linea crear nuevo registro
        $modelo_id = 146; // App\Ventas\Proveedores
        $html .= '<a href="'.url('compras_proveedores/create?id=9&id_modelo='.$modelo_id.'&id_transaccion').'" target="_blank" class="list-group-item list-group-item-sugerencia list-group-item-warning" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nuevo </a>';

        $html .= '</div>';

        return $html;
    }
    


    // Parámetro enviados por GET
    public function consultar_existencia_producto()
    {
        $bodega_id = (int)Input::get('bodega_id');
        $proveedor_id = (int)Input::get('proveedor_id');
        $producto_id = (int)Input::get('producto_id');
        
        $producto = InvProducto::where('inv_productos.id',$producto_id )
                                ->select(
                                            'inv_productos.id',
                                            'inv_productos.tipo',
                                            'inv_productos.descripcion',
                                            'inv_productos.precio_compra',
                                            'inv_productos.precio_venta'
                                        )
                                ->get()
                                ->first();

        
        // Se convierte en array para manipular facilmente sus campos
        if ( !is_null($producto) ) {
            $producto = $producto->toArray();  
        }else{
            $producto = [];
        }


        // $producto es un array
        if( !empty($producto) )
        {
            $costo_promedio = InvCostoPromProducto::where('inv_bodega_id','=',$bodega_id)
                                    ->where('inv_producto_id','=',$producto_id)
                                    ->value('costo_promedio');
            if ( ! ($costo_promedio>0) ) 
            {
                $costo_promedio = 0;
            }


            /*
                Falta el manejo de los descuentos.
            */

            // Precios traido del movimiento de compras. El último precio liquidado al proveedor para ese producto.
            $precio_unitario = ComprasMovimiento::get_ultimo_precio_producto( $proveedor_id, $producto_id )->precio_unitario;

            // Los impuestos en compras se obtinen del precio_compra

            $tasa_impuesto = Impuesto::get_tasa( $producto_id, $proveedor_id, 0 );

            $base_impuesto = ( (float)$producto['precio_compra'] ) / ( 1 + $tasa_impuesto / 100 );
            $valor_impuesto = (float)$producto['precio_compra'] - $base_impuesto;

            if ( !Input::get('liquida_impuestos') ) 
            {
                $tasa_impuesto = 0;
                $base_impuesto = 0;
                $valor_impuesto = 0;
            }
            

            /*
                PENDIENTE: VALIDACIONES DE FECHA


            */

            // Obtener existencia actual
            $existencia_actual = InvMovimiento::get_existencia_actual( $producto_id, $bodega_id, Input::get('fecha') );

            $producto = array_merge($producto,['costo_promedio'=>$costo_promedio]);

            $producto = array_merge($producto, [ 'existencia_actual' => $existencia_actual ],
                                                [ 'tipo' => $producto['tipo'] ],
                                                [ 'costo_promedio' => $costo_promedio ],
                                                [ 'precio_compra' => $precio_unitario ],
                                                [ 'base_impuesto' => $base_impuesto ],
                                                [ 'tasa_impuesto' => $tasa_impuesto ],
                                                [ 'valor_impuesto' => $valor_impuesto ]
                                    );
        }

        //print_r($producto);
        return $producto;
    }



    /*
        Proceso de eliminar FACTURA DE COMPRAS
        Se eliminan los registros de:
            - cxp_documentos_pendientes (se debe verificar que no tenga un abono, sino se debe eliminar primero el abono) y su movimiento en contab_movimientos
            - inv_movimientos de la entrada de almacén y su contabilidad. Además se actualiza el estado a Anulado en inv_doc_registros e inv_doc_encabezados
            - compras_movimientos y su contabilidad. Además se actualiza el estado a Anulado en compras_doc_registros y compras_doc_encabezados

            A FUTURO: se debe preguntar si se elimina o no la Entrada de almacén
    */
    public static function anular_factura(Request $request)
    {
        $factura = ComprasDocEncabezado::find( $request->factura_id );

        if($factura->enviado_electronicamente())
        {
            return redirect( 'compras/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error','Documento NO puede ser anulado. Ya fue enviado electrónicamente a la DIAN.');
        }

        $array_wheres = ['core_empresa_id'=>$factura->core_empresa_id, 
            'core_tipo_transaccion_id' => $factura->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $factura->core_tipo_doc_app_id,
            'consecutivo' => $factura->consecutivo];

        // Verificar si la factura tiene abonos, si tiene no se puede eliminar
        $cantidad = CxpAbono::where('doc_cxp_transacc_id',$factura->core_tipo_transaccion_id)
                            ->where('doc_cxp_tipo_doc_id',$factura->core_tipo_doc_app_id)
                            ->where('doc_cxp_consecutivo',$factura->consecutivo)
                            ->count();

        if($cantidad != 0)
        {
            return redirect( 'compras/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error','Factura NO puede ser eliminada. Tiene Abonos aplicados.');
        }

        // Verificar SI tiene notas crédito aplicada a factura
        $cantidad = ComprasDocEncabezado::where( 'compras_doc_relacionado_id', $factura->id )->count();

        if($cantidad != 0)
        {
            return redirect( 'compras/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error','Factura NO puede ser eliminada. Tiene Notas crédito aplicadas.');
        }
            

        $modificado_por = Auth::user()->email;

        // 1ro. Anular documento asociado de inventarios
        // Obtener las entradas de almacén relacionadas con la factura y anularlas o dejarlas en estado Pendiente
        $ids_documentos_relacionados = explode( ',', $factura->entrada_almacen_id );
        $cant_registros = count($ids_documentos_relacionados);
        for ($i=0; $i < $cant_registros; $i++)
        { 
            $entrada_almacen = InvDocEncabezado::find( $ids_documentos_relacionados[$i] );
            if ( $entrada_almacen != null )
            {
                if ( $request->anular_entrada_almacen ) //  anular_entrada_almacen es tipo boolean
                {
                    // Antes de anular la entrada de almacén, por cada producto ingresado en la factura
                    // Validar saldos negativos en movimientos de inventarios
                    $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores_todas_lineas( $entrada_almacen, 'no_fecha', 'anular', 'salida' ); // al anular la entrada de almacén se hace una salida de inventarios
                    
                    if( $linea_saldo_negativo != '0')
                    {
                        return redirect( 'compras/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('mensaje_error',$linea_saldo_negativo);
                    }

                    InventarioController::anular_documento_inventarios( $entrada_almacen->id );
                }else{
                    $entrada_almacen->update(['estado'=>'Pendiente', 'modificado_por' => $modificado_por]);
                }    
            }
        }

        // 2do. Borrar registros contables del documento
        ContabMovimiento::where($array_wheres)->delete();

        // 3ro. Se elimina el documento del movimimeto de cuentas por pagar
        CxpMovimiento::where($array_wheres)->delete();

        // 4to. Se elimina el documento del movimimeto de Tesorería
        TesoMovimiento::where($array_wheres)->delete();

        // 5to. Se elimina el movimiento de compras
        ComprasMovimiento::where($array_wheres)->delete();
        
        // 6to. Se marcan como anulados los registros del documento
        ComprasDocRegistro::where( 'compras_doc_encabezado_id', $factura->id )->update( [ 'estado' => 'Anulado', 'modificado_por' => $modificado_por] );

        // 7mo. Se marca como anulado el documento
        $factura->update([ 'estado'=>'Anulado', 'entrada_almacen_id' => '', 'modificado_por' => $modificado_por]);

        return redirect( 'compras/'.$request->factura_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion )->with('flash_message','Factura de compra ANULADA correctamente.');
        
    }

    
    // Parámetros enviados por GET
    // Las facturas de compras representan una factura del proveedor con un prefijo y consecutivo que deben ser únicos por proveedor
    public function validar_documento_proveedor()
    {
        $ya_esta = 'false';
        $registro = ComprasDocEncabezado::where( 'proveedor_id', Input::get('proveedor_id') )
                                        ->where( 'doc_proveedor_prefijo', Input::get('doc_proveedor_prefijo') )
                                        ->where( 'doc_proveedor_consecutivo', Input::get('doc_proveedor_consecutivo') )
                                        ->where( 'estado', '<>', 'Anulado' )
                                        ->get()
                                        ->first();
        if ( !is_null($registro) )
        {
            $ya_esta = 'true';
        }

        return $ya_esta;
    }

    
    // Parámetro enviados por GET
    // Cuando se hace la Entrada por compras y queda pendiente hacer la factura
    public function consultar_entradas_pendientes()
    {
        $entradas = InvDocEncabezado::get_documentos_por_transaccion( 35, Input::get('core_tercero_id'), 'Pendiente' );

        if( empty( $entradas->toArray() ) ){ return 'sin_registros'; }

        return View::make( 'compras.incluir.entradas_almacen_pendientes', compact('entradas') )->render();
    }

    
    // Petición AJAX. Parámetro enviados por GET
    public function get_formulario_edit_registro()
    {
        $linea_factura = ComprasDocRegistro::get_un_registro( Input::get('linea_registro_id') );

        $factura = ComprasDocEncabezado::get_registro_impresion( $linea_factura->compras_doc_encabezado_id );

        $entrada_almacen = InvDocEncabezado::get_registro_impresion( $factura->entrada_almacen_id );
        $linea_entrada_almacen = InvDocRegistro::where( 'inv_doc_encabezado_id', $factura->entrada_almacen_id )
                                    ->where( 'inv_producto_id', $linea_factura->producto_id )
                                    ->where( 'cantidad', $linea_factura->cantidad )
                                    ->get()
                                    ->first();
        
        $saldo_a_la_fecha = InvMovimiento::get_existencia_actual( $linea_entrada_almacen->inv_producto_id, $linea_entrada_almacen->inv_bodega_id, $entrada_almacen->fecha );

        $id = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $id_transaccion = Input::get('id_transaccion');

        $producto = InvProducto::find( $linea_entrada_almacen->inv_producto_id );

        $formulario = View::make( 'compras.incluir.formulario_editar_registro', compact('linea_factura','id','id_modelo','id_transaccion','linea_entrada_almacen','entrada_almacen','saldo_a_la_fecha','producto') )->render();

        return $formulario;
    }

    // MODIFICACIÓN DE REGISTROS DE DOCUMENTOS COMPRAS
    public function doc_registro_guardar( Request $request )
    {

        $linea_registro = ComprasDocRegistro::find( $request->linea_factura_id );
        $doc_encabezado = ComprasDocEncabezado::find( $linea_registro->compras_doc_encabezado_id );
        
        // NO ACTUALIZA BIEN LA CONTABILIDAD DEL MOV DE TESORERIA,POR LOS MEDIOS DE RECAUDOS
        //return redirect( 'compras/'.$doc_encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','En estos momentos no se pueden editar registros. Consultar con el administrador.');

        // Verificar si la factura tiene abonos, si tiene no se pueden modificar sus registros
        $abonos = CxpAbono::where('doc_cxp_transacc_id',$doc_encabezado->core_tipo_transaccion_id)->where('doc_cxp_tipo_doc_id',$doc_encabezado->core_tipo_doc_app_id)->where('doc_cxp_consecutivo',$doc_encabezado->consecutivo)->get()->toArray();

        if( !empty($abonos) )
        {
            return redirect( 'compras/'.$doc_encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Los registros de la Factura NO pueden ser modificados. Factura tiene Pagos de CXP aplicados (Tesorería).');
        }

        $viejo_total_encabezado = $doc_encabezado->valor_total;

        $cantidad = $request->cantidad;
        $valor_total_descuento = $request->valor_total_descuento;
        $tasa_descuento = $request->tasa_descuento;

        $precio_unitario = $request->precio_unitario - ( $valor_total_descuento / $cantidad );

        $precio_total = $precio_unitario * $cantidad;

        $base_impuesto = $precio_total / ( 1 + $linea_registro->tasa_impuesto / 100);
        $valor_impuesto = $precio_total - $base_impuesto;

        // 1. Actualizar total del encabezado de la factura
        $nuevo_total_encabezado = $doc_encabezado->valor_total - $linea_registro->precio_total + $precio_total;
        $doc_encabezado->update(
                                    ['valor_total' => $nuevo_total_encabezado]
                                );

        // 2. Actualiza total de la cuenta por pagar o Tesorería
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

        // 3. Actualiza movimiento de compras
        ComprasMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
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
                                    'tasa_descuento' => $tasa_descuento,
                                    'valor_total_descuento' => $valor_total_descuento
                                ] );

        // 4. Actualizar movimiento contable del registro de la factura
        // IVA descontable (DB)
        // Si se ha liquidado impuestos en la transacción
        if ( $linea_registro->tasa_impuesto > 0 )
        {
            $cta_impuesto_compras_id = InvProducto::get_cuenta_impuesto_compras( $linea_registro->inv_producto_id );
            ContabMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                        ->where('consecutivo',$doc_encabezado->consecutivo)
                        ->where('inv_producto_id',$linea_registro->inv_producto_id)
                        ->where('cantidad',$linea_registro->cantidad)
                        ->where('valor_debito',$linea_registro->valor_impuesto)
                        ->where('contab_cuenta_id',$cta_impuesto_compras_id)
                        ->update( [ 
                                    'valor_debito' => $valor_impuesto,
                                    'valor_saldo' => $valor_impuesto,
                                    'cantidad' => $cantidad,
                                    'base_impuesto' => $base_impuesto,
                                    'valor_impuesto' => $valor_impuesto
                                ] );
        }


        $producto = InvProducto::find( $linea_registro->inv_producto_id );
        $motivo = InvMotivo::find( $linea_registro->inv_motivo_id );

        if ( $producto->tipo == 'producto')
        {
            // Se toma la cuenta del motivo
            $cta_contrapartida_id = $motivo->cta_contrapartida_id;
        }else{
            // Se toma la cuenta del Grupo de Inventarios
            $cta_contrapartida_id = InvProducto::get_cuenta_inventarios( $producto->id );
        }

        ContabMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$linea_registro->cantidad)
                    ->where('valor_debito',$linea_registro->base_impuesto)
                    ->where('contab_cuenta_id',$cta_contrapartida_id)
                    ->update( [ 
                                'valor_debito' => $base_impuesto,
                                'valor_saldo' => $base_impuesto,
                                'cantidad' => $cantidad,
                                'base_impuesto' => $base_impuesto,
                                'valor_impuesto' => $valor_impuesto
                            ] );


        // Contabilizar Cta. Por Pagar (CR) o Caja/Banco Si es cuenta por pagar
        ContabMovimiento::where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                        ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                        ->where('consecutivo',$doc_encabezado->consecutivo)
                        ->where( 'valor_debito', 0)
                        ->where( 'valor_credito', $viejo_total_encabezado * -1 )
                        ->update( [ 
                                    'valor_credito' => $nuevo_total_encabezado * -1,
                                    'valor_saldo' => $nuevo_total_encabezado * -1
                                ] );

        // 5. Actualizar el registro del documento de inventario
        $inv_doc_encabezado = InvDocEncabezado::find( $doc_encabezado->entrada_almacen_id );
        //$costo_total_actual = $costo_unitario_actual * $linea_registro->cantidad;

        $costo_total_actual = $linea_registro->precio_total / ( 1 + $linea_registro->tasa_impuesto / 100 );
        $costo_unitario = $precio_unitario / ( 1 + $linea_registro->tasa_impuesto / 100);
        $costo_total = $costo_unitario * $cantidad;
        $inv_doc_registro = InvDocRegistro::where('inv_doc_encabezado_id', $doc_encabezado->entrada_almacen_id)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$linea_registro->cantidad)
                    ->get()
                    ->first();
                    
        $inv_doc_registro->update( [ 
                                'costo_unitario' => $costo_unitario,
                                'cantidad' => $cantidad,
                                'costo_total' => $costo_total
                            ] );

        // 6. Actualiza movimiento de inventarios
        InvMovimiento::where('core_tipo_transaccion_id',$inv_doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$inv_doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$inv_doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$linea_registro->cantidad)
                    ->update( [ 
                                'costo_unitario' => $costo_unitario,
                                'cantidad' => $cantidad,
                                'costo_total' => $costo_total
                            ] );

        // Se CALCULA el costo promedio del movimiento, si no existe será el enviado en el request

        $average_cost_serv = new AverageCost();
        $costo_prom = $average_cost_serv->calculate_average_cost($inv_doc_registro->inv_bodega_id, $linea_registro->inv_producto_id, $costo_unitario, $doc_encabezado->fecha, $cantidad);

        // Actualizo/Almaceno el costo promedio
        $average_cost_serv->set_costo_promedio($inv_doc_registro->inv_bodega_id,$linea_registro->inv_producto_id,$costo_prom);

        // 7. Actualizar movimiento contable del registro del documento de inventario
        // Inventarios (DB)
        $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea_registro->inv_producto_id );
        ContabMovimiento::where('core_tipo_transaccion_id',$inv_doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$inv_doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$inv_doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$linea_registro->cantidad)
                    //->where('valor_debito', $costo_total_actual)
                    ->where('contab_cuenta_id',$cta_inventarios_id)
                    ->update( [ 
                                'valor_debito' => $costo_total,
                                'valor_saldo' => $costo_total,
                                'cantidad' => $cantidad
                            ] );


        $motivo = InvMotivo::find( $inv_doc_registro->inv_motivo_id );

        if ( $producto->tipo == 'producto')
        {
            // Se toma la cuenta del motivo
            $cta_contrapartida_id = $motivo->cta_contrapartida_id;
        }else{
            // Se toma la cuenta del Grupo de Inventarios
            $cta_contrapartida_id = InvProducto::get_cuenta_inventarios( $producto->id );
        }

        ContabMovimiento::where('core_tipo_transaccion_id',$inv_doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id',$inv_doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo',$inv_doc_encabezado->consecutivo)
                    ->where('inv_producto_id',$linea_registro->inv_producto_id)
                    ->where('cantidad',$linea_registro->cantidad)
                    //->where('valor_credito', ($costo_total_actual * -1) )
                    ->where('contab_cuenta_id',$cta_contrapartida_id)
                    ->update( [ 
                                'valor_credito' => $costo_total * -1,
                                'valor_saldo' => $costo_total * -1,
                                'cantidad' => $cantidad
                            ] );


        // 8. Actualizar el registro del documento de factura
        $linea_registro->update( [
                                    'precio_unitario' => $precio_unitario,
                                    'cantidad' => $cantidad,
                                    'precio_total' => $precio_total,
                                    'base_impuesto' => $base_impuesto,
                                    'valor_impuesto' => $valor_impuesto,
                                    'tasa_descuento' => $tasa_descuento,
                                    'valor_total_descuento' => $valor_total_descuento
                                ] );


        return redirect( 'compras/'.$doc_encabezado->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('flash_message','El registro de la Factura de compras fue MODIFICADO correctamente.');
    }

}