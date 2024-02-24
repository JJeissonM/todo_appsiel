<?php

namespace App\Calificaciones\Services;

use App\AcademicoDocente\AsignacionProfesor;

use App\Calificaciones\CursoTieneAsignatura;

use Illuminate\Support\Facades\Auth;

class AsignaturasService
{
    public function get_asignaturas_del_curso_por_usuario(  $curso_id, $periodo_lectivo_id, $estado_asignaturas )
    {
        $user = Auth::user();

        $registros = CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, $periodo_lectivo_id, $estado_asignaturas );

        $asignaturas_del_usuario = AsignacionProfesor::get_asignaturas_por_usuario( $user->id, $curso_id, $periodo_lectivo_id )->pluck('id_asignatura')->toArray();

        $opciones = collect([]);
        foreach ($registros as $asignatura)
        {
            if (($user->hasRole('Profesor') || $user->hasRole('Director de grupo')) && !in_array($asignatura->id, $asignaturas_del_usuario)) {
                continue;
            }

            $opciones->push($asignatura);
        }

        return $opciones;
    }
}

