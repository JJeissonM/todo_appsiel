<?php

namespace App\Http\Controllers\web;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

use App\AcademicoDocente\PlanClaseEncabezado;
use App\Matriculas\Curso;
use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Asignatura;
use App\Calificaciones\CursoTieneAsignatura;

class GuiasAcademicasController extends Controller
{
    public function guias_planes_clases( $curso_id, $asignatura_id )
    {
        $planes = PlanClaseEncabezado::consultar_guias_estudiantes( $curso_id, $asignatura_id );

        $curso = Curso::find($curso_id);
        
        $asignatura = Asignatura::find($asignatura_id);

        return view('web.guias_academicas.guias_planes_clases',compact('planes', 'asignatura', 'curso'));
    }
    

    // LLenar select dependiente
    public function get_select_asignaturas( $curso_id, $periodo_id = null)
    {
        if ( is_null($periodo_id) or $periodo_id == 'null')
        {
            $periodo_lectivo = PeriodoLectivo::get_actual();
        }else{
            $periodo_lectivo = PeriodoLectivo::get_segun_periodo( $periodo_id );
        }

        $asignaturas = CursoTieneAsignatura::asignaturas_del_curso( $curso_id, null, $periodo_lectivo->id );

        $opciones = '<option value="">Seleccionar...</option>';
        foreach ($asignaturas as $campo) {
            $opciones .= '<option value="'.$campo->id.'">'.$campo->descripcion.'</option>';
        }
        return $opciones;
    }

}
