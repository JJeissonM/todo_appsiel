<?php

namespace App\Http\Controllers\PaginaWeb;

use App\Http\Controllers\Controller;
use App\web\Formcontactenos;
use \Illuminate\Support\Facades\Input;


class BackEndController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        //$modelo = Modelo::where( 'modelo', 'pw_paginas'  )->get()->first();

        $miga_pan = [
            [
                'url' => 'pagina_web' . '?id=' . Input::get('id'),
                'etiqueta' => 'Web'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Setup'
            ]
        ];

        $contacts = Formcontactenos::all();
        return view('web.setup', compact('miga_pan', 'contacts'));
    }
}
