<?php

namespace App\Http\Controllers\PaginaWeb;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;


class NavegacionController extends Controller
{
    const navegacionPordefault = 1;
    public function create(){

        $miga_pan = self::migapan();

        return view('web.navegacion.navegacion',compact('miga_pan'));

    }

    public function migapan() {
       return [
           [
               'url' => 'pagina_web'.'?id='. Input::get('id'),
               'etiqueta' => 'Web'
           ],
           [
               'url' => 'NO',
               'etiqueta' => 'Navegación'
           ]
       ];
    }

    public function store(Request $request){

    }

}
