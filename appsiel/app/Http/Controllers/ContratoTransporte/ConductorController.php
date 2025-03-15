<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Conductor;
use App\Contratotransporte\Documentosconductor;
use App\Contratotransporte\Vehiculo;
use App\Contratotransporte\Vehiculoconductor;
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
                        'tipo' => 'USADO EN RUTA ASIGNADA (' . $p->planillac->contrato->origen . " - " . $p->planillac->contrato->destino . ") EN LA FECHA: " . $p->planillac->contrato->fecha_inicio,
                        'id' => 0 //0 para vehiculo por ruta, 1 para vehiculo asociado
                    ];
                }
            }
        }
        //recogemos todos los vehículos asociados a la persona
        $vehiculoConductor = $v->vehiculoconductors;
        if (count($vehiculoConductor) > 0) {
            foreach ($vehiculoConductor as $vc) {
                $vehiculos[] = [
                    'placa' => $vc->vehiculo->placa,
                    'interno' => $vc->vehiculo->int,
                    'modelo' => $vc->vehiculo->modelo,
                    'marca' => $vc->vehiculo->marca,
                    'clase' => $vc->vehiculo->clase,
                    'tipo' => 'VEHÍCULO ASOCIADO (Funcionalidad: Contratos transporte \ Conductores \ Ver)',
                    'id' => 1 //0 para vehiculo por ruta, 1 para vehiculo asociado
                ];
            }
        }
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
        $c = Conductor::find($id);
        $variables_url = "?id=" .  Input::get('id') . "&id_modelo=" . Input::get('id_modelo');
        //validamos documentosconductors, planillaconductors, vehiculoconductors
        if (count($c->documentosconductors) > 0 || count($c->planillaconductors) > 0 || count($c->vehiculoconductors) > 0) {
            return redirect("web" . $variables_url)->with('mensaje_error', 'El conductor tiene datos asociados y no se puede eliminar, revise: documentos del conductor, planillas generadas al conductor y vehículos asociados.');
        }
        if ($c->delete()) {
            return redirect("web" . $variables_url)->with('flash_message', 'Eliminado con exito');
        } else {
            return redirect("web" . $variables_url)->with('mensaje_error', 'No pudo ser eliminado');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function vehiculos($id)
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $c = Conductor::find($id);
        $vehiculos = $c->vehiculoconductors;
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
                'etiqueta' => 'Vehículos del Conductor'
            ]
        ];
        $todosVehiculos = null;
        $vehi = Vehiculo::all();
        if (count($vehi) > 0) {
            foreach ($vehi as $v) {
                $todosVehiculos[$v->id] = "PLACA " . $v->placa . ", MOVIL INTERNO " . $v->int . ", CAPACIDAD " . $v->capacidad;
            }
        }
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.conductores.vehiculos')
            ->with('c', $c)
            ->with('vehiculos', $vehiculos)
            ->with('todosVehiculos', $todosVehiculos)
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan);
    }

    //asocia un vehiculo a un conductor
    public function vehiculoStore(Request $request)
    {
        $vc = new Vehiculoconductor($request->all());
        if ($vc->save()) {
            return redirect("cte_conductores/" . $request->conductor_id . "/vehiculos" . $request->variables_url)->with('flash_message', 'Vehículo asociado con exito');
        } else {
            return redirect("cte_conductores/" . $request->conductor_id . "/vehiculos" . $request->variables_url)->with('mensaje_error', 'No pudo ser asociado');
        }
    }

    //retira un vehiculo de un conductor
    public function vehiculoDelete($id)
    {
        $vc = Vehiculoconductor::find($id);
        $conductor = $vc->conductor_id;
        $variables_url = "?id=" .  Input::get('id') . "&id_modelo=" . Input::get('id_modelo') . "&id_transaccion=" . Input::get('id_transaccion');
        if ($vc->delete()) {
            return redirect("cte_conductores/" . $conductor . "/vehiculos" . $variables_url)->with('flash_message', 'Retirado asociado con exito');
        } else {
            return redirect("cte_conductores/" . $conductor . "/vehiculos" . $variables_url)->with('mensaje_error', 'No pudo ser retirado');
        }
    }
}
