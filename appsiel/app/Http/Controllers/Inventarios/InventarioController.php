<?php

namespace App\Http\Controllers\Inventarios;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Sistema\CrudController;
use App\Http\Controllers\Core\TransaccionController;

use App\Http\Controllers\Contabilidad\ContabilidadController;

// Objetos 
use App\Sistema\Html\TablaIngresoLineaRegistros;
use App\Sistema\Html\BotonesAnteriorSiguiente;

// Modelos
use App\Sistema\TipoTransaccion;
use App\Core\TipoDocApp;
use App\Sistema\Modelo;
use App\Sistema\Campo;

use App\Core\Empresa;

use App\Inventarios\InvTransaccion;
use App\Inventarios\InvBodega;
use App\Inventarios\InvProducto;
use App\Inventarios\InvGrupo;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\MinStock;

use App\Compras\ComprasDocEncabezado;
use App\Ventas\VtasDocEncabezado;

use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;

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

        // Existencias por bodegas
        $bodegas = InvBodega::get();
        $i = 0;
        $cantidad_graficas = 0;
        $titulos = [];
        foreach ($bodegas as $una_bodega) {
            unset($movimientos);
            //$movimientos['bodega'][$i] = $una_bodega->descripcion;
            $movimientos['registros'][$i] = InvMovimiento::where('inv_movimientos.inv_bodega_id', '=', $una_bodega->id)
                ->where('inv_productos.tipo', '=', 'producto')
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
                    $stocksTable->addRow([$registro['Producto'], (float) $registro['Cantidad']]);
                    // Se valida si los productos tienen cantidad mayor que cero
                    // Si al menos un producto tiene existencia, se dibuja la grafica
                    if ((float) $registro['Cantidad'] > 0) {
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

        $tipo_tranferencia = 2;

        $id_transaccion = $this->transaccion->id;

        $lista_campos = ModeloController::get_campos_modelo($this->modelo, '', 'create');
        $cantidad_campos = count($lista_campos);


        $lista_campos = ModeloController::personalizar_campos($id_transaccion, $this->transaccion, $lista_campos, $cantidad_campos, 'create', $tipo_tranferencia);

        $form_create = [
            'url' => $this->modelo->url_form_create,
            'campos' => $lista_campos
        ];

        $productos = InventarioController::get_productos('r');
        $servicios = InventarioController::get_productos('servicio');

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);

        $miga_pan = $this->get_array_miga_pan($this->app, $this->modelo, 'Crear: ' . $this->transaccion->descripcion);

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = ''; //new TablaIngresoLineaRegistros( InvTransaccion::get_datos_tabla_ingreso_lineas_registros( $tipo_transaccion, $motivos ) );

        return view('inventarios.create', compact('form_create', 'id_transaccion', 'productos', 'servicios', 'motivos', 'miga_pan', 'tabla'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lineas_registros = json_decode($request->movimiento);

        // Quitar primera línea
        array_shift($lineas_registros);

        // Quitar las dos últimas líneas
        array_pop($lineas_registros);
        array_pop($lineas_registros);

        $cantidad = count($lineas_registros);
        for ($i = 0; $i < $cantidad; $i++) {

            $lineas_registros[$i]->inv_motivo_id = explode("-", $lineas_registros[$i]->motivo)[0];
            $lineas_registros[$i]->costo_unitario = (float) substr($lineas_registros[$i]->costo_unitario, 1);
            $lineas_registros[$i]->cantidad = (float) substr($lineas_registros[$i]->cantidad, 0, strpos($lineas_registros[$i]->cantidad, " "));
            $lineas_registros[$i]->costo_total = (float) substr($lineas_registros[$i]->costo_total, 1);

            if (!is_null($request->modo_ajuste)) {
                if ($request->modo_ajuste == 'solo_cantidad') {
                    $lineas_registros[$i]->costo_unitario = 0;
                    $lineas_registros[$i]->costo_total = 0;
                }
            }
        }

        $doc_encabezado_id = InventarioController::crear_documento($request, $lineas_registros, $request->url_id_modelo);

        return redirect('inventarios/' . $doc_encabezado_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion);
    }


    /*
        Este método se llamada desde VentaController y CompraController
        Crea un documento completo: encabezados, registros, movimiento y contabilización
        Devuelve en ID del documento creado
    */
    public static function crear_documento(Request $request, array $lineas_registros, $modelo_id)
    {
        $request['creado_por'] = Auth::user()->email;
        $doc_encabezado = CrudController::crear_nuevo_registro($request, $modelo_id);

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

        $cantidad_registros = count($lineas_registros);
        for ($i = 0; $i < $cantidad_registros; $i++) {
            $costo_unitario = (float) $lineas_registros[$i]->costo_unitario;
            $cantidad = (float) $lineas_registros[$i]->cantidad;
            $costo_total = (float) $lineas_registros[$i]->costo_total;

            $motivo = InvMotivo::find($lineas_registros[$i]->inv_motivo_id);

            // Cuando el motivo de la transacción es de salida, 
            // las cantidades y costos totales restan del movimiento ( negativo )
            if ($motivo->movimiento == 'salida') {
                $cantidad = (float) $cantidad * -1;
                $costo_total = (float) $costo_total * -1;
            }

            $linea_datos = ['inv_motivo_id' => $lineas_registros[$i]->inv_motivo_id] +
                ['inv_producto_id' => $lineas_registros[$i]->inv_producto_id] +
                ['costo_unitario' => $costo_unitario] +
                ['cantidad' => $cantidad] +
                ['costo_total' => $costo_total];

            InvDocRegistro::create(
                $datos +
                    ['inv_doc_encabezado_id' => $doc_encabezado->id] +
                    $linea_datos
            );

            $tipo_producto = InvProducto::find($lineas_registros[$i]->inv_producto_id)->tipo;
            if ($tipo_producto == 'producto')
            {
                $datos['consecutivo'] = $doc_encabezado->consecutivo;
                InvMovimiento::create(
                    $datos +
                        ['inv_doc_encabezado_id' => $doc_encabezado->id] +
                        $linea_datos
                );
            }else{
                // Si no es un producto, saltar la contabilización de abajo.
                continue;
            }


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
            if ($motivo->movimiento == 'salida') {
                $valor_debito = 0;
                $valor_credito = abs($costo_total);
            }

            // 3. Contabilizar DB
            InventarioController::contabilizar_registro_inv($datos + $linea_datos, $cta_inventarios_id, $detalle_operacion, $valor_debito, $valor_credito);
            // 4. Contabilizar CR
            InventarioController::contabilizar_registro_inv($datos + $linea_datos, $cta_contrapartida_id, $detalle_operacion, $valor_credito, $valor_debito);


            // Cuando es una transaferencia, se deben guardar los registros de la bodega destino
            if ($request->core_tipo_transaccion_id == $tipo_transferencia) 
            {
                $motivo_entrada_transferencia = 9;
                $cantidad = (float) $cantidad * -1;
                $costo_total = (float) $costo_total * -1;

                // Se cambia el valor de la bodega principal del request
                $datos['inv_bodega_id'] = $request->bodega_destino_id;

                $linea_datos = ['inv_doc_encabezado_id' => $doc_encabezado->id] +
                    ['inv_motivo_id' => $motivo_entrada_transferencia] +
                    ['inv_producto_id' => $lineas_registros[$i]->inv_producto_id] +
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

                InventarioController::contabilizar_registro_inv($datos + $linea_datos, $cta_inventarios_id, $detalle_operacion, abs($costo_total), 0);

                // Para transferencias, la cuenta contrapartida es la misma de inventarios
                InventarioController::contabilizar_registro_inv($datos + $linea_datos, $cta_inventarios_id, $detalle_operacion, 0, abs($costo_total) );

                // PARA LA BODEGA DESTINO
                // Se CALCULA el costo promedio del movimiento, si no existe será el enviado en el request
                $costo_prom = TransaccionController::calcular_costo_promedio($request->bodega_destino_id, $lineas_registros[$i]->inv_producto_id, $costo_unitario, $request->fecha);

                // Actualizo/Almaceno el costo promedio
                TransaccionController::set_costo_promedio($request->bodega_destino_id, $lineas_registros[$i]->inv_producto_id, $costo_prom);

                // Se vuelve a colocar el valor del request a la bodega principal
                $datos['inv_bodega_id'] = $request->inv_bodega_id;
            }

            // Si es una entrada, se calcula el costo promedio por bodega y producto
            //if ($request->core_tipo_transaccion_id==$tipo_entrada) {
            if ($motivo->movimiento == 'entrada') {
                // Se CALCULA el costo promedio del movimiento, si no existe será el enviado en el request
                $costo_prom = TransaccionController::calcular_costo_promedio($request->inv_bodega_id, $lineas_registros[$i]->inv_producto_id, $costo_unitario, $request->fecha);

                // Actualizo/Almaceno el costo promedio
                TransaccionController::set_costo_promedio($request->inv_bodega_id, $lineas_registros[$i]->inv_producto_id, $costo_prom);
            }
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
                    <b>Factura de compras: </b> <a href="' . url('compras/' . $fatura_compra->id . '?id=9&id_modelo=147&id_transaccion=' . $reg_fatura_compras->core_tipo_transaccion_id) . '" target="_blank">' . $fatura_compra->documento_transaccion_prefijo_consecutivo . '</a>';
        }

        // Verificar si pertenece a una documento de ventas
        $reg_factura_venta = VtasDocEncabezado::where('remision_doc_encabezado_id', $doc_encabezado->id)
            ->orWhere('remision_doc_encabezado_id', 'LIKE', '%,' . $doc_encabezado->id)
            ->orWhere('remision_doc_encabezado_id', 'LIKE', $doc_encabezado->id . ',%')
            ->orWhere('remision_doc_encabezado_id', 'LIKE', '%,' . $doc_encabezado->id . ',%')
            ->get()
            ->first();
        $enlace2 = '';
        if (!is_null($reg_factura_venta)) {
            $fatura_venta = VtasDocEncabezado::get_registro_impresion($reg_factura_venta->id);
            $enlace2 = '<br/>
                    <b>Factura de ventas: </b> <a href="' . url('ventas/' . $fatura_venta->id . '?id=13&id_modelo=139&id_transaccion=' . $reg_factura_venta->core_tipo_transaccion_id) . '" target="_blank">' . $fatura_venta->documento_transaccion_prefijo_consecutivo . '</a>';
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
                $view = $this->generar_documento_vista(Input::get('id_transaccion'), $id, 'inventarios.formatos.remision');
                break;

            case '3':
                $view = $this->generar_documento_vista(Input::get('id_transaccion'), $id, 'inventarios.formatos.remision_pos');
                break;
            
            default:
                // No se especifica formato de impresión 
                $view = View::make('inventarios.pdf', compact('datos_encabezado_doc', 'descripcion_transaccion', 'productos', 'elaboro') )->render();
                break;
        }


        // Se prepara el PDF
        $orientacion = 'portrait';
        $tam_hoja = 'Letter';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);

        return $pdf->stream($descripcion_transaccion . '_' . $encabezado_doc->consecutivo . '.pdf');
        //return $view;
    }

    /*
        Generar la vista para los métodos show(), imprimir() o enviar_por_email()
    */
    public function generar_documento_vista($id_transaccion, $id, $ruta_vista)
    {
        $transaccion = TipoTransaccion::find($id_transaccion);

        $doc_encabezado = app($transaccion->modelo_encabezados_documentos)->get_registro_impresion($id);

        $doc_registros = app($transaccion->modelo_registros_documentos)->get_registros_impresion($doc_encabezado->id);

        $empresa = Empresa::find($doc_encabezado->core_empresa_id);

        return View::make($ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa'))->render();
    }


    // Parámetro enviados por GET
    public function consultar_productos()
    {
        $campo_busqueda = Input::get('campo_busqueda');

        switch ($campo_busqueda) {
            case 'codigo_barras':
                $operador = '=';
                $texto_busqueda = Input::get('texto_busqueda');
                break;
            case 'descripcion':
                $operador = 'LIKE';
                $texto_busqueda = '%' . str_replace( " ", "%", Input::get('texto_busqueda') ) . '%';
                break;
            case 'id':
                $operador = 'LIKE';
                $texto_busqueda = Input::get('texto_busqueda') . '%';
                break;

            default:
                # code...
                break;
        }

        //$producto = InvProducto::where('estado', 'Activo')->where($campo_busqueda, $operador, $texto_busqueda)->get()->take(7);


        if ( $campo_busqueda == 'descripcion')
        {
            $producto = InvProducto::where('estado', 'Activo')
                                ->having('nueva_cadena', $operador, $texto_busqueda)
                                ->select( 
                                            DB::raw('CONCAT( descripcion, " ", categoria_id, " ", unidad_medida2) AS nueva_cadena'),
                                            'id',
                                            'categoria_id',
                                            'unidad_medida2' )
                                ->get()
                                ->take(7);
        }else{
            $producto = InvProducto::where('estado', 'Activo')
                                    ->where($campo_busqueda, $operador, $texto_busqueda)
                                    ->select( 
                                            DB::raw('CONCAT( descripcion, " ", categoria_id, " ", unidad_medida2) AS nueva_cadena'),
                                            'id',
                                            'categoria_id',
                                            'unidad_medida2' )
                                    ->get()
                                    ->take(7);
        }/**/
            

        $html = '<div class="list-group">';
        $es_el_primero = true;
        foreach ($producto as $linea) {
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
            }

            //$html .= '<a class="list-group-item list-group-item-productos ' . $clase . ' flecha_mover" data-descripcion="' . $linea->descripcion . '" data-producto_id="' . $linea->id . '">' . $linea->id . ' ' . $linea->descripcion . '</a>';

            $html .= '<a class="list-group-item list-group-item-productos ' . $clase . ' flecha_mover" data-descripcion="' . $linea->nueva_cadena . '" data-producto_id="' . $linea->id . '">' . $linea->id . ' ' . $linea->nueva_cadena  . '</a>';
        }
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
            if ($transaccion_id != 1) {
                $costo_prom = InvCostoPromProducto::where('inv_bodega_id', '=', $bodega_id)
                    ->where('inv_producto_id', '=', $producto['id'])
                    ->value('costo_promedio');
                if ($costo_prom > 0) {
                    $producto = array_merge($producto, ['precio_compra' => $costo_prom]);
                }
            }

            // Obtener existencia actual
            $existencia_actual = InvMovimiento::get_existencia_actual($producto['id'], $bodega_id, Input::get('fecha'));

            $producto = array_merge($producto, ['existencia_actual' => $existencia_actual], ['tipo' => $producto['tipo']]);
        }

        return $producto;
    }

    // AL cambiar la selección de un producto en el formulario de ingreso_productos_2.blade.php
    public function post_ajax(Request $request)
    {
        $producto = InvProducto::find($request->inv_producto_id)->toArray();

        $costo_prom = InvCostoPromProducto::where('inv_bodega_id', '=', $request->id_bodega)
            ->where('inv_producto_id', '=', $request->inv_producto_id)
            ->value('costo_promedio');
        if ($costo_prom > 0) {
            $producto = array_merge($producto, ['precio_compra' => $costo_prom]);
        }


        // Obtener existencia actual
        $existencia_actual = InvMovimiento::get_existencia_actual($request->inv_producto_id, $request->id_bodega, $request->fecha_aux);

        $producto = array_merge($producto, ['existencia_actual' => $existencia_actual], ['tipo' => $producto['tipo']]);

        return $producto;
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
        $cantidad = InvMovimiento::where('inv_bodega_id', $inv_bodega_id)->count();
        if ($cantidad != 0) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Bodega NO puede ser eliminada. Tiene movimientos.');
        }

        //Borrar Registro
        $registro->delete();

        return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Bodega ELIMINADA correctamente. Descripcion: ' . $registro->descripcion);
    }

    // Proceso de eliminar un PRODUCTO
    public static function eliminar_producto($inv_producto_id)
    {
        $registro = InvProducto::find($inv_producto_id);

        // Verificación 1: Tiene movimientos
        $cantidad = InvMovimiento::where('inv_producto_id', $inv_producto_id)->count();
        if ($cantidad != 0) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('mensaje_error', 'Item NO puede ser eliminado. Tiene movimientos.');
        }

        //Borrar Registro
        $registro->delete();

        return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))->with('flash_message', 'Item ELIMINADO correctamente. Descripcion: ' . $registro->descripcion);
    }

    // Anular documento de inventario
    public function anular_documento($documento_id)
    {
        $this->set_variables_globales();

        $documento = InvDocEncabezado::find($documento_id);

        if ($documento->estado == 'Facturada') {
            return redirect('inventarios/' . $documento_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('mensaje_error', 'Registro NO puede ser modificado. El documento ya ha sido facturado.');
        }

        // Antes de anular el documento, por cada producto ingresado se debe
        // Validar saldos negativos en movimientos de inventarios LÍNEA X LÍNEA
        $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores_todas_lineas($documento, 'no_fecha', 'anular', 'segun_motivo');

        if ($linea_saldo_negativo != '0') {
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
            ->where('consecutivo', $documento->consecutivo)
            ->delete();

        // Marcar registros del documento como anulados
        $registros = InvDocRegistro::where('inv_doc_encabezado_id', $documento->id)->get();

        // Calcular costos promedios de cada producto del documento, cuando el motivo del movimiento es de entrada
        foreach ($registros as $linea) {
            $motivo = InvMotivo::find($linea->inv_motivo_id);
            if ($motivo->movimiento == 'entrada') {
                // Se CALCULA el nuevo costo promedio del movimiento con el producto YA retirado
                $costo_prom = TransaccionController::calcular_costo_promedio($linea->inv_bodega_id, $linea->inv_producto_id, $linea->costo_unitario, $documento->fecha);

                // Actualizo/Almaceno el costo promedio
                TransaccionController::set_costo_promedio($linea->inv_bodega_id, $linea->inv_producto_id, $costo_prom);

                // Marcar cada registro del documento como Anulado
                $linea->update(['estado' => 'Anulado', 'modificado_por' => Auth::user()->email]);
            }
        }

        // Marcar documento como Anulado
        $documento->update(['estado' => 'Anulado', 'modificado_por' => Auth::user()->email]);
    }



    // Petición AJAX. Parámetro enviados por GET
    public function get_formulario_edit_registro()
    {
        $linea_registro = InvDocRegistro::get_un_registro(Input::get('linea_registro_id'));
        $doc_encabezado = InvDocEncabezado::get_registro_impresion($linea_registro->inv_doc_encabezado_id);

        // Se debe recuperar el cantidad original del saldo a la fecha de la linea que se está editando
        $saldo_a_la_fecha = InvMovimiento::get_existencia_actual($linea_registro->producto_id, $linea_registro->inv_bodega_id, $doc_encabezado->fecha);

        $id = Input::get('id');
        $id_modelo = Input::get('id_modelo');
        $id_transaccion = Input::get('id_transaccion');

        $producto = InvProducto::find( $linea_registro->inv_producto_id );

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

        $motivo = InvMotivo::find($linea_registro->inv_motivo_id);

        $costo_unitario = $request->costo_unitario;
        $cantidad = $request->cantidad;

        if ($motivo->movimiento == 'salida') {
            $cantidad = $request->cantidad * -1;
        }

        $costo_total = $costo_unitario * $cantidad;

        $producto = InvProducto::find( $linea_registro->inv_producto_id );

        if ( $producto->tipo == 'producto')
        {
            // 1. Actualiza movimiento de inventarios
            InvMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                ->where('consecutivo', $doc_encabezado->consecutivo)
                ->where('inv_producto_id', $linea_registro->inv_producto_id)
                ->where('cantidad', $linea_registro->cantidad)
                ->update([
                    'costo_unitario' => $costo_unitario,
                    'cantidad' => $cantidad,
                    'costo_total' => $costo_total
                ]);

            // 2. Si es un motivo de entrada, se calcula el costo promedio
            if ($motivo->movimiento == 'entrada') {
                // Se CALCULA el costo promedio del movimiento, si no existe será el enviado en el request
                $costo_prom = TransaccionController::calcular_costo_promedio($linea_registro->inv_bodega_id, $linea_registro->inv_producto_id, $costo_unitario, $doc_encabezado->fecha);

                // Actualizo/Almaceno el costo promedio
                TransaccionController::set_costo_promedio($linea_registro->inv_bodega_id, $linea_registro->inv_producto_id, $costo_prom);


                // Si el motivo es de entrada SE DEBITA EL INVENTARIO
                // 3. Actualizar movimiento contable del registro del documento de inventario
                // Inventarios (DB)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios($linea_registro->inv_producto_id);
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_inventarios_id)
                    ->update([
                        'valor_debito' => abs($costo_total),
                        'valor_saldo' => abs($costo_total),
                        'cantidad' => $cantidad
                    ]);

                // Cta. Contrapartida (CR) Dada por el motivo de inventarios de la transaccion 
                // Motivos de inventarios y ventas: Costo de ventas
                // Moivos de compras: Cuentas por legalizar
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_contrapartida_id)
                    ->update([
                        'valor_credito' => abs($costo_total) * -1,
                        'valor_saldo' => abs($costo_total) * -1,
                        'cantidad' => $cantidad
                    ]);
            } else {

                // Si el motivo es de SALIDA se ACREDITA EL INVENTARIO
                // 3. Actualizar movimiento contable del registro del documento de inventario
                // Inventarios (CR)
                $cta_inventarios_id = InvProducto::get_cuenta_inventarios($linea_registro->inv_producto_id);
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_inventarios_id)
                    ->update([
                        'valor_credito' => abs($costo_total) * -1,
                        'valor_saldo' => abs($costo_total) * -1,
                        'cantidad' => $cantidad
                    ]);

                // Cta. Contrapartida (DB) Dada por el motivo de inventarios de la transaccion 
                // Motivos de inventarios y ventas: Costo de ventas
                // Moivos de compras: Cuentas por legalizar
                $cta_contrapartida_id = $motivo->cta_contrapartida_id;
                ContabMovimiento::where('core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
                    ->where('core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
                    ->where('consecutivo', $doc_encabezado->consecutivo)
                    ->where('inv_producto_id', $linea_registro->inv_producto_id)
                    ->where('cantidad', $linea_registro->cantidad)
                    ->where('contab_cuenta_id', $cta_contrapartida_id)
                    ->update([
                        'valor_debito' => abs($costo_total),
                        'valor_saldo' => abs($costo_total),
                        'cantidad' => $cantidad
                    ]);
            }
        } // Fin Si es producto

        // 4. Actualizar el registro del documento de factura
        $linea_registro->update([
            'costo_unitario' => $costo_unitario,
            'cantidad' => $cantidad,
            'costo_total' => $costo_total
        ]);


        return redirect('inventarios/' . $doc_encabezado->id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'El registro del documento fue MODIFICADO correctamente.');
    }





    public function get_validacion_saldo_movimientos_posteriores($bodega_id, $producto_id, $fecha, $cantidad_nueva, $saldo_a_la_fecha, $movimiento)
    {
        $producto = InvProducto::find( $producto_id );

        if ( $producto->tipo == 'servicio' )
        {
            return 0;
        }

        // $saldo_original_a_la_fecha: es el saldo a la fecha como si no hubiese existido la cantidad de la línea que se está validando.
        $linea_saldo_negativo = InvMovimiento::validar_saldo_movimientos_posteriores($bodega_id, $producto_id, $fecha, $cantidad_nueva, $saldo_a_la_fecha, $movimiento);

        if (!is_null($linea_saldo_negativo[0])) {
            if ($linea_saldo_negativo[0]->id == 0) {
                return 'Saldo negativo a la fecha.' . ' Producto: ' . InvProducto::find($producto_id)->descripcion . ', Saldo: ' . end($linea_saldo_negativo[1]);
            }

            $doc_inventario = InvDocEncabezado::get_registro_impresion($linea_saldo_negativo[0]->inv_doc_encabezado_id);
            return 'La transacción arroja saldos negativos en movimentos posteriores. Fecha: ' . $doc_inventario->fecha . ', Documento: ' . $doc_inventario->documento_transaccion_prefijo_consecutivo . ', Saldo: ' . end($linea_saldo_negativo[1]);
        }

        return 0;
    }

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

}
