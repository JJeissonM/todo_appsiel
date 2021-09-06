<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Inventarios\Services\FiltroMovimientos;

class PruebasController extends Controller
{

    public function prueba_directa()
    {
        
        $obj = new FiltroMovimientos();

        $obj->filtro_entre_fechas( null, '2021-09-01' );
        
        $obj->filtro_por_inv_grupo_id( 1 );
        
        $obj->filtro_por_bodega_id( 5 );
        
        $obj->filtro_por_item_id( 101 );
        
        $mov_filtrado = $obj->get_movimiento_filtrado()->get();
        
        //dd($mov_filtrado);
        $items = '';
        foreach ( $mov_filtrado as $movimiento )
        {
            $items .= '<br>' . $movimiento->inv_producto_id;
        }

        echo $items;
    }

}