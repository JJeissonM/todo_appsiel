<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\web\Footer;

class FooterController extends Controller
{

    public function index(){

        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Pie de página'
            ]
        ];

        $variables_url = '?id=' . Input::get('id');
        $footer = Footer::all()->first();
        return view('web.footer.footer',compact('footer','variables_url','miga_pan'));
    }

}
