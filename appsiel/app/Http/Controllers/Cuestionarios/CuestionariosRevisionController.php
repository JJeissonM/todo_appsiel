<?php

namespace App\Http\Controllers\Cuestionarios;

use App\Cuestionarios\Cuestionario;
use App\Cuestionarios\CuestionarioTienePregunta;
use App\Cuestionarios\Pregunta;
use App\Http\Controllers\Controller;
use App\Sistema\Aplicacion;
use App\Sistema\Html\MigaPan;
use App\Sistema\Modelo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class CuestionariosRevisionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasRole('Profesor') && ! $user->hasRole('Director de grupo')) {
            abort(403);
        }

        $cuestionarios = Cuestionario::with('preguntas')->orderBy('created_at', 'DESC')->get();


        $miga_pan = MigaPan::get_array( Aplicacion::find(Input::get('id')), Modelo::find(Input::get('id_modelo')), 'Listado');

        /*$miga_pan = [
            ['url' => 'web?id=' . $request->query('id'), 'etiqueta' => 'Cuestionarios'],
            ['url' => 'NO', 'etiqueta' => 'Revisión general']
        ];*/

        return view('calificaciones.actividades_escolares.cuestionarios_todos', compact('cuestionarios', 'miga_pan'));
    }

    public function duplicar(Request $request, $cuestionarioId)
    {
        $user = Auth::user();

        if (! $user->hasRole('Profesor') && ! $user->hasRole('Director de grupo')) {
            abort(403);
        }

        $original = Cuestionario::findOrFail($cuestionarioId);

        $nuevaVersion = $original->replicate();
        $nuevaVersion->created_by = $user->id;
        $nuevaVersion->descripcion = $original->descripcion . ' (copia)';
        $nuevaVersion->created_at = Carbon::now();
        $nuevaVersion->updated_at = Carbon::now();
        $nuevaVersion->save();

        $asignaciones = CuestionarioTienePregunta::where('cuestionario_id', $original->id)->orderBy('orden')->get();

        foreach ($asignaciones as $asignacion) {
            $pregunta = Pregunta::find($asignacion->pregunta_id);
            if (is_null($pregunta)) {
                continue;
            }

            $nuevaPregunta = $pregunta->replicate();
            $nuevaPregunta->created_by = $user->id;
            $nuevaPregunta->created_at = Carbon::now();
            $nuevaPregunta->updated_at = Carbon::now();
            $nuevaPregunta->save();

            CuestionarioTienePregunta::create([
                'orden' => $asignacion->orden,
                'cuestionario_id' => $nuevaVersion->id,
                'pregunta_id' => $nuevaPregunta->id,
            ]);
        }

        $query = ['id' => $request->query('id')];
        if ($request->has('id_modelo')) {
            $query['id_modelo'] = $request->query('id_modelo');
        }

        return redirect()->route('cuestionarios.revision', $query)->with('flash_message', 'Cuestionario duplicado correctamente.');
    }

    public function preview($cuestionarioId)
    {
        $user = Auth::user();

        if (! $user->hasRole('Profesor') && ! $user->hasRole('Director de grupo')) {
            abort(403);
        }

        $cuestionario = Cuestionario::with(['preguntas' => function ($q) {
            $q->orderBy('orden');
        }])->findOrFail($cuestionarioId);

        return view('calificaciones.actividades_escolares.cuestionarios_preview', compact('cuestionario'));
    }
}
