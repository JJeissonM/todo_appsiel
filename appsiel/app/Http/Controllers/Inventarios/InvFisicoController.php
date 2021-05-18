<?php

namespace App\Http\Controllers\Inventarios;

use Illuminate\Http\Request;
use Auth;
use DB;
use View;
use Lava;
use Input;
use Form;


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

class InvFisicoController extends TransaccionController
{

    /**
     * Show the form for creating a new resource.
     * Este método create() es llamado desde un botón-select en el index de inventarios
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tipo_tranferencia=2;

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');

        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create',$tipo_tranferencia);

        $url_form_create = 'web';

        if ( $modelo->url_form_create != '')
        {
            $url_form_create = $modelo->url_form_create;
        }

        $form_create = [
                        'url' => $url_form_create,
                        'campos' => $lista_campos
                    ];

        $motivos = InvMotivo::get_motivos_transaccion($id_transaccion);

        //dd($motivos);
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

        foreach ($doc_registros as $fila)
        {
            $existencia = InvMovimiento::get_existencia_producto($fila->producto_id, $fila->inv_bodega_id, $doc_encabezado->fecha );

            $fila->cantidad_sistema = $existencia->Cantidad;
            $fila->costo_total_sistema = $existencia->Costo;
        }

        $empresa = $this->empresa;
        $id_transaccion = $this->transaccion->id;

        $documento_vista = View::make( 'inventarios.inventario_fisico.documento_vista', compact('doc_encabezado', 'doc_registros' ) )->render();

        $miga_pan = [
                ['url'=>'inventarios?id='.Input::get('id'),'etiqueta'=>'Inventarios'],
                ['url'=>'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'),'etiqueta'=>'Inventarios físico'],
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

        foreach ($doc_registros as $fila)
        {
            $existencia = InvMovimiento::get_existencia_producto($fila->producto_id, $fila->inv_bodega_id, $doc_encabezado->fecha );
            $fila->cantidad_sistema = $existencia->Cantidad;
            $fila->costo_total_sistema = $existencia->Costo;
        }

        $empresa = $this->empresa;

        //dd($doc_encabezado);

        $documento_vista = View::make( 'inventarios.inventario_fisico.formato_estandar', compact('doc_encabezado', 'doc_registros', 'empresa' ) )->render();

        // Se prepara el PDF
        $orientacion='portrait';
        $tam_hoja = 'Letter';//array(0,0,50,800);//'A4';

        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML( $documento_vista );//->setPaper( $tam_hoja, $orientacion );

        return $pdf->stream( $doc_encabezado->documento_transaccion_descripcion.' - '.$doc_encabezado->documento_transaccion_prefijo_consecutivo.'.pdf');
    }


    public function hacer_ajuste()
    {
        $tipo_tranferencia=2;

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','create');
        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create',$tipo_tranferencia);

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
                $lista_campos[$key]['value'] = $doc_encabezado->fecha;
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
                        'campos' => $lista_campos
                    ];

        $cantidad_filas = count( $doc_registros->toarray() );
        $filas_tabla = View::make( 'inventarios.inventario_fisico.tabla_para_ajuste', compact( 'doc_registros', 'motivos' ) )->render();

        return view('inventarios.create', compact('form_create','id_transaccion','productos','servicios','motivos','miga_pan','tabla','filas_tabla','cantidad_filas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $tipo_tranferencia=2;

        $id_transaccion = Input::get('id_transaccion');

        // Se obtiene el modelo según la variable modelo_id  de la url
        $modelo = Modelo::find(Input::get('id_modelo'));

        $lista_campos = ModeloController::get_campos_modelo($modelo,'','edit');

        $cantidad_campos = count($lista_campos);

        $tipo_transaccion = TipoTransaccion::find($id_transaccion);

        $lista_campos = ModeloController::personalizar_campos($id_transaccion,$tipo_transaccion,$lista_campos,$cantidad_campos,'create',$tipo_tranferencia);

        $registro = InvDocEncabezado::get_registro_impresion( $id );

        foreach ($lista_campos as $key => $value)
        {
            if ($value['name'] == 'inv_bodega_id')
            {
                $lista_campos[$key]['value'] = $registro->inv_bodega_id;
            }

            if ($value['name'] == 'fecha')
            {
                $lista_campos[$key]['value'] = $registro->fecha;
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
            $descripcion_item = $linea->item->descripcion . ' (' . $linea->item->unidad_medida1 . ')';

            if( $linea->item->unidad_medida2 != '' )
            {
                $descripcion_item = $linea->item->descripcion . ' (' . $linea->item->unidad_medida1 . ') - Talla: ' . $linea->item->unidad_medida2;
            }

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

        //dd($motivos);
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
                                    ->select(
                                                DB::raw('CONCAT(inv_productos.descripcion, " (",inv_productos.unidad_medida1,") - Talla: ",inv_productos.unidad_medida2) AS producto_descripcion'),'inv_productos.id AS producto_id')
                                    ->get()
                                    ->toArray();

        

        $cantidad = count($productos);
        for($i=0; $i<$cantidad;$i++)
        {
            $productos[$i]['costo_unitario'] = InvCostoPromProducto::get_costo_promedio( $bodega_id, $productos[$i]['producto_id'] );
        }

        return $productos;
    }

}