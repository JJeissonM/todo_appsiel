<?php

namespace App\Http\Controllers\PaginaWeb;

use App\Http\Controllers\Controller;
use App\web\Configuraciones;
use App\web\Configuracionfuente;
use App\web\Formcontactenos;
use App\web\Fuente;
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
        $variables_url = '?id=' . Input::get('id');
        $contacts = Formcontactenos::all();
        $configuracion = Configuraciones::all()->first();
        $fuentes = Fuente::all();
        $fonts = null;
        if (count($fuentes) > 0) {
            foreach ($fuentes as $f) {
                $fonts[$f->id] = $f->font;
            }
        }
        $fontsconfig = null;
        if ($configuracion != null) {
            $fuentesya = Configuracionfuente::where('configuracion_id', $configuracion->id)->get();
            if (count($fuentesya) > 0) {
                foreach ($fuentesya as $fy) {
                    $fontsconfig[] = $fy->fuente_id;
                }
            }
        }
        return view('web.setup', compact('miga_pan', 'fonts', 'fontsconfig', 'contacts', 'variables_url', 'configuracion'));
    }
}
