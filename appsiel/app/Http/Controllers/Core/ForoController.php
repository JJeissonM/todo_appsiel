<?php

namespace App\Http\Controllers\Core;

use App\Calificaciones\Asignatura;
use App\Core\Foro;
use App\Core\Fororespuesta;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Matriculas\Curso;
use App\Matriculas\PeriodoLectivo;
use App\Sistema\Aplicacion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class ForoController extends Controller
{
    //index
    public function index($c, $a, $p)
    {
        $id = Input::get('id');
        $appModel = Aplicacion::find($id);
        $foros = Foro::where([['periodo_id', $p], ['curso_id', $c], ['asignatura_id', $a]])->get();
        $miga_pan = [
            ['url' => $appModel->app . '?id=' . $id, 'etiqueta' => $appModel->descripcion],
            ['url' => 'NO', 'etiqueta' => 'Foros de Discusión']
        ];
        $periodo = PeriodoLectivo::find($p);
        $curso = Curso::find($c);
        $materia = Asignatura::find($a);
        if (count($foros) > 0) {
            foreach ($foros as $fo) {
                $fo->url = url('') . "/foros/" . $fo->curso_id . "/" . $fo->asignatura_id . "/" . $fo->periodo_id . "/inicio/" . $id . "/ver/" . $fo->id . "/foro?id=" . $id;
            }
        }
        return view('foros.index', compact('miga_pan', 'periodo', 'materia', 'curso', 'foros', 'id'));
    }

    //crea foros
    public function store(Request $request)
    {
        $f = new Foro($request->all());
        $f->user_id = Auth::user()->id;
        if ($f->save()) {
            return redirect("foros/" . $request->curso_id . "/" . $request->asignatura_id . "/" . $request->periodo_id . "/inicio?id=" . $request->app)->with('flash_message', 'Foro guardado con exito');
        } else {
            return redirect("foros/" . $request->curso_id . "/" . $request->asignatura_id . "/" . $request->periodo_id . "/inicio?id=" . $request->app)->with('mensaje_error', 'El foro no pudo ser guardado');
        }
    }

    //participar del foro
    public function show($c, $a, $p, $app, $f)
    {
        $appModel = Aplicacion::find($app);
        $miga_pan = [
            ['url' => $appModel->app . '?id=' . $app, 'etiqueta' => $appModel->descripcion],
            ['url' => 'foros/' . $c . '/' . $a . '/' . $p . '/inicio?id=' . $app, 'etiqueta' => 'Foros de Discusión'],
            ['url' => 'NO', 'etiqueta' => 'Foro']
        ];
        $foro = Foro::find($f);
        $respuestas = $foro->fororespuestas;
        $periodo = PeriodoLectivo::find($p);
        $curso = Curso::find($c);
        $materia = Asignatura::find($a);
        return view('foros.show', compact('miga_pan', 'periodo', 'materia', 'curso', 'foro', 'app', 'respuestas'));
    }

    //guardar respuesta
    public function guardarrespuesta(Request $request)
    {
        $f = new Fororespuesta($request->all());
        $f->user_id = Auth::user()->id;
        if ($f->save()) {
            return redirect("foros/" . $request->curso_id . "/" . $request->asignatura_id . "/" . $request->periodo_id . "/inicio/" . $request->app . "/ver/" . $request->foro_id . "/foro?id=" . $request->app)->with('flash_message', 'Respuesta guardada con exito');
        } else {
            return redirect("foros/" . $request->curso_id . "/" . $request->asignatura_id . "/" . $request->periodo_id . "/inicio/" . $request->app . "/ver/" . $request->foro_id . "/foro?id=" . $request->app)->with('mensaje_error', 'La respuesta no pudo ser guardada');
        }
    }
}
