<?php

namespace App\Http\Controllers\Salud;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Salud\Agenda;
use App\Salud\Consultorio;
use Input;

class CitasController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $app = Input::get('id');
        $modelo = Input::get('id_modelo');
        $miga_pan = [
            ['url' => 'consultorio_medico?id=' . $app, 'etiqueta' => 'Consultorio Médico'],
            ['url' => 'NO', 'etiqueta' => 'Menú Agenda Citas']
        ];
        $variables_url = "?id=" . $app . "&id_modelo=" . $modelo;
        return view('consultorio_medico.citas.menu', compact('miga_pan', 'variables_url'));
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
        $a = new Agenda($request->all());
        if ($a->save()) {
            return redirect("citas_medicas/HOY" . $request->variables_url)->with('flash_message', 'Entrada de agenda guardada con exito');
        } else {
            return redirect("citas_medicas/HOY" . $request->variables_url)->with('mensaje_error', 'La entrada no se pudo guardar');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($fecha)
    {
        $agendas = null;
        if ($fecha == 'HOY') {
            $hoy = getdate();
            $fecha = $hoy["year"] . "-" . $hoy["mon"] . "-" . $hoy["mday"];
        }
        $agendas = Agenda::where('fecha', $fecha)->get();
        if ($agendas != null) {
            if (count($agendas) > 0) {
                foreach ($agendas as $a) {
                    $a->hora_inicio = date("g:i A", strtotime($a->hora_inicio));
                    $a->hora_fin = date("g:i A", strtotime($a->hora_fin));
                }
            }
        }
        $app = Input::get('id');
        $modelo = Input::get('id_modelo');
        $variables_url = "?id=" . $app . "&id_modelo=" . $modelo;
        $miga_pan = [
            ['url' => 'consultorio_medico?id=' . $app, 'etiqueta' => 'Consultorio Médico'],
            ['url' => 'citas_medicas' . $variables_url, 'etiqueta' => 'Menú Agenda Citas'],
            ['url' => 'NO', 'etiqueta' => 'Agenda de Citas']
        ];
        if ($agendas != null) {
            if (count($agendas) == 0) {
                $agendas = null;
            }
        }
        $cons = Consultorio::all();
        $consultorios = null;
        if (count($cons) > 0) {
            foreach ($cons as $c) {
                $consultorios[$c->id] = $c->descripcion . " - SEDE: " . $c->sede;
            }
        }
        return view('consultorio_medico.citas.agenda', compact('consultorios', 'miga_pan', 'variables_url', 'agendas', 'fecha', 'app', 'modelo'));
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
        $a = Agenda::find($id);
        $app = Input::get('id');
        $modelo = Input::get('id_modelo');
        $variables_url = "?id=" . $app . "&id_modelo=" . $modelo;
        if ($a->delete()) {
            return redirect("citas_medicas/HOY" . $variables_url)->with('flash_message', 'Entrada de agenda eliminada con exito');
        } else {
            return redirect("citas_medicas/HOY" . $variables_url)->with('mensaje_error', 'La entrada no se pudo eliminar');
        }
    }
}
