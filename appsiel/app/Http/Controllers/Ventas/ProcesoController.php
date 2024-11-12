<?php

namespace App\Http\Controllers\Ventas;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Ventas\VentaController;
use App\Http\Controllers\Ventas\NotaCreditoController;
use App\Http\Controllers\Inventarios\InventarioController;

use Carbon\Carbon;

use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;
use App\Sistema\Aplicacion;

use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\Ventas\VtasMovimiento;
use App\Inventarios\InvMotivo;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvProducto;

use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\RegistrosMediosPago;

use App\Ventas\Services\DocumentHeaderService;
use App\Ventas\Services\TreasuryServices;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class ProcesoController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function recontabilizar_documento_factura($documento_id)
    {
        ProcesoController::recontabilizar_documento($documento_id);
        return redirect('ventas/' . $documento_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'Documento Recontabilizado.');
    }

    public function recontabilizar_documento_nota_credito($documento_id)
    {
        ProcesoController::recontabilizar_nota_credito($documento_id);
        return redirect('ventas/' . $documento_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') . '&vista=ventas.notas_credito.show')->with('flash_message', 'Documento Recontabilizado.');
    }

    // Recontabilizar un documento dada su ID
    public static function recontabilizar_documento($documento_id)
    {
        $documento = VtasDocEncabezado::find($documento_id);

        dd('Error metodo contabilizar_movimiento_debito(). ProcesoController::get_lineas_medios_recaudos().');

        // Recontabilizar la remisión
        /* ¿Qué hacer cuando tiene varias remisiones?
        if ( $documento->remision_doc_encabezado_id != 0)
        {
            InvProcesoController::recontabilizar_documento( $documento->remision_doc_encabezado_id );
        }
        */

        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = VtasDocRegistro::where('vtas_doc_encabezado_id', $documento->id)->get();

        $total_documento = 0;
        $n = 1;
        $vtas_doc_header_serv = new DocumentHeaderService();
        foreach ($registros_documento as $linea)
        {
            $detalle_operacion = 'Recontabilizado. ' . $linea->descripcion;
            $vtas_doc_header_serv->contabilizar_movimiento_credito($documento->toArray() + $linea->toArray(), $detalle_operacion);
            $total_documento += $linea->precio_total;
            $n++;
        }

        $forma_pago = $documento->forma_pago;

        $datos = $documento->toArray();
        $datos['registros_medio_pago'] = [];
        if ($forma_pago == 'contado')
        {
            $datos['registros_medio_pago'] = ProcesoController::get_lineas_medios_recaudos($documento);
        }
        VentaController::contabilizar_movimiento_debito($forma_pago, $datos, $total_documento, $detalle_operacion);/**/
    }

    public static function get_lineas_medios_recaudos($documento)
    {
        $registro = TesoMovimiento::get_registros_un_documento($documento->core_tipo_transaccion_id, $documento->core_tipo_doc_app_id, $documento->consecutivo)->first();
        
        $medio_recaudo = 'Efectivo'; // MUY MANUAL
        $motivo = '1-Recaudo clientes'; // MUY MANUAL
        $caja = (object)['descripcion' => ''];
        if ($registro->teso_caja_id != 0) {
            $medio_recaudo = 'Cuenta bancaria'; // MUY MANUAL
            $caja = $registro->caja;
        }

        $cuenta_bancaria = (object)['descripcion' => ''];
        if ($registro->teso_cuenta_bancaria_id != 0) {
            $cuenta_bancaria = $registro->cuenta_bancaria;
            $motivo = '5-Pago a proveedores'; // MUY MANUAL
        }

        $campo_lineas_recaudos = json_decode('[{"teso_medio_recaudo_id":"1-' . $medio_recaudo . '","teso_motivo_id":"' . $motivo . '","teso_caja_id":"' . $registro->teso_caja_id . '-' . $caja->descripcion . '","teso_cuenta_bancaria_id":"' . $registro->teso_cuenta_bancaria_id . '-' . $cuenta_bancaria->descripcion . '","valor":"$' . $registro->valor_movimiento . '"}]');

        $registros_medio_pago = new RegistrosMediosPago;
        $lineas_registros_medios_recaudo = 'pendiente';
        $lineas_registros = 'pendiente';
        $total_documento = 'pendiente';
        //$campo_lineas_recaudos
        return $registros_medio_pago->get_datos_ids( $lineas_registros_medios_recaudo, $lineas_registros, $total_documento, 'ventas' );
    }


    /*
     * RECONTABILIZACION FACTURAS DE VENTAS
     */
    public function recontabilizar_documentos_ventas()
    {
        $fecha_desde = Input::get('fecha_desde'); //'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta'); //'2019-10-28';

        if (is_null($fecha_desde) || is_null($fecha_hasta)) {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> recontabilizar_documentos_ventas?fecha_desde=2019-10-28&fecha_hasta=2019-10-28';
            dd('Operación cancelada.');
        }

        // Obtener TODOS los documentos entre las fechas indicadas
        $documentos = VtasDocEncabezado::where('estado', '<>', 'Anulado')
            ->whereIn('core_tipo_transaccion_id', [23]) // 23 = Facturas de ventas
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->get();

        $i = 1;
        foreach ($documentos as $un_documento) {
            ProcesoController::recontabilizar_documento($un_documento->id);
            echo $i . '  ';
            $i++;
        }

        echo '<br>Se Recontabilizaron ' . ($i - 1) . ' documentos de ventas.'; //con sus repectivas remisiones
    }

    /*
     * RECONTABILIZACION NOTAS CRÉDITO DE VENTAS
     */
    public function recontabilizar_notas_creditos_ventas()
    {
        $fecha_desde = Input::get('fecha_desde'); //'2019-10-28';
        $fecha_hasta = Input::get('fecha_hasta'); //'2019-10-28';

        if (is_null($fecha_desde) || is_null($fecha_hasta)) {
            echo 'Se deben enviar las fechas como parámetros en la url. <br> Ejemplo: <br> recontabilizar_documentos_ventas?fecha_desde=2019-10-28&fecha_hasta=2019-10-28';
            dd('Operación cancelada.');
        }

        // Obtener TODOS los documentos entre las fechas indicadas
        $documentos = VtasDocEncabezado::where('estado', '<>', 'Anulado')
            ->whereIn('core_tipo_transaccion_id', [38, 41]) // Nota crédito y NC Directa 
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->get();

        $i = 1;
        foreach ($documentos as $un_documento) {
            ProcesoController::recontabilizar_nota_credito($un_documento->id);
            echo $i . '  ';
            $i++;
        }

        echo '<br>Se Recontabilizaron ' . ($i - 1) . ' documentos de ventas con sus repectivas remisiones.';
    }

    // Recontabilizar una NOTA CRÉDITO dada su ID
    public static function recontabilizar_nota_credito($documento_id)
    {
        $documento = VtasDocEncabezado::find($documento_id);

        // Recontabilizar la devolución
        /* ¿Qué hacer cuando tiene varias devoluciones?
        if ( $documento->remision_doc_encabezado_id != 0)
        {
            InvProcesoController::recontabilizar_documento( $documento->remision_doc_encabezado_id );
        }
        */


        // Eliminar registros contables actuales
        ContabMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->delete();

        // Obtener líneas de registros del documento
        $registros_documento = VtasDocRegistro::where('vtas_doc_encabezado_id', $documento->id)->get();

        $total_documento = 0;
        $n = 1;
        $vtas_doc_header_serv = new DocumentHeaderService();
        foreach ($registros_documento as $linea) {
            $detalle_operacion = 'Recontabilizado. ' . $linea->descripcion;
            NotaCreditoController::contabilizar_movimiento_debito($documento->toArray() + $linea->toArray(), $detalle_operacion);
            $total_documento += $linea->precio_total;
            $n++;
        }

        $vtas_doc_header_serv->contabilizar_movimiento_credito($documento->toArray(), $total_documento, $detalle_operacion);/**/
    }

    public function actualizar_valor_total_vtas_encabezados_doc()
    {
        $documentos = VtasDocEncabezado::all();

        $i = 1;
        foreach ($documentos as $un_documento)
        {
            $valor_total = VtasDocRegistro::where('vtas_doc_encabezado_id', $un_documento->id)->sum('precio_total');
            $un_documento->valor_total = $valor_total;
            $un_documento->save();
            echo $i . '  ';
            $i++;
        }

        echo '<br>Se actualizaron ' . ($i - 1) . ' documentos.';
    }

    //Conecta los procesos de cotizacion, pedidos, remisiones y facturas
    public function conexion_procesos(Request $request)
    {
        /*
            Desde cotizacion 
                1: Pedido
                3: Solo Remisión

            Desde Pedido
                remision_desde_pedido
                remision_y_factura_desde_pedido
        */

        $response = '';
        $encabezado = VtasDocEncabezado::find($request->modelo); //el documento desde donde se inicia el proceso

        $source = $request->source; //de donde viene la transaccion
        $url = $request->url; //URL origen de la transaccion
        switch ($request->generar)
        {
            case '1':
                $response = $this->soloPedido($encabezado, $request);
                break;
        }

        return redirect( $url )->with('flash_message', $response);

    }

    // Crea solo pedido
    public function soloPedido($cotizacion, $request)
    {
        $vtas_doc_header_serv = new DocumentHeaderService();

        $core_tipo_transaccion_id = 42;
        $core_tipo_doc_app_id = 41;
        $modelo_id = 175;

        $pedido = $vtas_doc_header_serv->clonar_encabezado( $cotizacion, $request->fecha, $core_tipo_transaccion_id, $core_tipo_doc_app_id, $cotizacion->descripcion, $modelo_id );

        $pedido->fecha_entrega = $request->fecha_entrega;
        $pedido->fecha_vencimiento = $request->fecha_entrega;
        $pedido->estado = 'Pendiente';
        $pedido->ventas_doc_relacionado_id = $cotizacion->id;
        $pedido->save();
        
        
        $vtas_doc_header_serv->clonar_lineas_registros( $cotizacion, $pedido->id );

        $cotizacion->estado = 'Cumplido';
        $cotizacion->save();

        return ' Pedido almacenado con exito';
    }
    
    /*
        Este metodo se llama desde la vista show de pedidos via POST
    */
    public function crear_remision_y_factura_desde_doc_venta( Request $request )
    {
        $pedido = VtasDocEncabezado::find( (int)$request->doc_encabezado_id );
        
        $vtas_doc_header_serv = new DocumentHeaderService();

        $hay_existencias_negativas = $vtas_doc_header_serv->determinar_posibles_existencias_negativas( $pedido );

        if ( $hay_existencias_negativas )
        {
            return redirect( 'vtas_pedidos/' . $pedido->id . '?id=13&id_modelo=175&id_transaccion=42' )->with( 'mensaje_error', 'No hay cantidades suficientes para facturar.' );
        }

        // Este metodo crear_remision_desde_doc_venta() debe estar en una clase Model
        $doc_remision = $this->crear_remision_desde_doc_venta( $pedido, $request->fecha );

        $pedido->forma_pago = $request->forma_pago;
        $pedido->remision_doc_encabezado_id = $doc_remision->id;

        $nueva_factura = $this->crear_factura_desde_doc_venta( $pedido, $request->fecha, $request->tipo_factura[0], $request->lineas_registros_medios_recaudo );

        $nueva_factura->remision_doc_encabezado_id = $doc_remision->id;
        $nueva_factura->ventas_doc_relacionado_id = $pedido->id;
        $nueva_factura->save();

        if( isset($request->abono) && $request->forma_pago == 'credito' )
        {
            // Create Account Receivable Payment (Recaudo de CxC)
            $abono = (float)$request->abono;
            if ($abono > $nueva_factura->valor_total) {
                $abono = $nueva_factura->valor_total;
            }
            if ( $abono != 0 )
            {
                $obj_trea_serv = new TreasuryServices();
                $obj_trea_serv->create_account_receivable_payment_from_invoice($nueva_factura, $abono,  $request['lineas_registros_medios_recaudo']);
            }            
        }

        $doc_remision->estado = 'Facturada';
        $doc_remision->save();

        $pedido->estado = 'Cumplido';
        $pedido->save();

        return redirect( 'vtas_pedidos/' . $pedido->id . '?id=13&id_modelo=175&id_transaccion=42' )->with( 'flash_message', 'Remisión y Factura almacenadas correctamente.' );
    }

    // Crea SOLO Factura (sin Remision) con cantidades completas
    public function crear_factura_desde_pasarela_de_pago(Request $request)
    {
        $data = $request->data['transaction'];
        
        if($data['status'] == 'APPROVED'){
            $pedido = VtasDocEncabezado::find( (int)$data['reference'] );        

            // este metodo crear_remision_desde_doc_venta() debe estar en una clase Model
            $doc_remision = $this->crear_remision_desde_doc_venta( $pedido, date('Y-m-d') );
            
            $pedido->remision_doc_encabezado_id = $doc_remision->id;

            $nueva_factura = $this->crear_factura_desde_doc_venta( $pedido, $doc_remision->id, date('Y-m-d'), config('web.tipo_factura_default') );
            $nueva_factura->remision_doc_encabezado_id = $doc_remision->id;
            $nueva_factura->ventas_doc_relacionado_id = $pedido->id;
            $nueva_factura->save();

            $doc_remision->estado = 'Facturada';
            $doc_remision->save();

            $pedido->estado = 'Cumplido';
            $pedido->save();

            return response()->json([
                'status'=> '200',
                'msg'=>'Transacción completada con exito'
            ]);
        }else{
            return response()->json([
                'status'=> '400',
                'msg'=>'Transacción fallida'
            ]);
        }
        
    }

    public function crear_factura_desde_doc_venta( $encabezado_doc_venta, $fecha, $tipo_factura, $lineas_registros_medios_recaudo = '{}' )
    {
        $modelo_id = 139; // FV estandar
        $tipo_transaccion_id = (int)config('ventas.factura_ventas_tipo_transaccion_id');
        $tipo_doc_app_id = (int)config('ventas.factura_ventas_tipo_doc_app_id');
        $estado = 'Activo';

        if ( $tipo_factura == 'electronica' )
        {            
            $modelo_id = 244; // FV Electronica
            $tipo_transaccion_id = (int)config('facturacion_electronica.transaction_type_id_default');
            $tipo_doc_app_id = (int)config('facturacion_electronica.document_type_id_default');
            $estado = 'Contabilizado - Sin enviar';
        }

        $vtas_doc_header_serv = new DocumentHeaderService();

        $descripcion = 'Generada desde ' . $encabezado_doc_venta->tipo_transaccion->descripcion . ' ' . $encabezado_doc_venta->tipo_documento_app->prefijo . ' ' . $encabezado_doc_venta->consecutivo;

        $nueva_factura = $vtas_doc_header_serv->clonar_encabezado( $encabezado_doc_venta, $fecha, $tipo_transaccion_id, $tipo_doc_app_id, $descripcion, $modelo_id );
        
        if ( $nueva_factura->forma_pago == 'credito' )
        {
            $nueva_factura->fecha_vencimiento = $this->sumar_dias_calendario_a_fecha( $fecha, $nueva_factura->cliente->condicion_pago->dias_plazo );
        }

        $nueva_factura->estado = $estado;
        $nueva_factura->save();        
        
        $vtas_doc_header_serv->clonar_lineas_registros( $encabezado_doc_venta, $nueva_factura->id );

        $vtas_doc_header_serv->crear_movimiento_ventas($nueva_factura);

        // Contabilizar
        $vtas_doc_header_serv->contabilizar_movimiento_debito($nueva_factura);
        $vtas_doc_header_serv->contabilizar_movimiento_credito($nueva_factura);

        $datos = $nueva_factura->toArray();
        
        $datos['registros_medio_pago'] = (new RegistrosMediosPago())->get_datos_ids( $lineas_registros_medios_recaudo, $nueva_factura->lineas_registros->toArray(), $nueva_factura->valor_total, 'ventas' );

        $vtas_doc_header_serv->crear_registro_pago( $nueva_factura->forma_pago, $datos, $nueva_factura->valor_total );

        //$vtas_doc_header_serv->crear_registro_pago($nueva_factura);

        return $nueva_factura;
    }

    /*
        Este metodo crea una remision con las cantidades completas de un documento de venta.
    */
    public function crear_remision_desde_doc_venta( $encabezado_doc_venta, $fecha )
    {
        $datos_remision = $encabezado_doc_venta->toArray();
        $datos_remision['fecha'] = $fecha;
        $datos_remision['inv_bodega_id'] = $encabezado_doc_venta->cliente->inv_bodega_id;

        $datos_remision['descripcion'] = $encabezado_doc_venta->descripcion;

        $datos_remision['vtas_doc_encabezado_origen_id'] = $encabezado_doc_venta->id;
        //$lineas_registros = VtasDocRegistro::where( 'vtas_doc_encabezado_id', $encabezado_doc_venta->id )->get();
        $lineas_registros = $encabezado_doc_venta->lineas_registros;

        $doc_remision = InventarioController::crear_encabezado_remision_ventas($datos_remision, 'Pendiente');
        
        InventarioController::crear_registros_remision_ventas( $doc_remision, $lineas_registros);

        InventarioController::contabilizar_documento_inventario( $doc_remision->id, '' );

        $this->actualizar_cantidades_pendientes( $lineas_registros );

        return $doc_remision;
    }

    // Se llama a la vista inventarios.create
    public function form_crear_remision_desde_doc_venta( Request $request )
    {
        $encabezado_doc_venta = VtasDocEncabezado::find( $request->doc_encabezado_id ); //el documento desde donde se inicia el proceso

        $id_transaccion = 24; // Remisiones de ventas

        $modelo = Modelo::find( 164 ); // Remision
        $transaccion = TipoTransaccion::find( $id_transaccion );

        $tipo_tranferencia = 2;

        $lista_campos = ModeloController::get_campos_modelo($modelo, '', 'create');
        $cantidad_campos = count($lista_campos);


        $lista_campos = ModeloController::personalizar_campos($id_transaccion, $transaccion, $lista_campos, $cantidad_campos, 'create', $tipo_tranferencia);

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo( $modelo, '' );

        $prod = new InvProducto();
        $productos = $prod->get_productos('r');
        $servicios = $prod->get_productos('servicio');

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);
        $app = Aplicacion::find( 13 );

        $miga_pan = [
                        ['url' => $app->app . '?id=13', 'etiqueta' => $app->descripcion],
                        ['url' => 'web?id=13' . '&id_modelo=' . $modelo->id, 'etiqueta' => $modelo->descripcion],
                        ['url' => 'NO', 'etiqueta' => 'Crear: ' . $transaccion->descripcion . ' desde ' . $encabezado_doc_venta->tipo_transaccion->descripcion ]
                    ];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = ''; //new TablaIngresoLineaRegistros( InvTransaccion::get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $motivos ) );

        $cantidad_filas = count( $encabezado_doc_venta->lineas_registros->toArray() );

        $lineas_registros = InventarioController::crear_lineas_registros_desde_doc_ventas( $encabezado_doc_venta );
        
        $hay_existencias_negativas = $this->determinar_existencias_negativas( $lineas_registros );
        
        $filas_tabla = View::make( 'inventarios.incluir.tabla_lineas_registros_para_almacenar', [ 'lineas_registros'=> $lineas_registros ] )->render();

        foreach ($lista_campos as $key => $value)
        {
            if ($value['name'] == 'core_tipo_transaccion_id')
            {
                $lista_campos[$key]['value'] = $id_transaccion;
            }

            if ($value['name'] == 'inv_bodega_id')
            {
                $lista_campos[$key]['value'] = $encabezado_doc_venta->cliente->inv_bodega_id;
            }

            if ($value['name'] == 'fecha')
            {
                $lista_campos[$key]['value'] = $request->fecha;
            }

            if ($value['name'] == 'core_tercero_id')
            {
                $lista_campos[$key]['value'] = $encabezado_doc_venta->core_tercero_id;
            }

            if ($value['name'] == 'descripcion')
            {
                $lista_campos[$key]['value'] = $encabezado_doc_venta->descripcion;
            }
        }
        
        $form_create = [
                        'url' => 'inv_store_remision_desde_pedido',
                        'campos' => $lista_campos,
                        'modo' => 'create'
                    ];

        $registro_id = 0;

        return view( 'inventarios.create', compact('form_create', 'id_transaccion', 'productos', 'servicios', 'motivos', 'miga_pan', 'tabla','filas_tabla','cantidad_filas', 'registro_id', 'hay_existencias_negativas'));
    }

    public function determinar_existencias_negativas( $lineas_registros )
    {
        foreach( $lineas_registros AS $linea )
        {
            if ( $linea->item->tipo == 'servicio' )
            {
                continue;
            }
            
            if ( ($linea->existencia_actual - abs($linea->cantidad) ) < 0 )
            {
                return 1;
            }
        }

        return 0;
    }

    public function actualizar_cantidades_pendientes( $lineas_registros )
    {
        foreach( $lineas_registros AS $linea )
        {
            $linea->cantidad_pendiente = $linea->cantidad_pendiente - $linea->cantidad;
            $linea->save();
        }
    }

    //valida si un valor se encuentra en el arreglo
    public function valueInArray($array, $value)
    {
        $esta = false;
        foreach ($array as $a) {
            if ($a == $value) {
                $esta = true;
            }
        }
        return $esta;
    }

    public function sumar_dias_calendario_a_fecha( string $fecha, int $cantidad_dias )
    {
        $fecha_aux = Carbon::createFromFormat('Y-m-d', $fecha );

        return $fecha_aux->addDays( $cantidad_dias )->format('Y-m-d');
    }

    public function documents_massive_canceling( $ids_list )
    {
        $ids = explode(',',$ids_list);

        $doc_header_serv = new DocumentHeaderService();

        $results = [];
        foreach ($ids as $key => $document_id) {
            $results[] = $doc_header_serv->cancel_document_by_id( $document_id, true );
        }

        return response()->json($results,200);
    }

    public function reconstruir_movimiento_documento($documento_id)
    {
        $this->reconstruir_movimiento_un_documento($documento_id);
        return redirect('ventas/' . $documento_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'Movimiento de ventas actualizado.');
    }

    public function reconstruir_movimiento_documento_por_lote($documento_inicial_id, $documento_final_id)
    {
        for ( $id = $documento_inicial_id; $id <= $documento_final_id; $id++) { 
            $this->reconstruir_movimiento_un_documento($id);
        }

        return redirect( 'ventas/' . $documento_inicial_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'Movimiento de ventas actualizado.');
    }

    public function reconstruir_movimiento_un_documento($documento_id)
    {
        $documento = VtasDocEncabezado::find($documento_id);

        if ( $documento == null ) {
            return false;
        }

        /**
         * 23: Facturas de Ventas Estándar
         * 38: Nota crédito
         * 52: Factura de Ventas Electrónica
         * 53: Nota crédito Electrónica
         */
        if ( !in_array( $documento->core_tipo_transaccion_id, [23, 38, 52, 53]) ) { 
            return false;
        }

        // Eliminar movimientos actuales
        VtasMovimiento::where([
                ['core_tipo_transaccion_id','=', $documento->core_tipo_transaccion_id],
                ['core_tipo_doc_app_id','=', $documento->core_tipo_doc_app_id],
                ['consecutivo','=', $documento->consecutivo]
            ])
            ->delete();

        if ( $documento->estado == 'Anulado' ) {
            return false;
        }

        // Obtener líneas de registros del documento
        $registros_documento = VtasDocRegistro::where('vtas_doc_encabezado_id', $documento->id)->get();
        
        $datos = $documento->toarray();
        $total_documento = 0;
        $n = 1;

        foreach ($registros_documento as $linea)
        {
            $nuevos_datos = $datos + $linea->toArray();

            VtasMovimiento::create( $nuevos_datos );

            $total_documento += $linea->precio_total;
            $n++;
        }

        $documento->valor_total = $total_documento;
        $documento->save();
    }

}
