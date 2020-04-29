<?php

namespace App\Http\Controllers\ContratoTransporte;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class ContratoTransporteController extends Controller
{
    public function index()
    {
        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Contratos Transporte']
        ];

        return view('contratos_transporte.index', compact('miga_pan'));
    }

    //crear contrato
    public function create()
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = [
            [
                'url' => 'contratos_transporte' . '?id=' . $idapp,
                'etiqueta' => 'Contratos transporte'
            ],
            [
                'url' => 'web?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Contratos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Crear Contrato'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.contratos.create')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan);
    }
}
