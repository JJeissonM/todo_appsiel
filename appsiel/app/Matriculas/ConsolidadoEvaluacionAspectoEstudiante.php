<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class ConsolidadoEvaluacionAspectoEstudiante extends Model
{
    protected $table = 'sga_consolidados_evaluacion_aspectos_estudiantes';

    // convencion_valoracion_id es el resultado del calculo, según todas las convencion_valoracion_id ingresadas en cada resultado de los items de aspectos valorados
    protected $fillable = [ 'estudiante_id', 'asignatura_id', 'convencion_valoracion_id_final', 'frecuencia', 'cantidad_dias', 'observacion', 'creado_por', 'modificado_por' ];
    
}
