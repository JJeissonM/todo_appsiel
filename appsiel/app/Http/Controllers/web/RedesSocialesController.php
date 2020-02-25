<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class RedesSocialesController extends Controller
{
    public function index(){

        $miga_pan =   [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Redes Sociales'
            ]
        ];
        $variables_url = '?id='.Input::get('id');
        return view('web.redesSociales.admin',compact('miga_pan','variables_url'));
    }


    public function create(){

        $miga_pan =   [
            [
                'url' => 'pagina_web'.'?id='. Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'sociales'.'?id='. Input::get('id'),
                'etiqueta' => 'Redes Sociales'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'añadir'
            ]
        ];
        $variables_url = '?id='.Input::get('id');
        return view('web.redesSociales.create',compact('miga_pan','variables_url'));

    }

}
