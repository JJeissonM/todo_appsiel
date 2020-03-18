<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Periodo;

use DB;

class EstudianteTieneActividadEscolar extends Model
{
	protected $table = 'sga_estudiante_tiene_actividad_escolar';

    protected $fillable = [ 'estudiante_id', 'actividad_escolar_id' ];


    public static function get_actividades_periodo_lectivo_actual( $estudiante_id, $curso_id, $asignatura_id )
    {
    	$periodo_lectivo_actual = PeriodoLectivo::get_actual();

    	$periodos = Periodo::where( 'periodo_lectivo_id', $periodo_lectivo_actual->id )->select('id')->get()->pluck('id');

        return EstudianteTieneActividadEscolar::leftJoin('sga_actividades_escolares','sga_actividades_escolares.id','=','sga_estudiante_tiene_actividad_escolar.actividad_escolar_id')
                                        ->leftJoin('sga_periodos','sga_periodos.id','=','sga_actividades_escolares.periodo_id')
                                        ->leftJoin('sga_asignaturas','sga_asignaturas.id','=','sga_actividades_escolares.asignatura_id')
                                        ->whereIn( 'sga_actividades_escolares.periodo_id', $periodos )
                                        ->where('sga_estudiante_tiene_actividad_escolar.estudiante_id', $estudiante_id )
                                        ->where('sga_actividades_escolares.estado','Activo')
                                        ->where('sga_actividades_escolares.curso_id', $curso_id)
                                        ->where('sga_asignaturas.id', $asignatura_id)
                                        ->select(
                                                'sga_actividades_escolares.id',
                                                'sga_asignaturas.descripcion AS asignatura_descripcion',
                                        		'sga_periodos.descripcion AS periodo_descripcion',
                                        		'sga_actividades_escolares.descripcion',
                                        		'sga_actividades_escolares.tematica',
                                        		'sga_actividades_escolares.fecha_entrega')
                                        ->get();

    }
}
