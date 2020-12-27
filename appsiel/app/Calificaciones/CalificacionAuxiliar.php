<?php

namespace App\Calificaciones;

use App\Boletin;
use Illuminate\Database\Eloquent\Model;

use DB;

class CalificacionAuxiliar extends Model
{
    protected $table = 'sga_calificaciones_auxiliares';

	protected $fillable = ['codigo_matricula','id_colegio','anio','id_periodo','curso_id','id_estudiante','id_asignatura','C1','C2','C3','C4','C5','C6','C7','C8','C9','C10','C11','C12','C13','C14','C15','creado_por','modificado_por'];

	public static function get_todas_un_estudiante_periodo( $estudiante_id, $periodo_id )
	{
		return CalificacionAuxiliar::leftJoin('sga_asignaturas', 'sga_asignaturas.id', '=', 'sga_calificaciones_auxiliares.id_asignatura')
					            ->where('sga_calificaciones_auxiliares.id_estudiante' , $estudiante_id)
					            ->where('sga_calificaciones_auxiliares.id_periodo' , $periodo_id)
					            ->select(
					            			'sga_asignaturas.descripcion AS asignatura',
					            			'sga_asignaturas.id AS asignatura_id',
					            			'sga_calificaciones_auxiliares.C1',
					            			'sga_calificaciones_auxiliares.C2',
					            			'sga_calificaciones_auxiliares.C3',
					            			'sga_calificaciones_auxiliares.C4',
					            			'sga_calificaciones_auxiliares.C5',
					            			'sga_calificaciones_auxiliares.C6',
					            			'sga_calificaciones_auxiliares.C7',
					            			'sga_calificaciones_auxiliares.C8',
					            			'sga_calificaciones_auxiliares.C9',
					            			'sga_calificaciones_auxiliares.C10',
					            			'sga_calificaciones_auxiliares.C11',
					            			'sga_calificaciones_auxiliares.C12',
					            			'sga_calificaciones_auxiliares.C13',
					            			'sga_calificaciones_auxiliares.C14',
					            			'sga_calificaciones_auxiliares.C15')
					            ->get();
	}

	public function get_registro_vacio()
	{
		return $this->fill(['id'=>0,'codigo_matricula'=>0,'id_colegio'=>0,'anio'=>0,'id_periodo'=>0,'curso_id'=>0,'id_estudiante'=>0,'id_asignatura'=>0,'C1'=>0,'C2'=>0,'C3'=>0,'C4'=>0,'C5'=>0,'C6'=>0,'C7'=>0,'C8'=>0,'C9'=>0,'C10'=>0,'C11'=>0,'C12'=>0,'C13'=>0,'C14'=>0,'C15'=>0,'creado_por'=>0,'modificado_por'=>0]);
	}
}
