<?php

namespace App\Http\Controllers\Salud;

use App\Core\Tercero;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Salud\Agenda;
use App\Salud\Citamedica;
use App\Salud\Consultorio;
use App\Salud\Paciente;
use App\Salud\ProfesionalSalud;
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


    /*
    Gestion de citas
    */
    public function citas()
    {
        $app = Input::get('id');
        $modelo = Input::get('id_modelo');
        $fecha = Input::get('fecha');
        $data = null;
        if ($fecha == 'HOY') {
            $hoy = getdate();
            $fecha = $hoy["year"] . "-" . $hoy["mon"] . "-" . $hoy["mday"];
        }
        $cons = Consultorio::all();
        $consultorios = null;
        if (count($cons) > 0) {
            foreach ($cons as $c) {
                $consultorios[$c->id] = $c->descripcion . " - " . $c->sede;
            }
        }
        $citas = null;
        if ($consultorios != null) {
            foreach ($consultorios as $key => $value) {
                $cit = Citamedica::where([['fecha', $fecha], ['consultorio_id', $key]])->get();
                if (count($cit) > 0) {
                    foreach ($cit as $ci) {
                        $t1 = Tercero::find($ci->profesional->core_tercero_id);
                        $t1p = "---";
                        if ($t1 != null) {
                            $t1p = $t1->nombre1 . " " . $t1->otros_nombres . " " . $t1->apellido1 . " " . $t1->apellido2 . " [[" . $ci->profesional->especialidad . "]]";
                        }
                        $t2p = "---";
                        $t2 = Tercero::find($ci->paciente->core_tercero_id);
                        if ($t2 != null) {
                            $t2p = $t2->nombre1 . " " . $t2->otros_nombres . " " . $t2->apellido1 . " " . $t2->apellido2;
                        }
                        $citas[$value][] = [
                            'consultorio_id' => $key,
                            'hora_inicio' => date("g:i A", strtotime($ci->hora_inicio)),
                            'hora_fin' => date("g:i A", strtotime($ci->hora_fin)),
                            'estado' => $ci->estado,
                            'profesional' => $t1p,
                            'paciente' => $t2p,
                            'cita_id' => $ci->id
                        ];
                    }
                } else {
                    $citas[$value] = [];
                }
            }
        }
        $estados = [
            "PENDIENTE" => "PENDIENTE",
            "CANCELADA" => "CANCELADA",
            "FINALIZADA" => "FINALIZADA",
            "RECHAZADA" => "RECHAZADA",
            "NO ASISTIO" => "NO ASISTIO"
        ];
        $variables_url = "?id=" . $app . "&id_modelo=" . $modelo;
        $miga_pan = [
            ['url' => 'consultorio_medico?id=' . $app, 'etiqueta' => 'Consultorio Médico'],
            ['url' => 'citas_medicas' . $variables_url, 'etiqueta' => 'Menú Agenda Citas'],
            ['url' => 'NO', 'etiqueta' => 'Gestión de Citas']
        ];
        $profesionales = null;
        $pacientes = null;
        $prof = ProfesionalSalud::all();
        $paci = Paciente::all();
        if (count($prof) > 0) {
            foreach ($prof as $p) {
                $t = Tercero::find($p->core_tercero_id);
                if ($t != null) {
                    $profesionales[$p->id] = $t->nombre1 . " " . $t->otros_nombres . " " . $t->apellido1 . " " . $t->apellido2 . " [[" . $p->especialidad . "]]";
                }
            }
        }
        if (count($paci) > 0) {
            foreach ($paci as $p) {
                $t = Tercero::find($p->core_tercero_id);
                if ($t != null) {
                    $pacientes[$p->id] = $t->nombre1 . " " . $t->otros_nombres . " " . $t->apellido1 . " " . $t->apellido2;
                }
            }
        }
        return view('consultorio_medico.citas.citas', compact('estados', 'consultorios', 'miga_pan', 'variables_url', 'citas', 'fecha', 'app', 'modelo', 'profesionales', 'pacientes'));
    }

    /*
    Eliminar cita
    */
    public function citas_delete($id)
    {
        $c = Citamedica::find($id);
        $app = Input::get('id');
        $modelo = Input::get('id_modelo');
        $fecha = Input::get('fecha');
        $variables_url = "?id=" . $app . "&id_modelo=" . $modelo . "&fecha=" . $fecha;
        if ($c->delete()) {
            return redirect("citas_medicas/agenda/citas" . $variables_url)->with('flash_message', 'Cita eliminada con exito');
        } else {
            return redirect("citas_medicas/agenda/citas" . $variables_url)->with('mensaje_error', 'La cita no se pudo eliminar');
        }
    }

    // cambiar estado de cita
    public function citas_estado($id, $estado)
    {
        $c = Citamedica::find($id);
        $response = [
            'icon' => 'error',
            'title' => 'Error desconocido',
            'text' => 'Ha ocurrido un error desconocido!'
        ];
        $c->estado = $estado;
        if ($c->save()) {
            $response = [
                'icon' => 'success',
                'title' => 'Información',
                'text' => 'El estado fue cambiado con exito, recargue la página para ver los cambios'
            ];
        } else {
            $response = [
                'icon' => 'error',
                'title' => 'Atención',
                'text' => 'El estado no pudo ser cambiado'
            ];
        }
        return json_encode($response);
    }

    //una fecha dentro de un rango
    /* Función */
    function check_in_range($fecha_inicio, $fecha_fin, $fecha)
    {
        $fecha_inicio = strtotime($fecha_inicio);
        $fecha_fin = strtotime($fecha_fin);
        $fecha = strtotime($fecha);
        if (($fecha >= $fecha_inicio) && ($fecha <= $fecha_fin)) {
            return true;
        } else {
            return false;
        }
    }

    // valida disponibilidad para la cita
    public function citas_verificar($fecha, $hi, $hf, $con, $pro)
    {
        //verificamos que el horario esté disponible en agenda para el consultorio
        $agenda = Agenda::where([['consultorio_id', $con], ['fecha', $fecha]])->get();
        if (count($agenda) > 0) {
            $disponible = "NO";
            foreach ($agenda as $a) {
                if ($this->check_in_range($a->hora_inicio, $a->hora_fin, $hi)) {
                    if ($this->check_in_range($a->hora_inicio, $a->hora_fin, $hf)) {
                        $disponible = "SI";
                        break;
                    }
                }
            }
            if ($disponible == 'SI') {
                //verificamos que el horario esté disponible en cita para el consultorio
                $citas = Citamedica::where([['consultorio_id', $con], ['fecha', $fecha], ['estado', '<>', 'RECHAZADA']])->get();
                if (count($citas) > 0) {
                    $ocupada = "NO";
                    foreach ($citas as $c) {
                        if ($this->check_in_range($c->hora_inicio, $c->hora_fin, $hi)) {
                            $ocupada = 'SI';
                        } elseif ($this->check_in_range($c->hora_inicio, $c->hora_fin, $hf)) {
                            $ocupada = "SI";
                        }
                    }
                    if ($ocupada == 'NO') {
                        //no hay citas en el horario
                        //verificamos que el profesional tenga disponibilidad en cita
                        $citas2 = Citamedica::where([['fecha', $fecha], ['estado', 'PENDIENTE'], ['profesional_id', $pro]])->get();
                        if (count($citas2) > 0) {
                            $ocupado = "NO";
                            foreach ($citas2 as $c2) {
                                if ($this->check_in_range($c2->hora_inicio, $c2->hora_fin, $hi)) {
                                    $ocupado = 'SI';
                                } elseif ($this->check_in_range($c2->hora_inicio, $c2->hora_fin, $hf)) {
                                    $ocupado = "SI";
                                }
                            }
                            if ($ocupado == 'NO') {
                                //puede crear la cita
                                return $this->notificacion('success', 'Información', 'El horario indicado está disponible, ¡Puede crear la cita!', 'NO', 'SI');
                            } else {
                                //ocupado
                                $html = $this->htmlString($agenda, 'HORARIO PROGRAMADO CONSULTORIO');
                                $html = $html . $this->htmlString($citas, 'HORARIO OCUPADO CONSULTORIO');
                                $html = $html . $this->htmlString($citas2, 'HORARIO OCUPADO PROFESIONAL DE LA SALUD');
                                return $this->notificacion('error', 'Información', 'El horario indicado está fuera del horario disponible!', $html, 'NO');
                            }
                        }
                    } else {
                        //ocupada
                        $html = $this->htmlString($agenda, 'HORARIO PROGRAMADO CONSULTORIO');
                        $html = $html . $this->htmlString($citas, 'HORARIO OCUPADO CONSULTORIO');
                        return $this->notificacion('error', 'Información', 'El horario indicado está fuera del horario disponible!', $html, 'NO');
                    }
                } else {
                    //no hay citas en el horario, puede crear la cita
                    return $this->notificacion('success', 'Información', 'El horario indicado está disponible, ¡Puede crear la cita!', 'NO', 'SI');
                }
            } else {
                $html = $this->htmlString($agenda, 'HORARIO PROGRAMADO CONSULTORIO');
                return $this->notificacion('error', 'Información', 'El horario indicado está fuera del horario disponible!', $html, 'NO');
            }
        } else {
            return $this->notificacion('error', 'Información', 'No hay disponibilidad para la fecha indicada!', 'NO', 'NO');
        }



        $c = Citamedica::find($id);
        $response = [
            'icon' => 'error',
            'title' => 'Error desconocido',
            'text' => 'Ha ocurrido un error desconocido!'
        ];
        $c->estado = $estado;
        if ($c->save()) {
            $response = [
                'icon' => 'success',
                'title' => 'Información',
                'text' => 'El estado fue cambiado con exito, recargue la página para ver los cambios'
            ];
        } else {
            $response = [
                'icon' => 'error',
                'title' => 'Atención',
                'text' => 'El estado no pudo ser cambiado'
            ];
        }
        return json_encode($response);
    }


    //devuelve una respuesta de tipo notificación
    public function notificacion($icon, $title, $text, $html, $disponibilidad)
    {
        return json_encode([
            'icon' => $icon,
            'title' => $title,
            'text' => $text,
            'html' => $html,
            'disponibilidad' => $disponibilidad
        ]);
    }

    //construye un html
    public function htmlString($data, $title)
    {
        $html = "<h4>" . $title . "</h4><ol>";
        foreach ($data as $a) {
            $html = $html . "<li>" . date("g:i A", strtotime($a->hora_inicio)) . " - " . date("g:i A", strtotime($a->hora_fin)) . "</li>";
        }
        $html = $html . "</ol>";
        return $html;
    }

    //guarda una cita
    public function store_cita(Request $request)
    {
        $c = new Citamedica($request->all());
        if ($c->save()) {
            return redirect("citas_medicas/agenda/citas" . $request->variables_url . "&fecha=" . $request->fecha)->with('flash_message', 'Cita creada con exito');
        } else {
            return redirect("citas_medicas/agenda/citas" . $request->variables_url . "&fecha=" . $request->fecha)->with('mensaje_error', 'La cita no se pudo crear');
        }
    }
}
