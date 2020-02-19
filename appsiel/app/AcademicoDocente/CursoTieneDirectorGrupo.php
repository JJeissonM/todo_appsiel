<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use DB;

class CursoTieneDirectorGrupo extends Model
{
	protected $table = 'sga_curso_tiene_director_grupo';

    protected $fillable = ['orden','curso_id','user_id'];
}
