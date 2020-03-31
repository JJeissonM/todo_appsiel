<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Numeraltabla;
use App\Contratotransporte\Plantilla;
use App\Contratotransporte\Plantillaarticulo;
use App\Contratotransporte\Plantillaarticulonumeral;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class PlantillaController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_numeraltabla($id)
    {
        $v = Numeraltabla::find($id);
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
                'etiqueta' => 'Numerales tabla de plantilla'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver Numeral de Tabla'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.plantilla.show_numeraltabla')
            ->with('v', $v)
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_plantillaarticulonumeral($id)
    {
        $v = Plantillaarticulonumeral::find($id);
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
                'etiqueta' => 'Numerales de artículos de plantillas'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver Numeral de Artículo'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.plantilla.show_plantillaarticulonumeral')
            ->with('v', $v)
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_plantillaarticulo($id)
    {
        $v = Plantillaarticulo::find($id);
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
                'etiqueta' => 'Artículos de la plantilla'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver Artículo'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.plantilla.show_plantillaarticulo')
            ->with('v', $v)
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_plantilla($id)
    {
        $v = Plantilla::find($id);
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
                'etiqueta' => 'plantillas'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver Plantilla'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.plantilla.show_plantilla')
            ->with('v', $v)
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan);
    }
}
