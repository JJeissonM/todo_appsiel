<?php

namespace App\Http\Controllers\Inventarios;

use App\Http\Controllers\Sistema\ModeloController;

use Illuminate\Http\Request;

use App\Inventarios\RecetaCocina;

class RecetasController extends ModeloController
{
    public function agregar_ingrediente_a_receta( Request $request )
    {
        RecetaCocina::create(
            [ 'item_platillo_id' => $request->item_platillo_id ,
            'item_ingrediente_id' => $request->item_ingrediente_id ,
            'cantidad_porcion' => $request->cantidad_porcion ]
        );

        return redirect( 'web/' . $request->registro_id . '?id=' . $request->url_id . '&id_modelo=' . $request->url_id_modelo . '&id_transaccion=' . $request->url_id_transaccion )->with( 'flash_message', 'Ingrediente agregado correctamente.' );
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
}