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
use App\Inventarios\InvDocumentoRelacionado;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\RecetaCocina;
use App\Inventarios\Services\AjustarSaldosBodegaService;
use App\Ventas\RestauranteCocina;
use App\Ventas\VtasMovimiento;
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
    const PERMISO_DESCONTAR_VENTAS = 'inventarios.inventario_fisico.descontar_ventas';
    const MODELO_DOCUMENTOS_INVENTARIO_ID = 25;

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de inventarios
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
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

        // Quitar las dos últimas líneas
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
        Crea un documento completo: encabezados, registros, movimiento y contabilización
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
        // Ahora mismo el campo inv_bodega_id se envía en el request, pero se debe tomar de cada línea de registro
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
        $ajustes_asociados = $this->get_ajustes_asociados($doc_encabezado->id);
        $inventario_fisico_tiene_ajuste = $ajustes_asociados->count() > 0;

        $documento_vista = View::make( 'inventarios.inventario_fisico.documento_vista', compact('doc_encabezado', 'doc_registros' ) )->render();

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Inventarios físico'],
                ['url'=>'NO','etiqueta'=> $doc_encabezado->documento_transaccion_prefijo_consecutivo ]
            ];
        
        return view( 'inventarios.inventario_fisico.show', compact( 'id', 'botones_anterior_siguiente', 'documento_vista', 'id_transaccion', 'miga_pan', 'empresa','doc_encabezado', 'inventario_fisico_tiene_ajuste', 'ajustes_asociados') );
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

        $formato_impresion_id = Input::get('formato_impresion_id');
        if ( $formato_impresion_id == '' )
        {
            $formato_impresion_id = 'estandar';
        }

        $orientacion = 'portrait';
        $vista = 'inventarios.inventario_fisico.formato_estandar';
        $datos_balance = [];

        if ( $formato_impresion_id == 'balance_inventarios' )
        {
            $orientacion = 'landscape';
            $vista = 'inventarios.inventario_fisico.balance_inventarios';
            $datos_balance = $this->preparar_datos_balance_inventario_fisico( $id );
        }

        $documento_vista = View::make( $vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'datos_balance' ) )->render();

        // Se prepara el PDF
        $tam_hoja = 'Letter';//array(0,0,50,800);//'A4';

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista )->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }

    private function preparar_datos_balance_inventario_fisico( $id )
    {
        $doc_encabezado = InvDocEncabezado::findOrFail( $id );
        $fecha = \Carbon\Carbon::parse( $doc_encabezado->fecha )->format('Y-m-d');
        $inv_bodega_id = (int)$doc_encabezado->inv_bodega_id;
        $core_empresa_id = (int)$doc_encabezado->core_empresa_id;

        $lineas = InvDocRegistro::where('inv_doc_encabezado_id', $id)
                    ->orderBy('id')
                    ->get();

        $cantidades_if = [];
        $item_ids = [];
        foreach ( $lineas as $linea )
        {
            $item_id = (int)$linea->inv_producto_id;
            if ( !isset($cantidades_if[$item_id]) )
            {
                $cantidades_if[$item_id] = 0;
                $item_ids[] = $item_id;
            }

            $cantidades_if[$item_id] += (float)$linea->cantidad;
        }

        if ( empty($item_ids) )
        {
            return [
                'fecha_desde' => $fecha,
                'fecha_hasta' => $fecha,
                'bodega' => null,
                'items' => [],
                'totales' => (object)[
                    'saldo_ini' => 0,
                    'entradas' => 0,
                    'salidas' => 0,
                    'saldo_fin' => 0,
                    'cantidad_if' => 0,
                    'diferencia' => 0
                ]
            ];
        }

        $items = InvProducto::whereIn('id', $item_ids)->get()->keyBy('id');
        $bodega = DB::table('inv_bodegas')->where('id', $inv_bodega_id)->first();

        $saldos_items = InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                            ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                            ->where('inv_doc_encabezados.fecha', '<', $fecha)
                            ->where('inv_movimientos.inv_bodega_id', $inv_bodega_id)
                            ->where('inv_movimientos.core_empresa_id', $core_empresa_id)
                            ->whereIn('inv_movimientos.inv_producto_id', $item_ids)
                            ->select(
                                'inv_productos.id AS item_id',
                                DB::raw('sum(inv_movimientos.cantidad) as cantidad_total_movimiento')
                            )
                            ->groupBy('inv_movimientos.inv_producto_id')
                            ->get()
                            ->pluck('cantidad_total_movimiento', 'item_id');

        $movimientos_entradas = InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                            ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                            ->leftJoin('inv_motivos','inv_motivos.id','=','inv_movimientos.inv_motivo_id')
                            ->where('inv_doc_encabezados.fecha', '>=', $fecha)
                            ->where('inv_doc_encabezados.fecha', '<=', $fecha)
                            ->where('inv_movimientos.inv_bodega_id', $inv_bodega_id)
                            ->where('inv_movimientos.core_empresa_id', $core_empresa_id)
                            ->where('inv_motivos.movimiento', 'entrada')
                            ->whereIn('inv_movimientos.inv_producto_id', $item_ids)
                            ->select(
                                'inv_productos.id AS item_id',
                                DB::raw('sum(inv_movimientos.cantidad) as cantidad_total_movimiento')
                            )
                            ->groupBy('inv_movimientos.inv_producto_id')
                            ->get()
                            ->pluck('cantidad_total_movimiento', 'item_id');

        $movimientos_salidas = InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
                            ->leftJoin('inv_doc_encabezados','inv_doc_encabezados.id','=','inv_movimientos.inv_doc_encabezado_id')
                            ->leftJoin('inv_motivos','inv_motivos.id','=','inv_movimientos.inv_motivo_id')
                            ->where('inv_doc_encabezados.fecha', '>=', $fecha)
                            ->where('inv_doc_encabezados.fecha', '<=', $fecha)
                            ->where('inv_movimientos.inv_bodega_id', $inv_bodega_id)
                            ->where('inv_movimientos.core_empresa_id', $core_empresa_id)
                            ->where('inv_motivos.movimiento', 'salida')
                            ->whereIn('inv_movimientos.inv_producto_id', $item_ids)
                            ->select(
                                'inv_productos.id AS item_id',
                                DB::raw('sum(inv_movimientos.cantidad) as cantidad_total_movimiento')
                            )
                            ->groupBy('inv_movimientos.inv_producto_id')
                            ->get()
                            ->pluck('cantidad_total_movimiento', 'item_id');

        $filas = [];
        $totales = (object)[
            'saldo_ini' => 0,
            'entradas' => 0,
            'salidas' => 0,
            'saldo_fin' => 0,
            'cantidad_if' => 0,
            'diferencia' => 0
        ];

        foreach ( $item_ids as $item_id )
        {
            $item = $items[$item_id] ?? null;
            if ( $item == null )
            {
                continue;
            }

            $saldo_ini = (float)($saldos_items[$item_id] ?? 0);
            $entradas = (float)($movimientos_entradas[$item_id] ?? 0);
            $salidas = (float)($movimientos_salidas[$item_id] ?? 0);
            $saldo_fin = $saldo_ini + $entradas + $salidas;
            $cantidad_if = (float)($cantidades_if[$item_id] ?? 0);
            $diferencia = $cantidad_if - $saldo_fin;

            $filas[] = (object)[
                'id' => $item->id,
                'descripcion' => $item->descripcion,
                'unidad_medida1' => $item->get_unidad_medida1(),
                'saldo_ini' => $saldo_ini,
                'entradas' => $entradas,
                'salidas' => $salidas,
                'saldo_fin' => $saldo_fin,
                'cantidad_if' => $cantidad_if,
                'diferencia' => $diferencia
            ];

            $totales->saldo_ini += $saldo_ini;
            $totales->entradas += $entradas;
            $totales->salidas += $salidas;
            $totales->saldo_fin += $saldo_fin;
            $totales->cantidad_if += $cantidad_if;
            $totales->diferencia += $diferencia;
        }

        return [
            'fecha_desde' => $fecha,
            'fecha_hasta' => $fecha,
            'bodega' => $bodega,
            'items' => $filas,
            'totales' => $totales
        ];
    }


    public function hacer_ajuste()
    {
        if ( InvDocumentoRelacionado::existe_ajuste_para_inventario_fisico((int)Input::get('doc_inv_fisico_id')) )
        {
            return redirect()->back()
                    ->with('flash_message', 'El Inventario Fisico ya tiene un ajuste relacionado. No se puede generar otro ajuste.');
        }

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
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
                $lista_campos[$key]['value'] = 'Hecho con base en Inventario Físico: '.$doc_encabezado->documento_transaccion_prefijo_consecutivo;
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
        if ( $this->tiene_ajuste_asociado($id) )
        {
            return redirect('inv_fisico/' . $id . $this->build_variables_url_from_input())
                    ->with('flash_message', 'El Inventario Fisico ya tiene un ajuste relacionado. No se puede modificar.');
        }

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
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
                                                                '<td><span style="color:white;">12-</span><span style="color:green;">Inventario Físico</span><input type="hidden" class="movimiento" value="entrada"></td>' . 
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
        if ( $this->tiene_ajuste_asociado($id) )
        {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'El Inventario Fisico ya tiene un ajuste relacionado. No se puede modificar.');
        }

        $lineas_registros = $this->preparar_array_lineas_registros( $request->movimiento );

        // Actualizar datos del encabezado
        $doc_encabezado = InvDocEncabezado::find($id);
        $doc_encabezado->fecha = $request->fecha;
        $doc_encabezado->descripcion = $request->descripcion;
        $doc_encabezado->core_tercero_id = $request->core_tercero_id;
        $doc_encabezado->modificado_por = Auth::user()->email;
        $doc_encabezado->save();

        // Borrar líneas de registros anteriores
        InvDocRegistro::where('inv_doc_encabezado_id',$doc_encabezado->id)->delete();

        // Crear nuevamente las líneas de registros
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
    
    // Parámetro enviados por GET
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
        if ( $this->tiene_ajuste_asociado($id) )
        {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'El Inventario Fisico ya tiene un ajuste relacionado. No se pueden unificar lineas.');
        }

        $doc_encabezado = InvDocEncabezado::findOrFail($id);

        $lineas = InvDocRegistro::where('inv_doc_encabezado_id', $doc_encabezado->id)
                    ->orderBy('id')
                    ->get();

        if ( $lineas->count() < 2 )
        {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'No hay líneas para unificar.');
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
                ->with('flash_message', 'Se unificaron ' . $unificados . ' ítems. Líneas eliminadas: ' . $eliminadas . '.');
    }


    public function ajustar_saldos_bodega(Request $request, $id)
    {
        if ( $this->tiene_ajuste_asociado($id) )
        {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'El Inventario Fisico ya tiene un ajuste relacionado. No se pueden ajustar saldos.');
        }

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

    public function descontar_ventas(Request $request, $id)
    {
        if ( !Auth::user()->can(self::PERMISO_DESCONTAR_VENTAS) )
        {
            abort(403);
        }

        if ( $this->tiene_ajuste_asociado($id) )
        {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'El Inventario Fisico ya tiene un ajuste relacionado. No se pueden descontar ventas nuevamente.');
        }

        try {
            $response = $this->crear_ajuste_descontando_ventas($id);
        } catch (\Exception $e) {
            return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                    ->with('flash_message', 'Error al descontar ventas: ' . $e->getMessage());
        }

        return redirect('inv_fisico/' . $id . $this->build_variables_url($request))
                ->with('flash_message', 'Proceso finalizado. AI generado: ' . $response->documento . '. Ingredientes descontados: ' . $response->ingredientes . '.');
    }

    private function crear_ajuste_descontando_ventas($inv_fisico_id)
    {
        $doc_inv_fisico = InvDocEncabezado::findOrFail($inv_fisico_id);
        $bodega_if_id = (int)$doc_inv_fisico->inv_bodega_id;
        $bodega_principal_id = (int)config('inventarios.item_bodega_principal_id');

        if ( $bodega_if_id == 0 )
        {
            throw new \Exception('El inventario fisico no tiene bodega asociada.');
        }

        if ( $bodega_principal_id == 0 )
        {
            throw new \Exception('No esta configurada la bodega principal de inventarios.');
        }

        $ai_tipo_transaccion_id = (int)config('inventarios.ai_tipo_transaccion_id');
        $ai_tipo_doc_app_id = (int)config('inventarios.ai_tipo_doc_app_id');
        $ai_motivo_entrada_id = (int)config('inventarios.ai_motivo_entrada_id');
        $ai_motivo_salida_id = (int)config('inventarios.ai_motivo_salida_id');
        $ai_tercero_id = (int)config('inventarios.ai_tercero_id');

        $parametros_faltantes = [];
        if ( $ai_tipo_transaccion_id == 0 )
        {
            $parametros_faltantes[] = 'ai_tipo_transaccion_id (Tipo de transaccion para crear el Ajuste de Inventarios AI)';
        }

        if ( $ai_tipo_doc_app_id == 0 )
        {
            $parametros_faltantes[] = 'ai_tipo_doc_app_id (Tipo de documento que usara el AI)';
        }

        if ( $ai_motivo_entrada_id == 0 )
        {
            $parametros_faltantes[] = 'ai_motivo_entrada_id (Motivo de entrada para registrar ingreso a la bodega principal)';
        }

        if ( $ai_motivo_salida_id == 0 )
        {
            $parametros_faltantes[] = 'ai_motivo_salida_id (Motivo de salida para registrar descuento de la bodega del IF)';
        }

        if ( $ai_tercero_id == 0 )
        {
            $parametros_faltantes[] = 'ai_tercero_id (Tercero que quedara asociado al AI)';
        }

        if ( !empty($parametros_faltantes) )
        {
            throw new \Exception('Faltan parametros de configuracion para crear ajustes de inventario: ' . implode('; ', $parametros_faltantes) . '. Configuracion: Configuracion > Inventarios > Parametros por defecto creacion Ajustes de Inventarios.');
        }

        $cocina = RestauranteCocina::where('bodega_default_id', $bodega_if_id)
                    ->where('estado', 'Activo')
                    ->first();

        if ( $cocina == null )
        {
            $bodega_descripcion = DB::table('inv_bodegas')->where('id', $bodega_if_id)->value('descripcion');
            throw new \Exception('No existe una cocina activa asociada a la bodega del inventario fisico. Bodega: ' . $bodega_if_id . ' - ' . $bodega_descripcion . '.');
        }

        $grupo_inventario = InvGrupo::find((int)$cocina->grupo_inventarios_id);
        $grupo_descripcion = $grupo_inventario != null ? $grupo_inventario->descripcion : '';
        $bodega_descripcion = DB::table('inv_bodegas')->where('id', $bodega_if_id)->value('descripcion');
        $parametros_consulta = ' Fecha: ' . $doc_inv_fisico->fecha .
            '. Bodega IF: ' . $bodega_if_id . ' - ' . $bodega_descripcion .
            '. Cocina: ' . $cocina->label .
            '. Grupo inventario: ' . (int)$cocina->grupo_inventarios_id . ' - ' . $grupo_descripcion . '.';

        $lineas_if = InvDocRegistro::where('inv_doc_encabezado_id', $doc_inv_fisico->id)
                        ->orderBy('id')
                        ->get()
                        ->unique('inv_producto_id')
                        ->values();

        if ( $lineas_if->count() == 0 )
        {
            throw new \Exception('El inventario fisico no tiene lineas de registro.');
        }

        $ingredientes_ids = $lineas_if->pluck('inv_producto_id')->unique()->values()->all();

        $ventas_platillos = VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
                            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->where('vtas_movimientos.fecha', $doc_inv_fisico->fecha)
                            ->where('inv_productos.inv_grupo_id', (int)$cocina->grupo_inventarios_id)
                            ->where('vtas_movimientos.estado', '<>', 'Anulado')
                            ->select(
                                'vtas_movimientos.inv_producto_id',
                                DB::raw('SUM(vtas_movimientos.cantidad) AS cantidad')
                            )
                            ->groupBy('vtas_movimientos.inv_producto_id')
                            ->get()
                            ->pluck('cantidad', 'inv_producto_id');

        if ( $ventas_platillos->count() == 0 )
        {
            throw new \Exception('No se encontraron ventas para los parametros consultados.' . $parametros_consulta);
        }

        $recetas = RecetaCocina::whereIn('item_ingrediente_id', $ingredientes_ids)
                    ->whereIn('item_platillo_id', $ventas_platillos->keys()->all())
                    ->get();

        if ( $recetas->count() == 0 )
        {
            throw new \Exception('No se encontraron recetas que relacionen los ingredientes del IF con los platillos vendidos.');
        }

        $consumos_por_ingrediente = [];
        foreach ( $recetas as $receta )
        {
            $cantidad_vendida = (float)$ventas_platillos[$receta->item_platillo_id];
            $cantidad_consumida = $cantidad_vendida * (float)$receta->cantidad_porcion;

            if ( $cantidad_consumida <= 0 )
            {
                continue;
            }

            if ( !isset($consumos_por_ingrediente[$receta->item_ingrediente_id]) )
            {
                $consumos_por_ingrediente[$receta->item_ingrediente_id] = 0;
            }

            $consumos_por_ingrediente[$receta->item_ingrediente_id] += $cantidad_consumida;
        }

        if ( count($consumos_por_ingrediente) == 0 )
        {
            throw new \Exception('Las recetas encontradas no generaron consumos positivos.');
        }

        $items = InvProducto::whereIn('id', array_keys($consumos_por_ingrediente))->get()->keyBy('id');
        $lineas_por_ingrediente = $lineas_if->keyBy('inv_producto_id');
        $lineas_ajuste = [];

        foreach ( $consumos_por_ingrediente as $ingrediente_id => $cantidad_consumida )
        {
            $item = $items[$ingrediente_id] ?? null;
            if ( $item == null )
            {
                continue;
            }

            $linea_if = $lineas_por_ingrediente[$ingrediente_id] ?? null;
            $costo_unitario = 0;
            if ( $linea_if != null && (float)$linea_if->costo_unitario > 0 )
            {
                $costo_unitario = (float)$linea_if->costo_unitario;
            }else{
                $costo_unitario = (float)$item->get_costo_promedio($bodega_if_id);
            }

            $costo_total = $cantidad_consumida * $costo_unitario;

            $lineas_ajuste[] = (object)[
                'inv_motivo_id' => $ai_motivo_entrada_id,
                'inv_bodega_id' => $bodega_principal_id,
                'inv_producto_id' => (int)$ingrediente_id,
                'costo_unitario' => $costo_unitario,
                'cantidad' => $cantidad_consumida,
                'costo_total' => $costo_total
            ];

            $lineas_ajuste[] = (object)[
                'inv_motivo_id' => $ai_motivo_salida_id,
                'inv_bodega_id' => $bodega_if_id,
                'inv_producto_id' => (int)$ingrediente_id,
                'costo_unitario' => $costo_unitario,
                'cantidad' => $cantidad_consumida,
                'costo_total' => $costo_total
            ];
        }

        if ( count($lineas_ajuste) == 0 )
        {
            throw new \Exception('No fue posible preparar lineas para el ajuste de inventario.');
        }

        $request_ajuste = new Request([
            'core_empresa_id' => Auth::user()->empresa_id,
            'core_tipo_transaccion_id' => $ai_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $ai_tipo_doc_app_id,
            'fecha' => $doc_inv_fisico->fecha,
            'inv_bodega_id' => $bodega_if_id,
            'core_tercero_id' => $ai_tercero_id,
            'codigo_referencia_tercero' => '',
            'documento_soporte' => 'IF ' . $doc_inv_fisico->id,
            'descripcion' => 'Descuento de ventas con base en Inventario Fisico ID ' . $doc_inv_fisico->id,
            'estado' => 'Activo',
            'creado_por' => Auth::user()->email,
            'modificado_por' => Auth::user()->email,
            'hora_inicio' => '00:00:00',
            'hora_finalizacion' => '00:00:00',
            'vtas_doc_encabezado_origen_id' => 0,
            'bodega_destino_id' => 0
        ]);

        DB::beginTransaction();
        try {
            $doc_ajuste_id = InventarioController::crear_documento($request_ajuste, $lineas_ajuste, self::MODELO_DOCUMENTOS_INVENTARIO_ID);
            InvDocumentoRelacionado::firstOrCreate(
                [
                    'inv_doc_encabezado_origen_id' => (int)$doc_inv_fisico->id,
                    'inv_doc_encabezado_relacionado_id' => $doc_ajuste_id,
                    'tipo_relacion' => InvDocumentoRelacionado::TIPO_IF_AJUSTE
                ],
                [
                    'creado_por' => Auth::user()->email,
                    'modificado_por' => Auth::user()->email
                ]
            );
            DB::commit();
        } catch ( \Exception $e ) {
            DB::rollBack();
            throw $e;
        }

        $doc_ajuste = InvDocEncabezado::find($doc_ajuste_id);

        $documento_ajuste = $doc_ajuste_id;
        if ( $doc_ajuste != null && $doc_ajuste->tipo_documento_app != null )
        {
            $documento_ajuste = $doc_ajuste->tipo_documento_app->prefijo . ' ' . $doc_ajuste->consecutivo;
        }

        return (object)[
            'id' => $doc_ajuste_id,
            'documento' => $documento_ajuste,
            'ingredientes' => count($consumos_por_ingrediente)
        ];
    }

    private function tiene_ajuste_asociado($inv_fisico_id)
    {
        return InvDocumentoRelacionado::existe_ajuste_para_inventario_fisico($inv_fisico_id);
    }

    private function get_ajustes_asociados($inv_fisico_id)
    {
        return InvDocumentoRelacionado::where('inv_doc_encabezado_origen_id', (int)$inv_fisico_id)
                ->where('tipo_relacion', InvDocumentoRelacionado::TIPO_IF_AJUSTE)
                ->with('documento_relacionado.tipo_documento_app')
                ->get();
    }

    private function build_variables_url(Request $request)
    {
        $id_app = $request->get('url_id', Input::get('id'));
        $id_modelo = $request->get('url_id_modelo', Input::get('id_modelo'));
        $id_transaccion = $request->get('url_id_transaccion', Input::get('id_transaccion'));

        return '?id=' . $id_app . '&id_modelo=' . $id_modelo . '&id_transaccion=' . $id_transaccion;
    }

    private function build_variables_url_from_input()
    {
        return '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion');
    }

}
