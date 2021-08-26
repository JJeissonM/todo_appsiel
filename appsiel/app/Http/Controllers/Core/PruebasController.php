<?php

namespace App\Http\Controllers\Core;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Inventarios\Services\ValidacionExistencias;

class PruebasController extends Controller
{

    public function prueba_directa()
    {
        
        $obj = new ValidacionExistencias( 1, date('Y-m-d') );
        dd( $obj->lista_items_con_existencias_negativas( [ 104=>23, 105=>3, 101=>34.5 ] ) );
        //echo 'Cantidad existencia: ' . $obj->set_item( 105 )->get_existencia_item();
    }

}