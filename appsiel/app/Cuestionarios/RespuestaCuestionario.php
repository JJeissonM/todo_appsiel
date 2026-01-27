<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;

class RespuestaCuestionario extends Model
{
    protected $table = 'sga_respuestas_cuestionarios';

    protected $fillable = ['estudiante_id','actividad_id','cuestionario_id','respuesta_enviada','calificacion','adjunto'];

    public function estudiante()
    {
        return $this->belongsTo('App\Matriculas\Estudiante','estudiante_id');
    }

    public function actividad_escolar()
    {
        return $this->belongsTo('App\Cuestionarios\ActividadEscolar','actividad_id');
    }

    public function cuestionario()
    {
        return $this->belongsTo('App\Cuestionarios\Cuestionario','cuestionario_id');
    }
}