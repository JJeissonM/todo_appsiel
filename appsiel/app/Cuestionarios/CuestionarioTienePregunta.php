<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;

class CuestionarioTienePregunta extends Model
{
    public $timestamps = false;
	protected $table = 'sga_cuestionario_tiene_preguntas';

    protected $fillable = ['orden','cuestionario_id','pregunta_id'];

    public function cuestionario()
    {
        return $this->belongsTo('App\Cuestionarios\Cuestionario','cuestionario_id');
    }

    public function pregunta()
    {
        return $this->belongsTo('App\Cuestionarios\Pregunta','pregunta_id');
    }
}
