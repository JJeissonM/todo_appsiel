<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\web\Icon;

class IconsController extends Controller
{
    //muestra la lista de iconos de la base de datos
    public function view()
    {
        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Íconos'
            ]
        ];
        $iconos = Icon::all();
        return view('web.icons.view')
            ->with('miga_pan', $miga_pan)
            ->with('iconos', $iconos);
    }
}
