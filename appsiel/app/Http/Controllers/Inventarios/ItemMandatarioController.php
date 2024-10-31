<?php

namespace App\Http\Controllers\Inventarios;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Inventarios\ItemMandatario;

use App\Sistema\Modelo;
use App\Sistema\Campo;

use App\Inventarios\InvProducto;
use App\Inventarios\Services\CodigoBarras;
use App\Inventarios\Services\TallaItem;
use App\Inventarios\EntradaAlmacen;
use App\Inventarios\InvCostoPromProducto;
use App\Inventarios\MandatarioTieneItem;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\Services\PricesServices;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class ItemMandatarioController extends ModeloController
{
    public function create()
    {
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'create' );
        //dd( $this->modelo );
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            switch ( $lista_campos[$i]['name'] )
            {
                case 'modelo_entidad_id':
                    $lista_campos[$i]['value'] = $this->modelo->id;
                    //dd( $this->modelo->id );
                case 'mandatario_id':
                    $lista_campos[$i]['value'] = Input::get('mandatario_id');
                    break;
                default:
                    # code...
                    break;
            }
        }

        array_push($lista_campos, [
            "id" => 999,
            "descripcion" => "Modelo ID",
            "tipo" => "hidden",
            "name" => "model_id",
            "opciones" => "",
            "value" => $this->modelo->id,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        $form_create = [
                            'url' => 'inv_item_mandatario',
                            'campos' => $lista_campos
                        ];

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_create_sin_botones', compact('form_create','datos_columnas') )->render();
    }

    // Crear Item relacionado
    public function store(Request $request)
    {
        $modelo = Modelo::find( $request->model_id );

        $mandatario = ItemMandatario::find( $request->mandatario_id );
        $item_relacionado_id = $this->almacenar_item_relacionado( $mandatario, $mandatario->referencia, $request ); // InvProducto        
        
        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $nuevo_precio_venta = 0;
            if ($request->precio_venta != null) {
                $nuevo_precio_venta = $request->precio_venta;
            }

            $data = [
                'lista_precios_id' => (int)config('ventas.lista_precios_id'),
                'inv_producto_id' => $item_relacionado_id,
                'fecha_activacion' => date('Y-m-d'),
                'precio' => $nuevo_precio_venta
            ];
            (new PricesServices())->create_item_price( $data );
        }

        // Crear Relacion: Mandatario tiene Item
        $record_created = app( $modelo->name_space )->create( [ 'mandatario_id' => $request->mandatario_id, 'item_id' => $item_relacionado_id ] );
        
        // Crear Entrada de Almacen
        if ( $request->cantidad != '' )
        {
            $entrada = new EntradaAlmacen;
            // modelo ID 248 = Entradas de almacén
            $entrada->crear_nueva( 248, $this->preparar_datos_entrada_almacen( $item_relacionado_id, $mandatario->precio_compra, $request->cantidad ) );
        }

        $json = json_decode('{"talla":"' . $request->unidad_medida2 . '","referencia":"' . $request->referencia . '","cantidad":"' . $request->cantidad . '"}');
        
        return response()->json( $json );
    }

    public function edit( $id )
    {
        $lista_campos = ModeloController::get_campos_modelo( $this->modelo, '', 'edit' );

        array_push($lista_campos, [
            "id" => 999,
            "descripcion" => "Modelo ID",
            "tipo" => "hidden",
            "name" => "model_id",
            "opciones" => "",
            "value" => $this->modelo->id,
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);
        

        $registro = app($this->modelo->name_space)->find($id)->item_relacionado;

        
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++)
        {
            if($lista_campos[$i]['name'] == 'precio_compra')
            {
                $costo_prom = InvCostoPromProducto::where([
                    ['inv_producto_id', '=', $registro->id]
                ])->count();

                if($costo_prom > 0)
                {
                    $lista_campos[$i]['atributos'] = ["disabled" => "disabled","style" => "background-color:#FBFBFB;"];
                }
            }
        }

        $form_create = [
                            'url' => 'inv_item_mandatario/' . $id,
                            'campos' => $lista_campos
                        ];

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_edit_sin_botones', compact('form_create','datos_columnas', 'registro') )->render();
    }

    /**
     * Para los items relacionados (InvProducto)
     */
    public function update(Request $request, $id)
    {
        // Se obtiene el modelo según la variable modelo_id de la url
        $modelo = Modelo::find($request->model_id);

        // Se obtinene el registro a modificar del modelo
        
        
        // $registro es una instacia de MandatarioTieneItem
        $registro = app($modelo->name_space)->find($id);
        
        $item_relacionado = $registro->item_relacionado;

        $datos = $request->all();
        
        if ( $request->codigo_barras == null || $request->codigo_barras == '' ) {
            $datos['codigo_barras'] = (new CodigoBarras($item_relacionado->id, 0, 0, 0))->barcode;
        }        

        $item_relacionado->fill( $datos );

        if ( $request->unidad_medida2 != null ) {

            // $request->unidad_medida2 almacena la Talla
            $item_relacionado->referencia = $registro->item_mandatario->referencia . '-' . strtoupper($request->unidad_medida2);

            $talla = new TallaItem( $request->unidad_medida2 );
            $item_relacionado->unidad_medida2 = $talla->convertir_mayusculas();
            $item_relacionado->codigo_barras = 99;
        }
        
        $item_relacionado->save();

        if (config('ventas.agregar_precio_a_lista_desde_create_item'))
        {
            $datos['fecha_activacion'] = date('Y-m-d');
            $datos['inv_producto_id'] = $item_relacionado->id;
            $datos['lista_precios_id'] = (int)config('ventas.lista_precios_id');
            
            (new PricesServices())->create_or_update_item_price( $datos );
        }

        $json = json_decode('{"talla":"' . $request->unidad_medida2 . '","referencia":"' . $request->referencia . '","cantidad":"' . $request->cantidad . '"}');
        
        return response()->json( $json );
    }

    public function preparar_datos_entrada_almacen( $item_relacionado_id, $costo_unitario, $cantidad )
    {
        $parametros = config('inventarios');

        return [
                'core_empresa_id' => Auth::user()->empresa_id,
                'core_tipo_transaccion_id' => (int)$parametros['ea_tipo_transaccion_id'],
                'core_tipo_doc_app_id' => (int)$parametros['ea_tipo_doc_app_id'],
                'fecha' => date('Y-m-d'),
                'inv_bodega_id' => (int)$parametros['item_bodega_principal_id'],
                'consecutivo' => '',
                'core_tercero_id' => (int)$parametros['core_tercero_id'],
                'lineas_registros' => '[{"inv_motivo_id":"' . (int)$parametros['ea_motivo_id'] . '","inv_bodega_id":"' . (int)$parametros['item_bodega_principal_id'] . '","inv_producto_id":"' . $item_relacionado_id . '","costo_unitario":"' . $costo_unitario . '","cantidad":"' . $cantidad . '","costo_total":"' . $costo_unitario * $cantidad . '"}]',
                'estado' => 'Activo'
            ];
    }

    public function almacenar_item_relacionado( $item_mandatario, $mandatario_referencia, $request )
    {
        $item_relacionado = new InvProducto();
        $item_relacionado->core_empresa_id = $item_mandatario->core_empresa_id;
        $item_relacionado->descripcion = $item_mandatario->descripcion;
        $item_relacionado->tipo = 'producto';

        $unidad_medida1 = 'UND';
        if ( $item_mandatario->unidad_medida1 != null) {
            $unidad_medida1 = $item_mandatario->unidad_medida1;
        }
        $item_relacionado->unidad_medida1 = $unidad_medida1;

        $item_relacionado->inv_grupo_id = $item_mandatario->inv_grupo_id;
        
        $impuesto_id = 1;
        if ( $item_mandatario->impuesto_id != null) {
            $impuesto_id = $item_mandatario->impuesto_id;
        }
        $item_relacionado->impuesto_id = $impuesto_id;

        $item_relacionado->codigo_barras = $item_mandatario->codigo_barras;

        $item_relacionado->precio_compra = 100;
        if ( $request->precio_compra != null ) {
            $item_relacionado->precio_compra = $request->precio_compra;
        }

        $item_relacionado->precio_venta = 200;
        if ( $request->precio_venta != null ) {
            $item_relacionado->precio_venta = $request->precio_venta;
        }
        
        if ( $request->categoria_id != null ) {
            $item_relacionado->categoria_id = $request->categoria_id;
        }
        
        $item_relacionado->estado = 'Activo';
        $item_relacionado->creado_por = $item_mandatario->creado_por;
        $item_relacionado->save(); // Para obtener el ID
        
        if ( $request->codigo_barras == null || $request->codigo_barras == '' ) {
            $item_relacionado->codigo_barras = (new CodigoBarras($item_relacionado->id, 0, 0, 0))->barcode;
        }

        // $request->unidad_medida2 almacena la Talla
        if ( $request->unidad_medida2 != null ) {
            $item_relacionado->referencia = $mandatario_referencia . '-' . strtoupper( $request->unidad_medida2 );

            $talla = new TallaItem( $request->unidad_medida2 );
            $item_relacionado->unidad_medida2 = $talla->convertir_mayusculas();
            $item_relacionado->codigo_barras = 99;
        }

        $item_relacionado->save();

        return $item_relacionado->id;
    } 

    public function get_barcode( $item_relacionado_id, $color_id, $talla_id, $referencia )
    {
        $codigo_barras = new CodigoBarras( $item_relacionado_id, $color_id, $talla_id, $referencia );

        return $codigo_barras->get_barcode( $item_relacionado_id );
    } 

    public function show($id)
    {
        $modelo = Modelo::find( Input::get('id_modelo') );
        $registro = app($modelo->name_space)->find( $id );

        $reg_anterior = app( $modelo->name_space )->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app( $modelo->name_space )->where('id', '>', $registro->id)->min('id');

        // Se obtienen los campos asociados a ese modelo
        $lista_campos = $modelo->campos()->orderBy('orden')->get()->toArray();

        // Formatear-asignar el valor correspondiente del registro del modelo
        
        // 1ro. Para los campos del modelo
        $lista_campos = Campo::asignar_valores_registro( $lista_campos, $registro );

        $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
        $acciones = $this->acciones_basicas_modelo( $modelo, $variables_url );

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;

        $form_create = [
                        'url' => $acciones->store,
                        'campos' => $lista_campos
                    ];

        $miga_pan = $this->get_miga_pan($modelo, $registro->descripcion );

        $tabla = '';

        return view( 'inventarios.items.show', compact('form_create','miga_pan','registro','url_crear','url_edit','reg_anterior','reg_siguiente','tabla') );
    }

    public function update_item_relacionado( $campo, $item_id, $nuevo_valor )
    {
        $item = InvProducto::find( $item_id );
        
        $referencia =  explode('-',$item->referencia);
        $talla_id =  $item->unidad_medida2;

        switch ( $campo )
        {
            case 'referencia':
                $referencia = $nuevo_valor;
                //$item->referencia = $nuevo_valor;
                break;

            case 'talla':
                /*
                $talla_id = $nuevo_valor;
                $talla = new TallaItem( $talla_id );
                $item->unidad_medida2 = $talla->convertir_mayusculas();
                */
                
                $item->unidad_medida2 = $nuevo_valor;

                $referencia = $referencia[0] . '-' . $nuevo_valor;
                $item->referencia = $referencia;
                break;
            
            default:
                // code...
                break;
        }

        $item->codigo_barras = 99;//$this->get_barcode( $item_id, '000', $talla_id, $referencia );
        $item->save();
    }

    public function etiquetas_codigos_barra( $mandatario_id, $item_id, $cantidad )
    {
        $item_bodega_principal_id = (int)config( 'inventarios.item_bodega_principal_id' );
        $items = [];

        if ( (int)$mandatario_id == 0 )
        {
            $item = InvProducto::where( 'id', $item_id )->get()->first();
            $items = collect([]);
            for ($i=0; $i < $cantidad; $i++)
            {
                $items->push( $item );
            }
        }else{
            $item_mandatario = ItemMandatario::find( (int)$mandatario_id );
            $items_relacionados = $item_mandatario->items_relacionados;
            $items = collect([]);
            foreach ( $items_relacionados as $item )
            {
                $cantidad = $item->get_existencia_actual( $item_bodega_principal_id, date('Y-m-d') );
                for ($i=0; $i < $cantidad; $i++)
                {
                    $items->push( $item );
                }
            }
            $cantidad = 25; // Aprox. 10 filas de tres stickers por hoja
        }

        if ($cantidad <= 3 )
        {
            $cantidad = 4;
        }

        $vista = View::make( 'inventarios.items.etiquetas_codigos_barra', compact('items','cantidad') )->render();

        $alto = 96 * ( ceil( $cantidad / 3 ) );

        if ( (int)config('inventarios.ancho_hoja_impresion') != 0 )
        {
            $alto = (int)config('inventarios.ancho_hoja_impresion');
        }

        //$tam_hoja = 'Letter';
        $tam_hoja = array(0, 0, (int)config('inventarios.ancho_hoja_impresion'), $alto );//array(0, 0, 612.00, 390.00);//'folio';
        $orientacion = 'Portrait';

        /*echo $vista;*/
        
        $pdf = App::make('dompdf.wrapper');

        $pdf->loadHTML( $vista )->setPaper( $tam_hoja, $orientacion );
        return $pdf->stream();
        
    }

    public function delete_item_relacionado( $registro_mandatario_tiene_item_id )
    {
        $registro_mandatario_tiene_item = MandatarioTieneItem::find( $registro_mandatario_tiene_item_id );
        
        $item_relacionado = $registro_mandatario_tiene_item->item_relacionado;

        $inv_producto = new InvProducto();

        $validacion = $inv_producto->validar_eliminacion( $registro_mandatario_tiene_item->item_id, false );

        if ( $validacion == 'Ítem tiene registros de Precios relacionados.' ) {
            // Paso todas las validaciones
            $reg_precios_actuales = ListaPrecioDetalle::where([
                ['lista_precios_id', '=', (int)config('ventas.lista_precios_id')],
                ['inv_producto_id', '=', $registro_mandatario_tiene_item->item_id]
            ])
            ->get();

            foreach ($reg_precios_actuales as $reg_detalle_precio)
            {
                $reg_detalle_precio->delete();
            }

            $registro_mandatario_tiene_item->delete();

            $item_relacionado->delete();

            $validacion = 'ok';
        }

        if ( $validacion == 'Ítem tiene ítems mandatarios relacionados.') {
            
            $registro_mandatario_tiene_item->delete();

            $item_relacionado->delete();

            $validacion = 'ok';
        }        

        return $validacion;
    }
}