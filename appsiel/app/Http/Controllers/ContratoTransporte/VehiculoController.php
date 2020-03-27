<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Vehiculo;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class VehiculoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $v = Vehiculo::find($id);
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
                'etiqueta' => 'Vehículos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.vehiculos.show')
            ->with('v', $v)
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
