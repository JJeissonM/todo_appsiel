<?php

namespace App\Http\Controllers\Inventarios;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\Core\TransaccionController;

// Objetos
use App\Sistema\Html\BotonesAnteriorSiguiente;

use App\Core\EncabezadoDocumentoTransaccion;

// Modelos
use App\Sistema\TipoTransaccion;
use App\Sistema\Modelo;


use App\Inventarios\InvProducto;
use App\Inventarios\InvGrupo;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\RecetaCocina;
use App\Inventarios\Services\AjustarSaldosBodegaService;
use App\Compras\Proveedor;
use App\Sistema\Campo;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class InvFisicoController extends TransaccionController
{

    /**
     * Show the form for creating a new resource.
     * Este mÃ©todo create() es llamado desde un botÃ³n-select en el index de inventarios
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo segÃºn la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');

        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create' );

        $url_form_create = 'web';

        if ( $modelo->url_form_create != '')
        {
            $url_form_create = $modelo->url_form_create;
        }

        $form_create = [
                        'url' => $url_form_create,
                        'campos' => $lista_campos,
                        'modo' => 'create'
                    ];

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>$tipo_transaccion->descripcion]
            ];

        $grupos = InvGrupo::opciones_campo_select();

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = '';

        return view('inventarios.inventario_fisico.create', compact('form_create','id_transaccion','motivos','miga_pan','tabla','grupos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lineas_registros = $this->preparar_array_lineas_registros( $request->movimiento );
        
        $doc_encabezado_id = InvFisicoController::crear_documento( $request, $lineas_registros, $request->url_id_modelo );

        return redirect('inv_fisico/'.$doc_encabezado_id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
    }

    public function preparar_array_lineas_registros( $request_registros )
    {
        $lineas_registros = json_decode( $request_registros );

        // Quitar las dos Ãºltimas lÃ­neas
        array_pop($lineas_registros);
        array_pop($lineas_registros);

        $cantidad = count( $lineas_registros );
        for ($i=0; $i < $cantidad; $i++) 
        { 
            $lineas_registros[$i]->inv_motivo_id = explode("-", $lineas_registros[$i]->motivo)[0];
            $lineas_registros[$i]->costo_unitario = (float)$lineas_registros[$i]->costo_unitario;
            $lineas_registros[$i]->cantidad = (float)$lineas_registros[$i]->cantidad;
            $lineas_registros[$i]->costo_total = (float)$lineas_registros[$i]->costo_total;
        }

        return $lineas_registros;
    }


    /*
        Crea un documento completo: encabezados, registros, movimiento y contabilizaciÃ³n
        Devuelve en ID del documento creado
    */
    public static function crear_documento( Request $request, array $lineas_registros, $modelo_id )
    {
        $request['creado_por'] = Auth::user()->email;
        $encabezado_documento = new EncabezadoDocumentoTransaccion( $modelo_id );
        $doc_encabezado = $encabezado_documento->crear_nuevo( $request->all() );

        InvFisicoController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        return $doc_encabezado->id;
    }


    /*
        No Devuelve nada
    */
    public static function crear_registros_documento( Request $request, $doc_encabezado, array $lineas_registros )
    {

        $tipo_transferencia = 2;

        // WARNING: Cuidar de no enviar campos en el request que se repitan en las lineas de registros 
        // Ahora mismo el campo inv_bodega_id se envÃ­a en el request, pero se debe tomar de cada lÃ­nea de registro
        $datos = $request->all();

        $cantidad_registros = count($lineas_registros);
        for ($i=0; $i < $cantidad_registros; $i++) 
        {
            $costo_unitario = (float)$lineas_registros[$i]->costo_unitario;
            $cantidad = (float)$lineas_registros[$i]->cantidad;
            $costo_total = (float)$lineas_registros[$i]->costo_total;

            $linea_datos = [ 'inv_motivo_id' => (int)$lineas_registros[$i]->inv_motivo_id ] +
                            [ 'inv_producto_id' => (int)$lineas_registros[$i]->inv_producto_id ] +
                            [ 'costo_unitario' => $costo_unitario ] +
                            [ 'cantidad' => $cantidad ] +
                            [ 'creado_por' => Auth::user()->email ] +
                            [ 'costo_total' => $costo_total ];

            InvDocRegistro::create( 
                                    $datos + 
                                    [ 'inv_doc_encabezado_id' => $doc_encabezado->id ] +
                                    $linea_datos
                                );
            
            // No se guarda movimiento, ni se contabiliza
        }
    }

    /**
     * 
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->set_variables_globales();

        $doc_encabezado = InvDocEncabezado::get_registro_impresion( $id );

        $botones_anterior_siguiente = new BotonesAnteriorSiguiente( $this->transaccion, $id );
        
        $doc_registros = InvDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $this->preparar_lineas_para_vista( $doc_registros, $doc_encabezado->fecha );

        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;

        $documento_vista = View::make( 'inventarios.inventario_fisico.documento_vista', compact('doc_encabezado', 'doc_registros' ) )->render();

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Inventarios fÃ­sico'],
                ['url'=>'NO','etiqueta'=> $doc_encabezado->documento_transaccion_prefijo_consecutivo ]
            ];
        
        return view( 'inventarios.inventario_fisico.show', compact( 'id', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan', 'empresa','doc_encabezado') );
    }

    /**
     * 
     *
     */
    public function imprimir($id)
    {
        $this->set_variables_globales();
        
        $doc_encabezado = InvDocEncabezado::get_registro_impresion( $id );
        
        $doc_registros = InvDocRegistro::get_registros_impresion( $doc_encabezado->id );

        $this->preparar_lineas_para_vista( $doc_registros, $doc_encabezado->fecha );

        $empresa = $this->empresa;

        $documento_vista = View::make( 'inventarios.inventario_fisico.formato_estandar', compact('doc_encabezado', 'doc_registros', 'empresa' ) )->render();

        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = 'Letter';//array(0,0,50,800);//'A4';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }


    public function hacer_ajuste()
    {

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo segÃºn la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create' );

        $productos = $this->get_productos('r');
        $servicios = $this->get_productos('servicio');

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>$tipo_transaccion->descripcion]
            ];

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = '';

        $doc_encabezado = InvDocEncabezado::get_registro_impresion( Input::get('doc_inv_fisico_id') );
        $doc_registros = InvDocRegistro::get_registros_impresion( $doc_encabezado->id );

        foreach ($doc_registros as $fila)
        {
            $fila->cantidad_sistema = InvMovimiento::get_existencia_producto($fila->producto_id, $fila->inv_bodega_id, $doc_encabezado->fecha )->Cantidad;
            $fila->costo_prom_sistema = InvCostoPromProducto::get_costo_promedio( $fila->inv_bodega_id, $fila->producto_id  );
        }

        foreach ($lista_campos as $key => $value)
        {
            if ($value['name'] == 'inv_bodega_id')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->inv_bodega_id;
            }

            if ($value['name'] == 'fecha')
            {
                $fecha = explode('-', $doc_encabezado->fecha);
                $lista_campos[$key]['value'] = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
            }

            if ($value['name'] == 'descripcion')
            {
                $lista_campos[$key]['value'] = 'Hecho con base en Inventario FÃ­sico: '.$doc_encabezado->documento_transaccion_prefijo_consecutivo;
            }

            if ($value['name'] == 'core_tercero_id')
            {
                $lista_campos[$key]['value'] = $doc_encabezado->core_tercero_id;
            }
        }

        $form_create = [
                        'url' => $modelo->url_form_create,
                        'campos' => $lista_campos,
                        'modo' => 'create'
                    ];

        $cantidad_filas = count( $doc_registros->toarray() );
        $filas_tabla = View::make( 'inventarios.inventario_fisico.tabla_para_ajuste', compact( 'doc_registros', 'motivos' ) )->render();

        return view( 'inventarios.create', compact('form_create','id_transaccion','productos','servicios','motivos','miga_pan','tabla','filas_tabla','cantidad_filas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo segÃºn la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','edit');

        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create' );

        $registro = InvDocEncabezado::get_registro_impresion( $id );

        foreach ($lista_campos as $key => $value)
        {
            if ($value['name'] == 'inv_bodega_id')
            {
                $lista_campos[$key]['value'] = $registro->inv_bodega_id;
            }

            if ($value['name'] == 'fecha')
            {
                $fecha = explode('-', $registro->fecha);
                $lista_campos[$key]['value'] = $fecha[2] . '-' . $fecha[1] . '-' . $fecha[0];
            }

            if ($value['name'] == 'descripcion')
            {
                $lista_campos[$key]['value'] = $registro->descripcion;
            }

            if ($value['name'] == 'core_tercero_id')
            {
                $lista_campos[$key]['value'] = $registro->core_tercero_id;
            }
        }

        $numero_linea = count( $registro->lineas_registros );

        $lineas_registros = '';
        $cantidad_total = 0;
        $costo_total = 0;
        $i = 1;
        foreach ( $registro->lineas_registros as $linea )
        {
            $descripcion_item = $linea->item->get_value_to_show(true);

            $lineas_registros .= '<tr id="' . $linea->inv_producto_id . '">' . 
                                                                '<td class="text-center">' . $linea->inv_producto_id . '</td>' . 
                                                                '<td class="nom_prod">' . $descripcion_item . '</td>' . 
                                                                '<td><span style="color:white;">12-</span><span style="color:green;">Inventario FÃ­sico</span><input type="hidden" class="movimiento" value="entrada"></td>' . 
                                                                '<td class="lbl_costo_unitario">' . $linea->costo_unitario . '</td>' . 
                                                                '<td class="lbl_cantidad"> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> '.$linea->cantidad.'</div> </div> </td>' . 
                                                                '<td class="lbl_costo_total">' . $linea->costo_total . '</td>' . 
                                                                '<td> <button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button> </td>' . 
                                                                '</tr>';
            $i++;
            $cantidad_total += $linea->cantidad;
            $costo_total += $linea->costo_total;
        }

        $url_form_create = 'web';

        if ( $modelo->url_form_create != '')
        {
            $url_form_create = $modelo->url_form_create . '/' . $id;
        }

        $form_create = [
                        'url' => $url_form_create,
                        'campos' => $lista_campos
                    ];

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'NO','etiqueta'=>$tipo_transaccion->descripcion]
            ];

        $grupos = InvGrupo::opciones_campo_select();

        // Dependiendo de la transaccion se genera la tabla de ingreso de lineas de registros
        $tabla = '';

        return view('inventarios.inventario_fisico.edit', compact('form_create','id_transaccion','motivos','miga_pan','tabla','grupos','registro','lineas_registros', 'cantidad_total', 'costo_total'));
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
        $lineas_registros = $this->preparar_array_lineas_registros( $request->movimiento );

        // Actualizar datos del encabezado
        $doc_encabezado = InvDocEncabezado::find($id);
        $doc_encabezado->fecha = $request->fecha;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->core_tercero_id = $request->core_tercero_id;
        $doc_encabezado->modificado_por = Auth::user()->email;
        $doc_encabezado->save();

        // Borrar lÃ­neas de registros anteriores
        InvDocRegistro::where('inv_doc_encabezado_id',$doc_encabezado->id)->delete();

        // Crear nuevamente las lÃ­neas de registros
        $request['creado_por'] = $doc_encabezado->creado_por;
        $request['modificado_por'] = Auth::user()->email;
        InvFisicoController::crear_registros_documento( $request, $doc_encabezado, $lineas_registros );

        return redirect('inv_fisico/'.$id.'?id='.$request->url_id.'&id_modelo='.$request->url_id_modelo.'&id_transaccion='.$request->url_id_transaccion);
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
    
    // ParÃ¡metro enviados por GET
    public function consultar_productos()
    {
        $campo_busqueda = Input::get('campo_busqueda');
        
        switch ( $campo_busqueda ) 
        {
            case 'codigo_barras':
                $operador = '=';
                $texto_busqueda = Input::get('texto_busqueda');
                break;
            case 'descripcion':
                $operador = 'LIKE';
                $texto_busqueda = '%'.Input::get('texto_busqueda').'%';
                break;
            case 'id':
                $operador = 'LIKE';
                $texto_busqueda = Input::get('texto_busqueda').'%';
                break;
            
            default:
                # code...
                break;
        }

        $producto = InvProducto::where('estado','Activo')->where($campo_busqueda,$operador,$texto_busqueda)->get()->take(7);

        $html = '<div class="list-group">';
        $es_el_primero = true;
        foreach ($producto as $linea) 
        {
            $clase = '';
            if ($es_el_primero) {
                $clase = 'active';
                $es_el_primero = false;
            }
            
            $html .= '<a class="list-group-item list-group-item-productos '.$clase.' flecha_mover" data-descripcion="'.$linea->descripcion.'" data-producto_id="'.$linea->id.'">'.$linea->descripcion.'</a>';
        }
        $html .= '</div>';

        return $html;
    }
    

    //
    // FUNCIONES AUXILIARES

    public function get_productos($tipo){
        $opciones = InvProducto::where('estado','Activo')
                            ->where('tipo','LIKE','%'.$tipo.'%')
                            ->get();
        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id]=$opcion->id.' '.$opcion->descripcion;
        }

        return $vec;
    }

    public function get_productos_del_grupo()
    {
        $grupo_id = Input::get('grupo_id');
        $bodega_id = Input::get('bodega_id');
        
        $productos = InvProducto::where('inv_productos.inv_grupo_id', $grupo_id)
                                ->where('inv_productos.estado', 'Activo')
                                ->get();

        $lista_items = [];
        foreach ($productos as $producto) {

            $item = $producto->toarray();
            $item['producto_id'] = $producto->id;
            $item['costo_unitario'] = $producto->get_costo_promedio( $bodega_id );
            $item['producto_descripcion'] = $producto->get_value_to_show(true);

            $lista_items[] = $item;

        }
        return $lista_items;
    }

    /**
     * Preparar datos de lineas para vistas show y imprimir sin N+1 queries.
     */
    private function preparar_lineas_para_vista( $doc_registros, $fecha_corte )
    {
        if ( $doc_registros->count() == 0 )
        {
            return;
        }

        $item_ids = $doc_registros->pluck('inv_producto_id')->unique()->filter()->values()->all();
        $bodega_ids = $doc_registros->pluck('inv_bodega_id')->unique()->filter()->values()->all();

        $items = InvProducto::whereIn('id', $item_ids)
                            ->get(['id','descripcion','unidad_medida1','unidad_medida2','referencia','categoria_id','precio_compra']);

        $items_map = $items->keyBy('id');
        $precio_compra_map = $items->pluck('precio_compra','id')->all();

        // Mapa de unidades de medida (se consulta una sola vez)
        $unidad_map = [];
        $campo = Campo::find(79);
        if ( $campo != null )
        {
            $unidad_map = json_decode( $campo->opciones, true );
            if ( !is_array($unidad_map) )
            {
                $unidad_map = [];
            }
        }

        // Colores por item (si aplica)
        $color_map = [];
        if ( !empty( $item_ids )
            && Schema::hasTable('inv_mandatario_tiene_items')
            && Schema::hasTable('inv_items_mandatarios')
            && Schema::hasTable('inv_indum_paletas_colores')
            && Schema::hasColumn('inv_items_mandatarios', 'paleta_color_id') )
        {
            $colores = DB::table('inv_mandatario_tiene_items as mti')
                        ->leftJoin('inv_items_mandatarios as im', 'im.id', '=', 'mti.mandatario_id')
                        ->leftJoin('inv_indum_paletas_colores as pc', 'pc.id', '=', 'im.paleta_color_id')
                        ->whereIn('mti.item_id', $item_ids)
                        ->select('mti.item_id', 'pc.descripcion as color')
                        ->get();

            foreach ( $colores as $row )
            {
                if ( $row->color != null && $row->color != '' )
                {
                    $color_map[$row->item_id] = $row->color;
                }
            }
        }

        // Codigos de proveedor por categoria (si aplica)
        $proveedor_codigos = [];
        if ( (int)config('inventarios.items_mandatarios_por_proveedor') && Schema::hasTable('compras_proveedores') )
        {
            $categoria_ids = $items->pluck('categoria_id')->unique()->filter()->values()->all();
            if ( !empty( $categoria_ids ) )
            {
                $proveedor_codigos = Proveedor::whereIn('id', $categoria_ids)->pluck('codigo','id')->all();
            }
        }

        $mostrar_referencia = (int)config('inventarios.mostrar_referencia_en_descripcion_items');
        $codigo_principal = config('inventarios.codigo_principal_manejo_productos');

        // Existencias por item y bodega (una sola consulta)
        $exist_map = [];
        if ( !empty( $item_ids ) && !empty( $bodega_ids ) )
        {
            $fecha_corte = \Carbon\Carbon::parse( $fecha_corte )->format('Y-m-d');
            $existencias = InvMovimiento::where('inv_movimientos.core_empresa_id', Auth::user()->empresa_id)
                                ->where('inv_movimientos.fecha', '<=', $fecha_corte)
                                ->whereIn('inv_movimientos.inv_producto_id', $item_ids)
                                ->whereIn('inv_movimientos.inv_bodega_id', $bodega_ids)
                                ->select(
                                        'inv_movimientos.inv_producto_id',
                                        'inv_movimientos.inv_bodega_id',
                                        DB::raw('sum(inv_movimientos.cantidad) as cantidad')
                                    )
                                ->groupBy('inv_movimientos.inv_producto_id')
                                ->groupBy('inv_movimientos.inv_bodega_id')
                                ->get();

            foreach ( $existencias as $row )
            {
                $exist_map[$row->inv_producto_id . ':' . $row->inv_bodega_id] = round( (float)$row->cantidad, 2 );
            }
        }

        // Costos promedio por item (una sola consulta)
        $cost_map = [];
        $maneja_bodegas = (int)config('inventarios.maneja_costo_promedio_por_bodegas');
        $cost_bodegas = $maneja_bodegas ? $bodega_ids : [0];

        if ( !empty( $item_ids ) && !empty( $cost_bodegas ) )
        {
            $costos = InvCostoPromProducto::whereIn('inv_producto_id', $item_ids)
                        ->whereIn('inv_bodega_id', $cost_bodegas)
                        ->get(['inv_producto_id','inv_bodega_id','costo_promedio']);

            foreach ( $costos as $row )
            {
                $cost_map[$row->inv_producto_id . ':' . $row->inv_bodega_id] = (float)$row->costo_promedio;
            }
        }

        foreach ( $doc_registros as $fila )
        {
            $item_id = $fila->inv_producto_id;
            $bodega_id = $fila->inv_bodega_id;

            $existencia = $exist_map[$item_id . ':' . $bodega_id] ?? 0;
            $bodega_key = $maneja_bodegas ? $bodega_id : 0;
            $costo_prom = $cost_map[$item_id . ':' . $bodega_key] ?? ( $precio_compra_map[$item_id] ?? 0 );

            $fila->cantidad_sistema = $existencia;
            $fila->costo_total_sistema = $existencia * $costo_prom;

            $item = $items_map[$item_id] ?? null;
            if ( $item != null )
            {
                $fila->descripcion_item = $this->build_descripcion_item(
                    $item,
                    $color_map,
                    $unidad_map,
                    $proveedor_codigos,
                    $mostrar_referencia,
                    $codigo_principal,
                    true
                );
            }else{
                $fila->descripcion_item = $fila->producto_descripcion ?? '';
            }
        }
    }

    /**
     * Genera la descripcion del item sin consultas adicionales.
     */
    private function build_descripcion_item( $item, $color_map, $unidad_map, $proveedor_codigos, $mostrar_referencia, $codigo_principal, $ocultar_id = true )
    {
        $descripcion_item = $item->descripcion;

        $color = $color_map[$item->id] ?? '';
        if ( $color != '' )
        {
            $descripcion_item .= ' ' . $color;
        }

        $talla = '';
        if ( $item->unidad_medida2 != '' )
        {
            $talla = ' - ' . $item->unidad_medida2;
        }

        $referencia = '';
        if ( $mostrar_referencia && $item->referencia != '' )
        {
            $referencia = ' - ' . $item->referencia;
        }

        $codigo_proveedor = '';
        if ( (int)config('inventarios.items_mandatarios_por_proveedor') )
        {
            $codigo = $proveedor_codigos[$item->categoria_id] ?? '';
            if ( $codigo != '' )
            {
                $codigo_proveedor = ' - ' . $codigo;
            }
        }

        $prefijo = $item->id . ' ';
        if ( $ocultar_id )
        {
            $prefijo = '';
        }

        if ( $codigo_principal == 'referencia' && $mostrar_referencia )
        {
            $prefijo = $item->referencia . ' ';
            $referencia = '';
        }

        $unidad = $unidad_map[$item->unidad_medida1] ?? $item->unidad_medida1;

        $descripcion_item .= $talla . $referencia . $codigo_proveedor . ' (' . $unidad . ')';

        return $prefijo . $descripcion_item;
    }

    public function cargar_lista_ingredientes_fabricacion($item_platillo_id,$cantidad_fabricar)
    {
        $motivo_salida = InvMotivo::find( (int)config('inventarios.motivo_salida_id') );

        $receta_platillo = RecetaCocina::where('item_platillo_id', $item_platillo_id)->get()->first();

        if ($receta_platillo == null) {
            return '';
        }

        $ingredientes = $receta_platillo->ingredientes();
        $lineas_desarme = '';
        foreach ($ingredientes as $ingrediente) {
            $cantidad_a_sacar = $ingrediente['cantidad_porcion'] * $cantidad_fabricar;

            $costo_unitario_ingrediente = $ingrediente['ingrediente']->get_costo_promedio( 0 );

            // Una linea de salida por cada ingrediente
            $lineas_desarme .= '<tr id="' . $ingrediente['ingrediente']->id . '"> <td style="display:none;">0</td> <td class="text-center">' . $ingrediente['ingrediente']->id . '</td> <td class="nom_prod">' . $ingrediente['ingrediente']->descripcion . ' (' . $ingrediente['ingrediente']->get_unidad_medida1() . ')' . '</td> <td><span style="color:white;">' . $motivo_salida->id . '-</span><span style="color:red;">' . $motivo_salida->descripcion . '</span><input type="hidden" class="movimiento" value="' . $motivo_salida->movimiento . '"></td><td>$' . $costo_unitario_ingrediente . '</td><td class="text-center cantidad">' . $cantidad_a_sacar . ' ' . $ingrediente['ingrediente']->get_unidad_medida1() . '</td><td class="costo_total">$' . ($cantidad_a_sacar * $costo_unitario_ingrediente) . '</td><td><button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button></td></tr>';
        }
        return $lineas_desarme;
    }

    /**
     * Unificar lineas repetidas por inv_producto_id en un documento.
     */
    public function unificar_registros(Request $request, $id)
    {
        $doc_encabezado = InvDocEncabezado::findOrFail($id);

        $lineas = InvDocRegistro::where('inv_doc_encabezado_id', $doc_encabezado->id)
                    ->orderBy('id')
                    ->get();

        if ( $lineas->count() < 2 )
        {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'No hay lÃ­neas para unificar.');
        }

        $grupos = $lineas->groupBy('inv_producto_id');

        $unificados = 0;
        $eliminadas = 0;

        DB::beginTransaction();
        try {
            foreach ( $grupos as $grupo )
            {
                if ( $grupo->count() < 2 )
                {
                    continue;
                }

                $principal = $grupo->first();

                $cantidad_total = (float)$grupo->sum('cantidad');
                $costo_total = (float)$grupo->sum('costo_total');
                $costo_unitario = 0;
                if ( $cantidad_total != 0 )
                {
                    $costo_unitario = $costo_total / $cantidad_total;
                }

                $principal->cantidad = $cantidad_total;
                $principal->costo_total = $costo_total;
                $principal->costo_unitario = $costo_unitario;
                $principal->modificado_por = Auth::user()->email;
                $principal->save();

                foreach ( $grupo as $linea )
                {
                    if ( $linea->id == $principal->id )
                    {
                        continue;
                    }
                    $linea->delete();
                    $eliminadas++;
                }

                $unificados++;
            }

            DB::commit();
        } catch ( \Exception $e ) {
            DB::rollBack();
            throw $e;
        }

        return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                ->with('flash_message', 'Se unificaron ' . $unificados . ' Ã­tems. LÃ­neas eliminadas: ' . $eliminadas . '.');
    }


    public function ajustar_saldos_bodega(Request $request, $id)
    {
        try {
            $service = new AjustarSaldosBodegaService();
            $response = $service->ejecutar($id);
        } catch (\Exception $e) {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'Error al ajustar saldos: ' . $e->getMessage());
        }

        return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                ->with('flash_message', 'Proceso finalizado. Lineas agregadas: ' . $response->agregadas . '.');
    }
    private function build_variables_url(Request $request)
    {
        $id_app = $request->get('url_id', Input::get('id'));
        $id_modelo = $request->get('url_id_modelo', Input::get('id_modelo'));
        $id_transaccion = $request->get('url_id_transaccion', Input::get('id_transaccion'));

        return '?id=' . $id_app . '&id_modelo=' . $id_modelo . '&id_transaccion=' . $id_transaccion;
    }

}

