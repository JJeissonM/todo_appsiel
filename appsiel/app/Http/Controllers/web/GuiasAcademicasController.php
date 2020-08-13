<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

use App\AcademicoDocente\PlanClaseEncabezado;
use App\Matriculas\Curso;
use App\Calificaciones\Asignatura;

class GuiasAcademicasController extends Controller
{
    public function guias_planes_clases( $curso_id, $asignatura_id )
    {
        $planes = PlanClaseEncabezado::consultar_guias_estudiantes( $curso_id, $asignatura_id );

        $curso = Curso::find($curso_id);
        
        $asignatura = Asignatura::find($asignatura_id);

        return view('web.guias_academicas.guias_planes_clases',compact('planes', 'asignatura', 'curso'));
    }

}
