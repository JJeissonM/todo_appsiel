<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

/*
	El Control disciplinario permite llevar un registro semanal de códigos de comportamiento (positivos o negativos)
	en los que haya incurrido el estudiante.
*/
class ControlDisciplinario extends Model
{
    protected $table = 'sga_control_disciplinario';

    protected $fillable = ['estudiante_id', 'semana_id', 'curso_id', 'asignatura_id', 'codigo_1_id', 'codigo_2_id', 'codigo_3_id', 'observacion_adicional', 'creado_por', 'modificado_por', 'estado'];

    public static function get_un_estudiante( $estudiante_id )
    {
    	return ControlDisciplinario::leftJoin('sga_semanas_calendario','sga_semanas_calendario.id','=','sga_control_disciplinario.semana_id')
    					->where('sga_control_disciplinario.estudiante_id',$estudiante_id)
    					->orderBy('sga_semanas_calendario.fecha_inicio','ASC')
    					->groupBy('sga_control_disciplinario.semana_id')
    					->get();
    }
}
