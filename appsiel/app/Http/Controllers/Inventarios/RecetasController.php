<?php

namespace App\Http\Controllers\Inventarios;

use App\Http\Controllers\Sistema\ModeloController;
use App\Inventarios\InvProducto;
use Illuminate\Http\Request;

use App\Inventarios\RecetaCocina;

class RecetasController extends ModeloController
{
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
}