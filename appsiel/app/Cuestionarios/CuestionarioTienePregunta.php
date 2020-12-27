<?php

namespace App\Cuestionarios;

use Illuminate\Database\Eloquent\Model;

use DB;

class CuestionarioTienePregunta extends Model
{
	protected $table = 'sga_cuestionario_tiene_preguntas';

    protected $fillable = ['orden','cuestionario_id','pregunta_id'];
}
