<?php

namespace App\Http\Controllers\ContratoTransporte;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;


class ContratoTransporteController extends Controller
{
    public function index()
    {
        $miga_pan = [
                ['url'=>'NO','etiqueta'=>'Contratos Transporte']
            ];

        return view( 'contratos_transporte.index', compact( 'miga_pan' ) );
    }

}