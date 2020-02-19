<?php

namespace App\Http\Controllers\PaginaWeb;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;

use App\Sistema\Modelo;

class BackEndController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
    	$modelo = Modelo::where( 'modelo', 'pw_paginas'  )->get()->first();

        return redirect( 'web?id='.Input::get('id').'&id_modelo='.$modelo->id );
    }

}
