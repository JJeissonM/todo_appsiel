<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Conductor;
use App\Contratotransporte\Documentosconductor;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class ConductorController extends Controller
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
        $v = Conductor::find($id);
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
                'etiqueta' => 'Conductores'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver'
            ]
        ];
        //recogemos todos los vehículos conducidos por la persona, histórico de planillas
        $planillas = $v->planillaconductors;
        $vehiculos = null;
        if (count($planillas) > 0) {
            foreach ($planillas as $p) {
                $vehiculo = $p->planillac->contrato->vehiculo;
                if ($vehiculo != null) {

                    $vehiculos[] = [
                        'placa' => $vehiculo->placa,
                        'interno' => $vehiculo->int,
                        'modelo' => $vehiculo->modelo,
                        'marca' => $vehiculo->marca,
                        'clase' => $vehiculo->clase,
                        'tipo' => 'USADO EN RUTA ASIGNADA, RUTA: ' . $p->planillac->contrato->origen . " - " . $p->planillac->contrato->destino . " - FECHA: " . $p->planillac->contrato->fecha_inicio,
                        'id' => 0 //0 para vehiculo por ruta, 1 para vehiculo asociado
                    ];
                }
            }
        }
        //recogemos todos los vehículos asociados a la persona
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.conductores.show')
            ->with('v', $v)
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('vehiculos', $vehiculos);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showDocuments($id)
    {
        $v = Documentosconductor::find($id);
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
                'etiqueta' => 'Documentos del conductor'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver Documentos'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.conductores.showDocuments')
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
