<?php

namespace App\Http\Controllers\Inventarios;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Inventarios\ItemMandatario;

use Input;
use View;
use Auth;

use App\Sistema\Modelo;
use App\Sistema\Campo;

use App\Inventarios\InvProducto;
use App\Inventarios\Services\CodigoBarras;
use App\Inventarios\Services\TallaItem;
use App\Inventarios\EntradaAlmacen;

use App\Sistema\Html\MigaPan;

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

        $form_create = [
                            'url' => 'inv_item_mandatario',
                            'campos' => $lista_campos
                        ];

        $datos_columnas = true;

        return View::make( 'layouts.modelo_form_create_sin_botones', compact('form_create','datos_columnas') )->render();
    }

    public function store(Request $request)
    {
        $modelo_id = 317; // Item relacionado a mandatario (MandatarioTieneItem)
        $modelo = Modelo::find( $modelo_id );

        // Crear Item relacionado
        $mandatario = ItemMandatario::find( $request->mandatario_id );
        $item_relacionado_id = $this->almacenar_item_relacionado( $mandatario, $request->referencia, $request->unidad_medida2 );

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
        //dd( $json );
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

    public function almacenar_item_relacionado( $item_mandatario, $referencia, $talla_id )
    {
        $item_relacionado = new InvProducto();
        $item_relacionado->core_empresa_id = $item_mandatario->core_empresa_id;
        $item_relacionado->descripcion = $item_mandatario->descripcion;
        $item_relacionado->tipo = $item_mandatario->tipo;
        $item_relacionado->unidad_medida1 = $item_mandatario->unidad_medida1;
        $item_relacionado->inv_grupo_id = $item_mandatario->inv_grupo_id;
        $item_relacionado->impuesto_id = $item_mandatario->impuesto_id;
        $item_relacionado->precio_compra = $item_mandatario->precio_compra;
        $item_relacionado->precio_venta = $item_mandatario->precio_venta;
        $item_relacionado->estado = $item_mandatario->estado;
        $item_relacionado->creado_por = $item_mandatario->creado_por;

        $item_relacionado->referencia = $referencia;

        $talla = new TallaItem( $talla_id );
        $item_relacionado->unidad_medida2 = $talla->convertir_mayusculas();
        $item_relacionado->save();

        $item_relacionado->codigo_barras = $talla->get_talla_formateada();
        $item_relacionado->save();

        return $item_relacionado->id;
    } 

    public function get_barcode( $item_relacionado_id, $color_id, $talla_id, $referencia )
    {
        $codigo_barras = new CodigoBarras( $item_relacionado_id, $color_id, $talla_id, $referencia );
        return $codigo_barras->get_barcode();
    } 

    public function show($id)
    {
        $modelo = Modelo::find( Input::get('id_modelo') );
        $registro = ItemMandatario::find( $id );

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
        switch ( $campo )
        {
            case 'referencia':
                $item->referencia = $nuevo_valor;
                $nuevo_barcode = substr( $item->codigo_barras, 0, (int)config('codigo_barras.longitud_item') + (int)config('codigo_barras.longitud_color') + (int)config('codigo_barras.longitud_talla') ) . $nuevo_valor;
                break;

            case 'talla':
                $talla = new TallaItem( $nuevo_valor );
                $item->unidad_medida2 = $talla->convertir_mayusculas();
                $nuevo_barcode = substr( $item->codigo_barras, 0, (int)config('codigo_barras.longitud_item') + (int)config('codigo_barras.longitud_color') ) . $talla->get_talla_formateada() . $item->referencia;
                break;
            
            default:
                // code...
                break;
        }

        $item->codigo_barras = $nuevo_barcode;
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

        $vista = View::make( 'inventarios.items.etiquetas_codigos_barra', compact('items','cantidad') )->render();

        $alto = 96 * ( ceil( $cantidad / 3 ) );

        //dd( $cantidad, ceil( $cantidad / 3 ), $alto );

        //$tam_hoja = 'Letter';
        $tam_hoja = array(0, 0, 295, $alto );//array(0, 0, 612.00, 390.00);//'folio';
        $orientacion = 'Portrait';

        /*echo $vista;*/
        
        $pdf = \App::make('dompdf.wrapper');

        $pdf->loadHTML( $vista )->setPaper( $tam_hoja, $orientacion );
        return $pdf->stream();
        
    }
}