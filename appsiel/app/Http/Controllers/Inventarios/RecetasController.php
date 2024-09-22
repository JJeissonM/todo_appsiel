<?php

namespace App\Http\Controllers\Inventarios;

use App\Http\Controllers\Sistema\ModeloController;
use App\Inventarios\InvProducto;
use Illuminate\Http\Request;

use App\Inventarios\RecetaCocina;
use Illuminate\Support\Facades\Input;

class RecetasController extends ModeloController
{
    public function agregar_ingrediente_a_receta( Request $request )
    {
        RecetaCocina::create(
            [ 'item_platillo_id' => $request->item_platillo_id ,
            'item_ingrediente_id' => $request->item_ingrediente_id ,
            'cantidad_porcion' => $request->cantidad_porcion ]
        );

        return redirect( 'web/' . $request->registro_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion )->with( 'flash_message', 'Insumo agregado correctamente.' );
    }
    
    public function get_items_contorno()
    {
        return response()->json(
            InvProducto::where([
                ['inv_grupo_id', '=', (int)config('inventarios.categoria_id_items_contorno')]
            ])->get()
            ->toArray()
        );
    }
    
    public function cambiar_cantidad_porcion( $item_platillo_id, $item_ingrediente_id, $nueva_cantidad_porcion )
    {        
        RecetaCocina::where( [
                                    'item_platillo_id' => $item_platillo_id,
                                    'item_ingrediente_id' => $item_ingrediente_id
                                    ] )
                            ->update( [ 'cantidad_porcion' => $nueva_cantidad_porcion ] );

        return 'true';        
    }
    
    public function eliminar_ingrediente( $item_platillo_id, $item_ingrediente_id )
    {
        RecetaCocina::where( [
                            'item_platillo_id' => $item_platillo_id,
                            'item_ingrediente_id' => $item_ingrediente_id
                            ] )
                    ->delete();

        $platillo = RecetaCocina::where( [
                                    'item_platillo_id' => $item_platillo_id
                                    ] )
                            ->get()->first();

        if ( $platillo == null ) {
            return redirect( 'web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') )->with( 'flash_message', 'Se retiraron todos los insumos del Producto terminado.' );
        }

        return redirect( 'web/' . $platillo->id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') )->with( 'flash_message', 'Insumo retirado.' );
    }
}