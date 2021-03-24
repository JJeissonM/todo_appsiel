<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class ConsolidadoEvaluacionAspectoEstudiante extends Model
{
    protected $table = 'sga_consolidados_evaluacion_aspectos_estudiantes';

    // convencion_valoracion_id es el resultado del calculo, según todas las convencion_valoracion_id ingresadas en cada resultado de los items de aspectos valorados
    protected $fillable = [ 'estudiante_id', 'curso_id', 'asignatura_id', 'valoracion_id_final', 'semana_calendario_id', 'observacion_id', 'creado_por', 'modificado_por' ];
    
    public function estudiante()
    {
        return $this->belongsTo('App\Matriculas\Estudiante', 'estudiante_id' );
    }
    
    public function curso()
    {
        return $this->belongsTo('App\Matriculas\Curso', 'curso_id' );
    }
    
    public function observacion()
    {
        return $this->belongsTo('App\Matriculas\CatalogoObservacionesEvaluacionAspecto', 'observacion_id' );
    }
    
    public function asignatura()
    {
        return $this->belongsTo('App\Calificaciones\Asignatura', 'asignatura_id' );
    }
    
    public function semana_calendario()
    {
    	return $this->belongsTo('App\Core\SemanasCalendario', 'semana_calendario_id' );
    }
}
