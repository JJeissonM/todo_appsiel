<?php

namespace App\Http\Controllers\Inventarios;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

use Khill\Lavacharts\Laravel\LavachartsFacade as Lava;

use Illuminate\Support\Facades\Input;

use Illuminate\Support\Facades\Schema;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Contabilidad\ContabilidadController;


// Objetos
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Sistema\TipoTransaccion;

use App\Core\EncabezadoDocumentoTransaccion;
use App\Core\TipoDocApp;
use App\Core\Empresa;

use App\Inventarios\InvBodega;
use App\Inventarios\InvProducto;
use App\Inventarios\InvGrupo;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvCostoPromProducto;


use App\Compras\ComprasDocEncabezado;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\VentasPos\DocRegistro;

use App\Contabilidad\ContabMovimiento;
use App\Inventarios\RecetaCocina;
use App\Inventarios\Services\AverageCost;
use App\Inventarios\Services\InvDocumentsLinesService;
use App\Inventarios\Services\RecipeServices;
use App\Nomina\OrdenDeTrabajo;
use App\Sistema\Aplicacion;
use App\Ventas\ListaPrecioDetalle;
use App\VentasPos\FacturaPos;

class InventarioController extends TransaccionController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->set_variables_globales();

        $select_crear = $this->get_boton_select_crear($this->app);

        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Inventarios']
        ];

        $movimientos = [];

        // Existencias por bodegas
        $bodegas = InvBodega::take(10)->get();
        $i = 0;
        $cantidad_graficas = 0;
        $titulos = [];
        foreach ($bodegas as $una_bodega)
        {
            unset($movimientos);
            //$movimientos['bodega'][$i] = $una_bodega->descripcion;
            $movimientos['registros'][$i] = InvMovimiento::where('inv_movimientos.inv_bodega_id', '=', $una_bodega->id)
                ->where('inv_productos.tipo', '=', 'producto')
                ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_movimientos.inv_producto_id')
                ->select('inv_productos.descripcion as Producto', DB::raw('sum(inv_movimientos.cantidad) as Cantidad'))
                ->groupBy('inv_movimientos.inv_producto_id')
                ->get()
                ->toArray();

            if (!empty($movimientos['registros'][$i])) {

                $dibujar_grafica = false;

                // Creación de gráfico de Torta
                $stocksTable = Lava::DataTable();

                $stocksTable->addStringColumn('Producto')
                    ->addNumberColumn('Cantidad');

                foreach ($movimientos['registros'][$i] as $registro) {
                    $stocksTable->addRow([$registro['Producto'], round($registro['Cantidad'], 2)]);
                    // Se valida si los productos tienen cantidad mayor que cero
                    // Si al menos un producto tiene existencia, se dibuja la grafica
                    if (round($registro['Cantidad'], 2) > 0) {
                        $dibujar_grafica = true;
                    }
                }

                if ($dibujar_grafica) {
                    $grafica = 'MyStocks_' . $cantidad_graficas;
                    Lava::BarChart($grafica, $stocksTable, [
                        'is3D'                  => True,
                        'orientation' => 'horizontal',
                        'vAxis' => ['gridlines' => ['count' => 30]],
                        'height' => 600
                    ]);

                    $titulos[$cantidad_graficas]['bodega_id'] = $una_bodega->id;
                    $titulos[$cantidad_graficas]['bodega_nombre'] = $una_bodega->descripcion;
                    $cantidad_graficas++;
                }
            }
            $i++;
        }

        return view('inventarios.index', compact('miga_pan', 'select_crear', 'cantidad_graficas', 'titulos', 'movimientos'));
    }

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de inventarios
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->set_variables_globales();

        $id_transaccion = $this->transaccion->id;

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');
        $cantidad_campos = count($lista_campos);


        $lista_campos = ModeloController::personalizar_campos($id_transaccion, $this->transaccion, $lista_campos, $cantidad_campos, 'create' );

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo( $this->modelo, '' );
        
        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos,
                        'modo' => 'create'
                    ];

        $productos = InventarioController::get_productos('r');
        $servicios = InventarioController::get_productos('servicio');

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Crear: ' . $this->transaccion->descripcion);

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = ''; //new TablaIngresoLineaRegistros( InvTransaccion::get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $motivos ) );

        $cantidad_filas = 0;
        
        $descripcion_tercero = '';

        return view('inventarios.create', compact('form_create', 'id_transaccion', 'productos', 'servicios', 'motivos', 'miga_pan', 'tabla', 'cantidad_filas', 'descripcion_tercero'));
    }

    public function store( Request $request )
    {
        $lineas_registros = self::preparar_array_lineas_registros( $request->movimiento, $request->modo_ajuste );

        if ( (int)config('inventarios.generare_ensamble_automatico_en_salidas_mercancias')  ) {
            self::hacer_preparaciones_recetas($request->core_tipo_transaccion_id, $request->fecha, $request->inv_bodega_id, $lineas_registros);
        }

        $doc_encabezado_id = self::crear_documento($request, $lineas_registros, $request->url_id_modelo);

        if ( isset( $request->ruta_redirect ) )
        {
            // Por ahora esto solo se usa para Salidas de Invetario creadas desde Ordenes de Trabajo de Nomina
            $orden_trabajo = OrdenDeTrabajo::find( (int)$request->registro_id );
            if ( !is_null( $orden_trabajo ) )
            {
                $orden_trabajo->inv_doc_encabezado_id = $doc_encabezado_id;
                $orden_trabajo->save();
            }

            return redirect( $request->ruta_redirect . $request->registro_id . '?id=' . $request->url_id . '&id_modelo=' . $request->modelo_id_ruta . '&id_transaccion=' . $request->url_id_transaccion )->with( 'flash_message', 'Documento de inventario generado correctamente.' );
        }else{
            return redirect( 'inventarios/' . $doc_encabezado_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion);
        }
    }

    public function store_remision_desde_pedido( Request $request )
    {
        $lineas_registros = self::preparar_array_lineas_registros( $request->movimiento, $request->modo_ajuste );

        $doc_encabezado_remision_id = self::crear_documento($request, $lineas_registros, $request->url_id_modelo);

        $encabezado_pedido = VtasDocEncabezado::find( (int)$request->doc_ventas_id );
        if ( !is_null( $encabezado_pedido ) )
        {
            $encabezado_remision = InvDocEncabezado::find( $doc_encabezado_remision_id );
            $encabezado_remision->vtas_doc_encabezado_origen_id = $encabezado_pedido->id;
            $encabezado_remision->save();

            self::actualizar_cantidades_pendientes( $encabezado_pedido, $encabezado_remision, 'restar' );

            $encabezado_pedido->estado = 'Remisionado';

            if ( $encabezado_pedido->lineas_registros->sum('cantidad_pendiente') == 0 )
            {
                $encabezado_pedido->estado = 'Cumplido';
            }
            $encabezado_pedido->save();
        }

        return redirect( 'inventarios/' . $doc_encabezado_remision_id . '?id=13&id_modelo=164&id_transaccion=24' )->with( 'flash_message', 'Remisión almacenada correctamente.' );
    }

    public static function actualizar_cantidades_pendientes( $encabezado_pedido, $encabezado_remision, $operacion )
    {
        $lineas_registros_remision = $encabezado_remision->lineas_registros;
        foreach( $lineas_registros_remision AS $linea_remision )
        {
            $linea_pedido = VtasDocRegistro::find( $linea_remision->linea_registro_doc_origen_id );
            
            if(is_null($linea_pedido) )
            {
                continue;
            }
            
            if ( $operacion == 'restar' )
            {
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad_pendiente - abs($linea_remision->cantidad);
            }else{
                // sumar: al anular la remision
                $linea_pedido->cantidad_pendiente = $linea_pedido->cantidad_pendiente + abs($linea_remision->cantidad);
            }
                
            $linea_pedido->save();
        }
    }

    /*
        Esta se reemplaza en el servicio Inventarios\Sevices\DocumentsLinesService
    
    */
    public static function preparar_array_lineas_registros( $request_registros, $modo_ajuste )
    {
        $lineas_registros = json_decode( $request_registros );

        // Quitar primera línea
        array_shift( $lineas_registros );

        // Quitar las dos últimas líneas
        array_pop($lineas_registros);
        array_pop($lineas_registros);

        $cantidad = count($lineas_registros);
        for ($i = 0; $i < $cantidad; $i++)
        {
            $lineas_registros[$i]->inv_motivo_id = explode( "-", $lineas_registros[$i]->motivo )[0];
            $lineas_registros[$i]->costo_unitario = (float) substr($lineas_registros[$i]->costo_unitario, 1);
            $lineas_registros[$i]->cantidad = (float) $lineas_registros[$i]->cantidad;
            $lineas_registros[$i]->costo_total = (float) substr($lineas_registros[$i]->costo_total, 1);

            if (!is_null($modo_ajuste))
            {
                if ($modo_ajuste == 'solo_cantidad')
                {
                    $lineas_registros[$i]->costo_unitario = 0;
                    $lineas_registros[$i]->costo_total = 0;
                }
            }
        }

        return $lineas_registros;
    }

    public static function hacer_preparaciones_recetas( $core_tipo_transaccion_id, $fecha, $inv_bodega_id, $lineas_registros)
    {
        $obj_inv_doc_serv = new RecipeServices();

        $descripcion = 'Doc. Creado automáticamente desde la creación de una Salida de Almacén.';

        if ( $core_tipo_transaccion_id == 2 ) { // 2: Transferencia
            $descripcion = 'Doc. Creado automáticamente desde la creación de una Transferencia.';
        }

        return $obj_inv_doc_serv->create_document_making( $lineas_registros, $inv_bodega_id, $fecha,  $descripcion);        
    }

    /*

        Deprecated. Usar en su lugar App\Inventarios\Services\InvDocumentsHeadersService@crear_documento
        Este método se llamada desde VentaController, CompraController y varios Controllers más
        Crea un documento completo: encabezados, registros, movimiento y contabilización
        Devuelve en ID del documento creado
    */
    public static function crear_documento(Request $request, array $lineas_registros, $modelo_id)
    {
        $request['creado_por'] = Auth::user()->email;
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $modelo_id );
        
        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );
        InventarioController::crear_registros_documento($request, $doc_encabezado, $lineas_registros);

        return $doc_encabezado->id;
    }

    /*
        No Devuelve nada
    */
    public static function crear_registros_documento(Request $request, $doc_encabezado, array $lineas_registros)
    {
        $tipo_transferencia = 2;

        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        // Ahora mismo el campo inv_bodega_id se envía en el request, pero se debe tomar de cada línea de registro
        $datos = $request->all();
        
        $average_cost_serv = new AverageCost();

        $cantidad_registros = count($lineas_registros);
        for ($i = 0; $i < $cantidad_registros; $i++)
        {
            $item = InvProducto::find($lineas_registros[$i]->inv_producto_id);

            if ($item == null) {
                continue;
            }
            
            $cantidad = (float)$lineas_registros[$i]->cantidad;
            if( $cantidad == 0 )
            {
                continue;
            }
            
            $costo_unitario = (float) $lineas_registros[$i]->costo_unitario;
            
            $costo_total = (float) $lineas_registros[$i]->costo_total;

            $motivo = InvMotivo::find($lineas_registros[$i]->inv_motivo_id);

            // Cuando el motivo de la transacción es de salida, 
            // las cantidades y costos totales restan del movimiento ( negativo )
            if ( $motivo->movimiento == 'salida')
            {
                $cantidad = (float) $cantidad * -1;
                $costo_total = (float) $costo_total * -1;
            }

            $linea_registro_doc_origen_id = 0;
            if ( isset( $lineas_registros[$i]->linea_registro_doc_origen_id ) )
            {
                $linea_registro_doc_origen_id = $lineas_registros[$i]->linea_registro_doc_origen_id;
            }

            $linea_datos = ['inv_motivo_id' => $lineas_registros[$i]->inv_motivo_id] +
                            ['linea_registro_doc_origen_id' => $linea_registro_doc_origen_id ] +
                            ['inv_producto_id' => $lineas_registros[$i]->inv_producto_id] +
                            ['costo_unitario' => $costo_unitario] +
                            ['cantidad' => $cantidad] +
                            ['costo_total' => $costo_total];

            InvDocRegistro::create(
                $datos +
                    ['inv_doc_encabezado_id' => $doc_encabezado->id] +
                    $linea_datos
            );

            $tipo_producto = $item->tipo;

            // Solo los productos generan movimiento de inventario y contabilización.
            if ($tipo_producto == 'servicio' )
            {                
                continue;
            }

            $datos['consecutivo'] = $doc_encabezado->consecutivo;
            InvMovimiento::create(
                                    $datos +
                                    ['inv_doc_encabezado_id' => $doc_encabezado->id] +
                                    $linea_datos
                                );

            // Contabilizar
            $detalle_operacion = '';
            
            // 1. Determinar las cuentas
            // 1.1. Dada por el Grupo de Inventarios
            $cta_inventarios_id = InvProducto::get_cuenta_inventarios($lineas_registros[$i]->inv_producto_id);
            
            // 1.2. Dada por el Motivo de Inventarios
            $cta_contrapartida_id = $motivo->cta_contrapartida_id;

            // 2. Determinar la anturaleza del registro
            // 2.1. Si el movimiento es de ENTRADA de inventarios, se DEBITA la cta. de inventarios vs la cta. contrapartida
            if ($motivo->movimiento == 'entrada') {
                $valor_debito = abs($costo_total);
                $valor_credito = 0;
            }

            // 2.2. Si el movimiento es de SALIDA de inventarios, se ACREDITA la cta. de inventarios vs la cta. contrapartida
            if ($motivo->movimiento == 'salida')
            {
                $valor_debito = 0;
                $valor_credito = abs($costo_total);
            }

            // 3. Contabilizar DB
            InventarioController::contabilizar_registro_inv($datos + $linea_datos, $cta_inventarios_id, $detalle_operacion, $valor_debito, $valor_credito);
            // 4. Contabilizar CR
            InventarioController::contabilizar_registro_inv($datos + $linea_datos, $cta_contrapartida_id, $detalle_operacion, $valor_credito, $valor_debito);

            // Cuando es una transferencia, se deben guardar los registros de la bodega destino
            if ($request->core_tipo_transaccion_id == $tipo_transferencia) 
            {
                self::guardar_registros_bodega_destino_transferencia( $datos, $doc_encabezado->id, $request->bodega_destino_id, $lineas_registros[$i]->inv_producto_id, $cantidad, $costo_unitario, $costo_total, $cta_inventarios_id, $request->fecha );
            }

            // Si es una entrada, se calcula el costo promedio por bodega y producto
            if ($motivo->movimiento == 'entrada')
            {
                // Se CALCULA el costo promedio del movimiento, si no existe será el enviado en el request
                $costo_prom = $average_cost_serv->calculate_average_cost($request->inv_bodega_id, $lineas_registros[$i]->inv_producto_id, $costo_unitario, $request->fecha, $cantidad);
                
                self::actualizar_costo_promedio($request->inv_bodega_id, $lineas_registros[$i]->inv_producto_id, $costo_prom, $request->core_tipo_transaccion_id, $average_cost_serv);
            }
        }
    }

    public static function actualizar_costo_promedio($inv_bodega_id, $inv_producto_id, $costo_prom, $core_tipo_transaccion_id, $average_cost_serv)
    {
        $tipo_transferencia = 2;
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1  )
        {
            // Actualizo/Almaceno el costo promedio
            $average_cost_serv->set_costo_promedio( $inv_bodega_id, $inv_producto_id, $costo_prom);
        }else{

            // Cuando no maneja costo promedio por bodegas (un solo costo para todo)

            // Solo se calcula costo promedio, si la entrada NO es por transferencia
            if ($core_tipo_transaccion_id != $tipo_transferencia) 
            {
                // Actualizo/Almaceno el costo promedio
                $average_cost_serv->set_costo_promedio( $inv_bodega_id, $inv_producto_id, $costo_prom);
            }
        }
    }

    public static function guardar_registros_bodega_destino_transferencia( $datos, $inv_doc_encabezado_id, $bodega_destino_id, $inv_producto_id, $cantidad, $costo_unitario, $costo_total, $cta_inventarios_id, $fecha )
    {
        $motivo_entrada_transferencia = 9;
        $cantidad = (float) $cantidad * -1;
        $costo_total = (float) $costo_total * -1;

        // Se cambia el valor de la bodega principal del request
        $datos['inv_bodega_id'] = $bodega_destino_id;

        $linea_datos = ['inv_doc_encabezado_id' => $inv_doc_encabezado_id ] +
            ['inv_motivo_id' => $motivo_entrada_transferencia] +
            ['inv_producto_id' => $inv_producto_id] +
            ['costo_unitario' => $costo_unitario] +
            ['cantidad' => $cantidad] +
            ['costo_total' => $costo_total];

        InvDocRegistro::create(
            $datos +
                $linea_datos
        );

        InvMovimiento::create(
            $datos +
                $linea_datos
        );

        InventarioController::contabilizar_registro_inv($datos + $linea_datos, $cta_inventarios_id, '', abs($costo_total), 0);

        // Para transferencias, la cuenta contrapartida es la misma de inventarios
        InventarioController::contabilizar_registro_inv( $datos + $linea_datos, $cta_inventarios_id, '', 0, abs($costo_total) );
        
        if ( (int)config('inventarios.maneja_costo_promedio_por_bodegas') == 1  )
        {
            $average_cost_serv = new AverageCost();
            // PARA LA BODEGA DESTINO
            // Se CALCULA el costo promedio del movimiento, si no existe será el enviado en el request
            $costo_prom = $average_cost_serv->calculate_average_cost($bodega_destino_id, $inv_producto_id, $costo_unitario, $fecha, $cantidad);

            // Actualizo/Almaceno el costo promedio
            $average_cost_serv->set_costo_promedio( $bodega_destino_id, $inv_producto_id, $costo_prom);
        }            
    }

    public static function crear_encabezado_remision_ventas( $datos, $estado = null )
    {
        // Llamar a los parámetros del archivo de configuración
        $parametros = config('ventas');

        $datos['core_tipo_transaccion_id'] = $parametros['rm_tipo_transaccion_id'];
        $datos['core_tipo_doc_app_id'] = $parametros['rm_tipo_doc_app_id'];
        $datos['estado'] = 'Facturada';
        if ( !is_null( $estado ) )
        {
            $datos['estado'] = $estado;
        }
        
        $datos['creado_por'] = 'paula@appsiel.com.co';
        if(Auth::user()){
            $datos['creado_por'] = Auth::user()->email;
        }        
        $datos['consecutivo'] = TipoDocApp::get_consecutivo_actual( $datos['core_empresa_id'], $datos['core_tipo_doc_app_id'] ) + 1;

        TipoDocApp::aumentar_consecutivo( $datos['core_empresa_id'], $datos['core_tipo_doc_app_id'] );

        $doc_encabezado = InvDocEncabezado::create( $datos );

        return $doc_encabezado;
    }

    /*
        Nota los costos son llamados del costo promedio
    */
    public static function crear_registros_remision_ventas( $doc_encabezado, $lineas_registros )
    {
        foreach( $lineas_registros AS $linea )
        {
            if ( $linea->cantidad == 0 )
            {
                continue;
            }

            $item = InvProducto::find( $linea->inv_producto_id );
            
            if ( is_null($item) )
            {
                continue;
            }

            $costo_unitario = InvCostoPromProducto::get_costo_promedio($doc_encabezado->inv_bodega_id, $linea->inv_producto_id );
            $cantidad = $linea->cantidad * -1; // Salida de inventarios
            $costo_total = $cantidad * $costo_unitario;

            $datos = $doc_encabezado->toArray();
            $datos['inv_doc_encabezado_id'] = $doc_encabezado->id;
            $datos['core_empresa_id'] = $doc_encabezado->core_empresa_id;
            $datos['inv_bodega_id'] = $doc_encabezado->inv_bodega_id;
            $datos['core_tercero_id'] = $doc_encabezado->core_tercero_id;

            $linea_datos = [ 'inv_motivo_id' => $linea->vtas_motivo_id ] + // Warning: $linea tiene un campo especifico
                            [ 'inv_producto_id' => $linea->inv_producto_id ] + 
                            [ 'costo_unitario' => $costo_unitario ] +
                            [ 'cantidad' => $cantidad ] +
                            [ 'costo_total' => $costo_total ];

            InvDocRegistro::create( $datos + $linea_datos );

            if ( $item->tipo == 'producto')
            {
                InvMovimiento::create( $datos + $linea_datos );
            }  
        }
    }

    public static function crear_lineas_registros_desde_doc_ventas( $doc_encabezado )
    {
        $lineas_registros = $doc_encabezado->lineas_registros;
        $cantidad_registros = count($lineas_registros);

        $lineas = [];
        foreach( $lineas_registros AS $linea )
        {
            if ( $linea->cantidad_pendiente == 0 )
            {
                continue;
            }

            $inv_bodega_id = $doc_encabezado->cliente->inv_bodega_id;
            if ( is_null($inv_bodega_id) )
            {
                $inv_bodega_id = 1;
            }

            $costo_unitario = InvCostoPromProducto::get_costo_promedio( $inv_bodega_id, $linea->inv_producto_id );
            $cantidad = $linea->cantidad_pendiente * -1; // Salida de inventarios
            $costo_total = $cantidad * $costo_unitario;

            $existencia_actual = InvMovimiento::get_existencia_actual( $linea->inv_producto_id, $inv_bodega_id, $doc_encabezado->fecha );

            $lineas[] = (object)[
                                    'linea_registro_doc_origen_id' => $linea->id,
                                    'item' => $linea->item,
                                    'motivo' => $linea->motivo,
                                    'inv_producto_id' => $linea->inv_producto_id,
                                    'costo_unitario' => $costo_unitario,
                                    'cantidad' => $cantidad,
                                    'costo_total' => $costo_total,
                                    'existencia_actual' => $existencia_actual
                                ];
        }

        return $lineas;
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

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id);

        $doc_encabezado = InvDocEncabezado::get_registro_impresion($id);

        $doc_registros = InvDocRegistro::get_registros_impresion($doc_encabezado->id);

        $empresa = Empresa::find($doc_encabezado->core_empresa_id);

        $registros_contabilidad = TransaccionController::get_registros_contabilidad($doc_encabezado);

        // Verificar si pertenece a una documento de compras
        $reg_fatura_compras = ComprasDocEncabezado::where('entrada_almacen_id', '=', $doc_encabezado->id)
            ->orWhere('entrada_almacen_id', 'LIKE', '%,' . $doc_encabezado->id)
            ->orWhere('entrada_almacen_id', 'LIKE', $doc_encabezado->id . ',%')
            ->orWhere('entrada_almacen_id', 'LIKE', '%,' . $doc_encabezado->id . ',%')
            ->get()
            ->first();
        $enlace1 = '';
        if (!is_null($reg_fatura_compras)) {
            $fatura_compra = ComprasDocEncabezado::get_registro_impresion($reg_fatura_compras->id);
            $enlace1 = '<br/>
                    <b>Orden de compras: </b> <a href="' . url('compras/' . $fatura_compra->id . '?id=9&id_modelo=147&id_transaccion=' . $reg_fatura_compras->core_tipo_transaccion_id) . '" target="_blank">' . $fatura_compra->documento_transaccion_prefijo_consecutivo . '</a>';
        }

        $enlace2 = '';
        // Verificar si pertenece a una documento de ventas
        $reg_factura_venta = VtasDocEncabezado::where('remision_doc_encabezado_id', $doc_encabezado->id)
            ->orWhere('remision_doc_encabezado_id', 'LIKE', '%,' . $doc_encabezado->id)
            ->orWhere('remision_doc_encabezado_id', 'LIKE', $doc_encabezado->id . ',%')
            ->orWhere('remision_doc_encabezado_id', 'LIKE', '%,' . $doc_encabezado->id . ',%')
            ->get()
            ->first();
        if (!is_null($reg_factura_venta)) {
            $fatura_venta = VtasDocEncabezado::get_registro_impresion($reg_factura_venta->id);
            $enlace2 = '<br/>
                    <b>Factura de ventas: </b> <a href="' . url('ventas/' . $fatura_venta->id . '?id=13&id_modelo=139&id_transaccion=' . $reg_factura_venta->core_tipo_transaccion_id) . '" target="_blank">' . $fatura_venta->documento_transaccion_prefijo_consecutivo . '</a>';
        }

        // Verificar si pertenece a una documento de ventas POS
        $reg_factura_venta = FacturaPos::where('remision_doc_encabezado_id', $doc_encabezado->id)
            ->orWhere('remision_doc_encabezado_id', 'LIKE', '%,' . $doc_encabezado->id)
            ->orWhere('remision_doc_encabezado_id', 'LIKE', $doc_encabezado->id . ',%')
            ->orWhere('remision_doc_encabezado_id', 'LIKE', '%,' . $doc_encabezado->id . ',%')
            ->get()
            ->first();
        if (!is_null($reg_factura_venta)) {
            $fatura_venta = FacturaPos::get_registro_impresion($reg_factura_venta->id);
            $enlace2 = '<br/>
                    <b>Factura POS: </b> <a href="' . url('pos_factura/' . $fatura_venta->id . '?id=20&id_modelo=230&id_transaccion=' . $reg_factura_venta->core_tipo_transaccion_id) . '" target="_blank">' . $fatura_venta->documento_transaccion_prefijo_consecutivo . '</a>';
        }

        $documento_vista = View::make('inventarios.incluir.documento_vista', compact('doc_encabezado', 'doc_registros', 'empresa', 'registros_contabilidad', 'enlace1', 'enlace2'))->render();
        $id_transaccion = $this->transaccion->id;

        $miga_pan = [
            ['url' => $this->app->app . '?id=' . Input::get('id'), 'etiqueta' => $this->app->descripcion],
            ['url' => 'web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'), 'etiqueta' => $this->modelo->descripcion],
            ['url' => 'NO', 'etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
        ];

        return view('inventarios.show', compact('id', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan', 'registros_contabilidad', 'doc_encabezado', 'empresa', 'enlace1', 'enlace2'));
    }


    // VISTA PARA MOSTRAR UN DOCUMENTO DE TRANSACCION
    public function imprimir($id)
    {
        $encabezado_doc = InvDocEncabezado::find($id);
        
        $tipo_transaccion = TipoTransaccion::find($encabezado_doc->core_tipo_transaccion_id);

        //$core_app = $tipo_transaccion->core_app;

        $tipo_doc_app = TipoDocApp::find($encabezado_doc->core_tipo_doc_app_id);

        $descripcion_transaccion = $tipo_transaccion->descripcion;
        
        $movimientos = $encabezado_doc->movimientos;

        $productos = [];

        $i = 0;
        foreach ($movimientos as $movimiento) {

            $producto = InvProducto::find($movimiento->inv_producto_id);
            $bodega = InvBodega::find($movimiento->inv_bodega_id);

            $producto->unidad_medida1 = $producto->get_unidad_medida1();
            $productos[$i] = $movimiento->toArray();
            $productos[$i]['producto'] = $producto;
            $productos[$i]['bodega'] = $bodega->descripcion;

            $i++;
        }

        // Se obtinen las descripciones de los datos del encabezado
        $sql_datos_encabezado_doc = InvDocEncabezado::get_registro($id);
        $datos_encabezado_doc =  $sql_datos_encabezado_doc[0];
        $elaboro = $encabezado_doc->creado_por;

        switch ( Input::get('formato_impresion_id') )
        {
            case '2':
                $view = $this->generar_documento_vista(Input::get('id_transaccion'), $id, 'inventarios.formatos.remision',$datos_encabezado_doc);
                break;

            case '3':
                $view = $this->generar_documento_vista(Input::get('id_transaccion'), $id, 'inventarios.formatos.remision2',$datos_encabezado_doc);
                break;          

            case '4':
                $view = $this->generar_documento_vista(Input::get('id_transaccion'), $id, 'inventarios.formatos.remision_pos',$datos_encabezado_doc);
                break;   

            case '5':
                $view = $this->generar_documento_vista(Input::get('id_transaccion'), $id, 'inventarios.formatos.remision_ceof',$datos_encabezado_doc);
                break;

            case '6':
                $view = $this->generar_documento_vista(Input::get('id_transaccion'), $id, 'inventarios.formatos.remision_cem',$datos_encabezado_doc);
                break;
            
            default:
                // No se especifica formato de impresión 
                $view = View::make('inventarios.pdf', compact('datos_encabezado_doc', 'descripcion_transaccion', 'productos', 'elaboro') )->render();
                break;
        }

        // Se prepara el PDF
        $orientacion = 'portrait';
        $tam_hoja = 'Letter';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);
        
        return $pdf->stream($descripcion_transaccion . '_' . $encabezado_doc->consecutivo . '.pdf');
        //return $view;
    }

    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista($id_transaccion, $id, $ruta_vista,$datos_encabezado_doc)
    {
        $transaccion = TipoTransaccion::find($id_transaccion);

        $doc_encabezado = app($transaccion->modelo_encabezados_documentos)->get_registro_impresion($id);

        $doc_registros = app($transaccion->modelo_registros_documentos)->get_registros_impresion($doc_encabezado->id);

        $empresa = Empresa::find($doc_encabezado->core_empresa_id);        

        return View::make($ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa','datos_encabezado_doc'))->render();
    }

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de inventarios
     *
     * @return \Illuminate\Http\Response
     */
    public function edit( $id )
    {
        $this->set_variables_globales();

        $id_transaccion = $this->transaccion->id;

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'edit');
        $cantidad_campos = count($lista_campos);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion, $this->transaccion, $lista_campos, $cantidad_campos, 'edit');

        $modelo_controller = new ModeloController;
        $acciones = $modelo_controller->acciones_basicas_modelo( $this->modelo, '' );

        $productos = InventarioController::get_productos('r');
        $servicios = InventarioController::get_productos('servicio');

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Crear: ' . $this->transaccion->descripcion);

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = '';

        $doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->find( $id );

        $cantidad_filas = count( $doc_encabezado->lineas_registros->toArray() );

        $filas_tabla = View::make( 'inventarios.incluir.tabla_lineas_registros_para_almacenar', ['lineas_registros'=> $doc_encabezado->lineas_registros ] )->render();

        foreach ($lista_campos as $key => $value)
        {
            if ($value['name'] == 'core_tipo_transaccion_id')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->core_tipo_transaccion_id;
            }

            if ($value['name'] == 'inv_bodega_id')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->inv_bodega_id;
            }

            if ($value['name'] == 'fecha')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->fecha;
            }

            if ($value['name'] == 'core_tercero_id')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->core_tercero_id;
            }

            if ($value['name'] == 'descripcion')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->descripcion;
            }

            if ($value['name'] == 'documento_soporte')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->documento_soporte;
            }

            if ($value['name'] == 'bodega_destino_id')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->bodega_destino_id;
            }
        }
        
        $url_action = str_replace('id_fila', $id, $acciones->update);

        $form_create = [
                            'url' => $url_action,
                            'campos' => $lista_campos,
                            'modo' => 'edit'
                        ];
        $registro_id = $id;

        $descripcion_tercero = $doc_encabezado->tercero->get_label_to_show();

        return view( 'inventarios.create', compact('form_create','id_transaccion','productos','servicios','motivos','miga_pan','tabla','filas_tabla','cantidad_filas', 'registro_id', 'descripcion_tercero'));
    }

    public function update(Request $request, $id)
    {
        // Actualizar datos del encabezado
        $doc_encabezado = InvDocEncabezado::find($id);

        $doc_encabezado->fecha = $request->fecha;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->documento_soporte = $request->documento_soporte;
        //$doc_encabezado->core_tercero_id = $request->core_tercero_id;
        $doc_encabezado->modificado_por = Auth::user()->email;
        $doc_encabezado->save();

        // Borrar líneas de registros anteriores
        InvDocRegistro::where('inv_doc_encabezado_id',$doc_encabezado->id)->delete();

        // Borrar movimiento contable
        ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
            ->where('consecutivo', $doc_encabezado->consecutivo)
            ->delete();

        // Eliminar movimiento de inventarios
        InvMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->where('consecutivo', $doc_encabezado->consecutivo)
            ->delete();

        // Crear nuevamente las líneas de registros

        $lineas_registros = self::preparar_array_lineas_registros( $request->movimiento, null );

        $request['creado_por'] = $doc_encabezado->creado_por;
        $request['core_tercero_id'] = $doc_encabezado->core_tercero_id;
        $request['modificado_por'] = Auth::user()->email;
        self::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        $ruta_redirect = 'inventarios/';
        if ( isset( $request->ruta_redirect ) )
        {
            $ruta_redirect = $request->ruta_redirect;
        }

        $registro_id = $id;
        if ( isset( $request->registro_id ) )
        {
            $registro_id = $request->registro_id;
        }

        return redirect( $ruta_redirect . $registro_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion )->with( 'flash_message', 'Documento actualizado correctamente.' );
    }


    // Parámetro enviados por GET
    public function consultar_productos()
    {
        $campo_busqueda = Input::get('campo_busqueda');

        $cantidad_a_mostrar = 15;

        $array_wheres = [
            ['estado', '=', 'Activo']
        ];

        if ( Input::get('proveedor_id') != null && (int)config('compras.mostrar_solo_productos_relacionados_con_el_proveedor') ) {
            $array_wheres = array_merge( $array_wheres, [['categoria_id', '=', Input::get('proveedor_id')]]);
        }

        switch ($campo_busqueda) {
            case 'codigo_barras':
                $operador = '=';
                $texto_busqueda = Input::get('texto_busqueda');
                $producto = InvProducto::where( $array_wheres )
                                    ->where($campo_busqueda, $operador, $texto_busqueda)
                                    ->select( 
                                            DB::raw('CONCAT( descripcion, " ", referencia ) AS nueva_cadena'),
                                            'id',
                                            'categoria_id',
                                            'referencia',
                                            'codigo_barras',
                                            'descripcion',
                                            'unidad_medida1',
                                            'unidad_medida2' )
                                    ->get()
                                    ->take($cantidad_a_mostrar);
                                    
                break;
            case 'descripcion':
                $operador = 'LIKE';
                $texto_busqueda = '%' . str_replace( " ", "%", Input::get('texto_busqueda') ) . '%';

                $producto = InvProducto::where( $array_wheres )
                                ->having('nueva_cadena', $operador, $texto_busqueda)
                                ->select( 
                                            DB::raw('CONCAT( descripcion, " ", referencia ) AS nueva_cadena'),
                                            'id',
                                            'categoria_id',
                                            'referencia',
                                            'descripcion',
                                            'codigo_barras',
                                            'unidad_medida1',
                                            'unidad_medida2' )
                                ->get()
                                ->take($cantidad_a_mostrar);
                break;
            case 'id':
                $operador = 'LIKE';
                $texto_busqueda = Input::get('texto_busqueda') . '%';

                $producto = InvProducto::where( $array_wheres )
                                    ->where($campo_busqueda, $operador, $texto_busqueda)
                                    ->select( 
                                            DB::raw('CONCAT( descripcion, " ", referencia ) AS nueva_cadena'),
                                            'id',
                                            'categoria_id',
                                            'codigo_barras',
                                            'referencia',
                                            'descripcion',
                                            'unidad_medida1',
                                            'unidad_medida2' )
                                    ->get()
                                    ->take($cantidad_a_mostrar);
                break;

            default:
                # code...
                break;
        }            

        $html = '<div class="list-group">';
        $es_el_primero = true;
        $ultimo_item = 0;
        $num_item = 1;
        $cantidad_datos = count( $producto->toArray() );
        foreach ($producto as $linea)
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

            $html .= '<a class="list-group-item list-group-item-productos ' . $clase . ' flecha_mover" data-descripcion="' . $linea->get_value_to_show(true) . '" data-producto_id="' . $linea->id . '" data-primer_item="'.$primer_item.
                                '" data-accion="na" '.
                                '" data-ultimo_item="'.$ultimo_item;

            $descripcion_item = $linea->get_value_to_show();

            $html .=            '" > ' . $descripcion_item . ' </a>';

            $num_item++;
        }
        
        // Linea crear nuevo registro
        $modelo_id = 22; // Items

        if (Aplicacion::find(8)->estado == 'Inactivo') { // 8 = Aplicacion Inventarios
            $modelo_id = 220; // Servicios
        }

        $href =  url( 'web/create?id=' . Input::get('url_id') . '&id_modelo=' . $modelo_id . '&id_transaccion' );
        $html .= '<a href="'. $href . '" target="_blank" class="list-group-item list-group-item-sugerencia list-group-item-warning" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nuevo registro </a>';

        $html .= '</div>';

        return $html;
    }
    
    // Parámetro enviados por GET
    public function consultar_productos_v2()
    {
        $texto_busqueda_codigo = (int)Input::get('texto_busqueda');

        if( $texto_busqueda_codigo == 0 )
        {
            $campo_busqueda = 'haystack';
            $texto_busqueda = '%' . str_replace( " ", "%", Input::get('texto_busqueda') ) . '%';
        }else{
            $campo_busqueda = 'id';
            $texto_busqueda = Input::get('texto_busqueda').'%';
        }

        $cantidad_a_mostrar = 15;

        $datos = InvProducto::where('estado', 'Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            //->where( $campo_busqueda, 'LIKE', $texto_busqueda)
                            ->having( $campo_busqueda, 'LIKE', $texto_busqueda)
                            ->select(
                                        'id',
                                        'descripcion',
                                        'categoria_id',
                                        DB::raw('CONCAT(referencia," ",descripcion) AS haystack'),
                                        'referencia',
                                        'unidad_medida1',
                                        'unidad_medida2')
                            ->get()
                            ->take($cantidad_a_mostrar);
                

        $html = '<div class="list-group">';
        $es_el_primero = true;
        $ultimo_item = 0;
        $num_item = 1;
        $cantidad_datos = count( $datos->toArray() ); // si datos es null?
        foreach ($datos as $linea) 
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

            $html .= '<a class="list-group-item list-group-item-sugerencia '.$clase.'" data-registro_id="'.$linea->id.
                                '" data-primer_item="'.$primer_item.
                                '" data-accion="na" '.
                                '" data-ultimo_item="'.$ultimo_item; // Esto debe ser igual en todas las busquedas

            $html .=            '" > ' . $linea->get_value_to_show() . ' </a>';

            $num_item++;
        }

        // Linea crear nuevo registro
        $modelo_id = 22; // Items
        $href =  url( 'web/create?id=8&id_modelo=' . $modelo_id . '&id_transaccion' );
        $html .= '<a href="'. $href . '" target="_blank" class="list-group-item list-group-item-sugerencia list-group-item-warning" data-modelo_id="'.$modelo_id.'" data-accion="crear_nuevo_registro" > + Crear nuevo registro </a>';

        $html .= '</div>';

        return $html;
    }

    // Parámetro enviados por GET
    public function consultar_existencia_producto()
    {
        $transaccion_id = Input::get('transaccion_id');
        $bodega_id = Input::get('bodega_id');

        $producto = InvProducto::find(Input::get('producto_id'));

        if (!is_null($producto)) {
            $producto = $producto->toArray(); // Se convierte en array para manipular facilmente sus campos 
        } else {
            $producto = [];
        }

        if (!empty($producto)) {
            // si no es una Entrada, se debe cambiar el costo unitario, por el costo promedio
            if ($transaccion_id != 1)
            {
                
                $costo_prom = InvCostoPromProducto::get_costo_promedio( $bodega_id, $producto['id']);

                $producto = array_merge( $producto, ['precio_compra' => $costo_prom] );
            }

            // Obtener existencia actual
            $existencia_actual = InvMovimiento::get_existencia_actual($producto['id'], $bodega_id, Input::get('fecha'));

            $producto = array_merge($producto, ['existencia_actual' => $existencia_actual], ['tipo' => $producto['tipo']]);
        }

        return $producto;
    }
    
    /**
     * Obtener el stock de un item en una bodega específica. Si la bodega es cero, se suman las existencias de todas las bodegas activas.
     * Este método es llamado por AJAX
     */
    public function get_item_stock( $item_id, $bodega_id, $fecha)
    {
        $existencia_actual = 0;

        if ( $bodega_id == 0 ) 
        {
            $bodegas = InvBodega::where('estado', 'Activo')
                                ->get();
            foreach ($bodegas as $bodega)
            {
                $existencia_actual += InvMovimiento::get_existencia_actual($item_id, $bodega->id, $fecha);
            }

            return $existencia_actual;
        }

        return InvMovimiento::get_existencia_actual($item_id, $bodega_id, $fecha);
    }

    // AL cambiar la selección de un producto en el formulario de ingreso_productos_2.blade.php
    public function post_ajax(Request $request)
    {
        $producto = InvProducto::find($request->inv_producto_id);

        $producto->descripcion = $producto->get_value_to_show();

        $producto->unidad_medida1 = $producto->get_unidad_medida1();

        $costo_prom = InvCostoPromProducto::get_costo_promedio( $request->id_bodega, $request->inv_producto_id);

        $producto->precio_compra = $costo_prom;

        // Obtener existencia actual
        $existencia_actual = InvMovimiento::get_existencia_actual($request->inv_producto_id, $request->id_bodega, $request->fecha_aux);

        $producto->existencia_actual = $existencia_actual;
        if ($this->item_es_un_platillo($producto->id) && (int)config('inventarios.generare_ensamble_automatico_en_salidas_mercancias')) {
            $producto->existencia_actual = 99999999;
        }

        return $producto;
    }

    public function item_es_un_platillo($item_id)
    {
        $receta = RecetaCocina::where([
            ['item_platillo_id','=',$item_id]
        ])->get()->first();

        if ($receta == null) {
            return false;
        }

        return true;
    }

    //
    // FUNCIONES AUXILIARES

    public function get_productos($tipo)
    {
        $opciones = InvProducto::where('estado', 'Activo')
            ->where('tipo', 'LIKE', '%' . $tipo . '%')
            ->get();
        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->id . ' ' . $opcion->descripcion;
        }

        return $vec;
    }

    public function get_lista_productos()
    {

        $descripcion = 'arr';

        $opciones = InvProducto::where('estado', 'Activo')
            ->where('descripcion', 'LIKE', '%' . $descripcion . '%')
            ->get();

        $lista2 = '';
        $lista = '[';
        $primero = true;
        $i = 0;
        foreach ($opciones as $opcion) {
            if ($primero) {
                $lista .= '{"id":"' . $opcion->id . '","value":"' . $opcion->descripcion . '"}';
                $primero = false;
            } else {
                $lista .= ',{"id":"' . $opcion->id . '","value":"' . $opcion->descripcion . '"}';
            }
            $i++;

            $lista2 .= $opcion->descripcion . '<br>';
        }
        $lista .= ']';

        return $lista;
    }

    // Proceso de eliminar un GRUPO DE INVENTARIO
    public static function eliminar_grupo_inventario($inv_grupo_id)
    {
        $registro = InvGrupo::find($inv_grupo_id);

        // Verificación 1: Está en un producto
        $cantidad = InvProducto::where('inv_grupo_id', $inv_grupo_id)->count();
        if ($cantidad != 0) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Grupo de inventario NO puede ser eliminado. Tiene asignación en productos.');
        }

        //Borrar Registro
        $registro->delete();

        return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Grupo de inventario ELIMINADO correctamente. Descripcion: ' . $registro->descripcion);
    }

    // Proceso de eliminar una BODEGA
    public static function eliminar_bodega($inv_bodega_id)
    {
        $registro = InvBodega::find($inv_bodega_id);

        // Verificación 1: Está en un producto
        $cantidad = InvMovimiento::where('inv_bodega_id', $inv_bodega_id)
                                    ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                    ->count();
        if ($cantidad != 0) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Bodega NO puede ser eliminada. Tiene movimientos.');
        }

        //Borrar Registro
        $registro->delete();

        return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Bodega ELIMINADA correctamente. Descripcion: ' . $registro->descripcion);
    }

    // Anular documento de inventario
    public function anular_documento($documento_id)
    {
        $this->set_variables_globales();

        $documento = InvDocEncabezado::find($documento_id);

        if ($documento->estado == 'Facturada')
        {
            return redirect('inventarios/' . $documento_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('mensaje_error', 'Registro NO puede ser modificado. El documento ya ha sido facturado.');
        }

        // Antes de anular el documento, por cada producto ingresado se debe
        // Validar saldos negativos en movimientos de inventarios LÍNEA X LÍNEA
        $linea_saldo_negativo = '0';
        if ((int)config('ventas.permitir_inventarios_negativos') == 0) {
            $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores_todas_lineas($documento, 'no_fecha', 'anular', 'segun_motivo');
        }

        if ($linea_saldo_negativo != '0')
        {
            return redirect('inventarios/' . $documento_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('mensaje_error', $linea_saldo_negativo);
        }

        // El método siguiente se llama desde otros Controllers
        InventarioController::anular_documento_inventarios( $documento_id );

        return redirect('inventarios/' . $documento_id . $this->variables_url)->with('flash_message', 'Documento ANULADO correctamente.');
    }

    // Este método no hace validación de existencias
    // Dichas validaciones se debieron hacer antes.
    public static function anular_documento_inventarios($doc_encabezado_id)
    {
        $documento = InvDocEncabezado::find($doc_encabezado_id);

        // Eliminar Movimineto contable
        ContabMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo)
            ->delete();

        // Eliminar movimiento de inventarios
        InvMovimiento::where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->where('consecutivo', $documento->consecutivo)
            ->delete();

        // Marcar registros del documento como anulados
        $registros = InvDocRegistro::where('inv_doc_encabezado_id', $documento->id)->get();
        
        // Calcular costos promedios de cada producto del documento, cuando el motivo del movimiento es de entrada
        $average_cost_serv = new AverageCost();
        foreach ($registros as $linea)
        {
            $motivo = InvMotivo::find($linea->inv_motivo_id);
            if ($motivo->movimiento == 'entrada')
            {
                // Se CALCULA el nuevo costo promedio del movimiento con el producto YA retirado
                $costo_prom = $average_cost_serv->calculate_average_cost($linea->inv_bodega_id, $linea->inv_producto_id, $linea->costo_unitario, $documento->fecha, $linea->cantidad);
                
                self::actualizar_costo_promedio($linea->inv_bodega_id, $linea->inv_producto_id, $costo_prom, $documento->core_tipo_transaccion_id, $average_cost_serv);

                // Marcar cada registro del documento como Anulado
                $linea->update(['estado' => 'Anulado', 'modificado_por' => Auth::user()->email]);
            }
        }

        // Para una remisión de ventas, se activa nuevamente el pedido de ventas, si se generó con base en pedido
        if( $documento->core_tipo_transaccion_id == 24 ) 
        {
            $pedido = VtasDocEncabezado::find( $documento->vtas_doc_encabezado_origen_id );
            if( !is_null($pedido) )
            {

                self::actualizar_cantidades_pendientes( $pedido, $documento, 'sumar' );

                $pedido->estado = "Pendiente";
                $pedido->save();

                $documento->vtas_doc_encabezado_origen_id = 0;
                $documento->save();
            }      
        }

        // Para una entrada de almacén, se activa nuevamente la orden de compras, si se generó con base en OC
        if( $documento->core_tipo_transaccion_id == 35 ) 
        {
            $orden_compra = ComprasDocEncabezado::where( 'entrada_almacen_id', $documento->id )->get()->first();
            if( !is_null($orden_compra) )
            {
                $orden_compra->entrada_almacen_id = 0;
                $orden_compra->estado = "Pendiente";
                $orden_compra->save();
            }       
        }

        // Si esta relacionado con una Orden de Trabajo
        if ( Schema::hasTable( 'nom_ordenes_de_trabajo' ) )
        {
            OrdenDeTrabajo::where( 'inv_doc_encabezado_id',$documento->id )->update(['inv_doc_encabezado_id'=>0]);
        }

        // Marcar documento como Anulado
        $documento->update(['estado' => 'Anulado', 'modificado_por' => Auth::user()->email]);
    }

    // Petición AJAX. Parámetro enviados por GET
    public function get_formulario_edit_registro()
    {
        $linea_registro = InvDocRegistro::get_un_registro( Input::get('linea_registro_id') );
        $doc_encabezado = InvDocEncabezado::get_registro_impresion($linea_registro->inv_doc_encabezado_id);

        // Se debe recuperar el cantidad original del saldo a la fecha de la linea que se está editando
        $saldo_a_la_fecha = InvMovimiento::get_existencia_actual($linea_registro->producto_id, $linea_registro->inv_bodega_id, $doc_encabezado->fecha);

        $id = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $id_transaccion = Input::get('id_transaccion');

        $producto = InvProducto::find( $linea_registro->producto_id );

        $formulario = View::make('inventarios.incluir.formulario_editar_registro', compact('linea_registro', 'id', 'id_modelo', 'id_transaccion', 'saldo_a_la_fecha', 'doc_encabezado','producto'))->render();

        return $formulario;
    }

    // Modificar una línea de un documento
    public function doc_registro_guardar(Request $request)
    {
        $linea_registro = InvDocRegistro::find($request->linea_registro_id);
        $doc_encabezado = InvDocEncabezado::find($linea_registro->inv_doc_encabezado_id);

        if ($doc_encabezado->estado == 'Facturada') {
            return redirect('inventarios/' . $doc_encabezado->id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('mensaje_error', 'Registro NO puede ser modificado. El documento ya ha sido facturado.');
        }

        $costo_unitario = $request->costo_unitario;
        $cantidad = $request->cantidad;

        (new InvDocumentsLinesService())->update_document_line($linea_registro, $costo_unitario, $cantidad);

        return redirect('inventarios/' . $doc_encabezado->id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'El registro del documento fue MODIFICADO correctamente.');
    }

    /**
     * 
     */
    public function get_validacion_saldo_movimientos_posteriores($bodega_id, $producto_id, $fecha, $cantidad_nueva, $saldo_a_la_fecha, $movimiento)
    {
        $producto = InvProducto::find( $producto_id );

        if ( $producto->tipo == 'servicio' )
        {
            return 0;
        }

        // $saldo_original_a_la_fecha: es el saldo a la fecha como si no hubiese existido la cantidad de la línea que se está validando.
        $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores($bodega_id, $producto_id, $fecha, $cantidad_nueva, $saldo_a_la_fecha, $movimiento);
        
        if ( $linea_saldo_negativo[0] != null) 
        {
            if ($linea_saldo_negativo[0]->id == 0) {
                return 'Saldo negativo a la fecha.' . ' Producto: ' . InvProducto::find($producto_id)->descripcion . ', Saldo: ' . end($linea_saldo_negativo[1]);
            }

            $doc_inventario = InvDocEncabezado::get_registro_impresion($linea_saldo_negativo[0]->inv_doc_encabezado_id);
            return 'La transacción arroja saldos negativos en movimentos posteriores. Fecha: ' . $doc_inventario->fecha . ', Documento: ' . $doc_inventario->documento_transaccion_prefijo_consecutivo . ', Saldo: ' . end($linea_saldo_negativo[1]);
        }

        return 0;
    }

    /**
     * 
     */
    public function recosteo_form()
    {
        $this->set_variables_globales();
        $miga_pan = [
            ['url' => $this->app->app . '?id=' . Input::get('id'), 'etiqueta' => $this->app->descripcion],
            ['url' => 'NO', 'etiqueta' => 'Proceso: Recosteo']
        ];

        $productos = InvProducto::opciones_campo_select();
        return view('inventarios.recosteo_form', compact('miga_pan', 'productos'));
    }

    /**
     * 
     */
    public static function contabilizar_registro_inv( $datos, $contab_cuenta_id, $detalle_operacion, $valor_debito, $valor_credito )
    {
        ContabMovimiento::create( $datos + 
                            [ 'contab_cuenta_id' => $contab_cuenta_id ] +
                            [ 'detalle_operacion' => $detalle_operacion] + 
                            [ 'valor_debito' => $valor_debito] + 
                            [ 'valor_credito' => ($valor_credito * -1) ] + 
                            [ 'valor_saldo' => ( $valor_debito - $valor_credito ) ]
                        );
    }

    /**
     * 
     */
    public static function contabilizar_documento_inventario( $documento_id, $detalle_operacion )
    {
        $documento = InvDocEncabezado::find( $documento_id );

        // Obtener líneas de registros del documento
        $registros_documento = InvDocRegistro::where( 'inv_doc_encabezado_id', $documento->id )->get();

        foreach ($registros_documento as $linea)
        {
            $motivo = InvMotivo::find( $linea->inv_motivo_id );

            // Si el movimiento es de ENTRADA de inventarios, se DEBITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'entrada')
            {
                // Inventarios (DB)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, abs($linea->costo_total), 0);
                
                // Cta. Contrapartida (CR)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, 0, abs($linea->costo_total) );
            }

            // Si el movimiento es de SALIDA de inventarios, se ACREDITA la cta. de inventarios vs la cta. contrapartida
            if ( $motivo->movimiento == 'salida')
            {
                // Inventarios (CR)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios( $linea->inv_producto_id );
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_inventarios_id, $detalle_operacion, 0, abs($linea->costo_total));
                
                // Cta. Contrapartida (DB)
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                ContabilidadController::contabilizar_registro2( $documento->toArray() + $linea->toArray(), $cta_contrapartida_id, $detalle_operacion, abs($linea->costo_total), 0 );
            }
                
        }
    }

}
