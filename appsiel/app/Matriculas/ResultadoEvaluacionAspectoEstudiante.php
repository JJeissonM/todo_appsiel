<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class ResultadoEvaluacionAspectoEstudiante extends Model
{
    protected $table = 'sga_resultados_evaluacion_aspectos_estudiantes';

    protected $fillable = [ 'estudiante_id', 'asignatura_id', 'item_aspecto_id', 'fecha_valoracion', 'convencion_valoracion_id', 'creado_por', 'modificado_por' ];

    public function item_aspecto()
    {
    	return $this->belongsTo(CatalogoAspecto::class, 'item_aspecto_id');
    }
}
