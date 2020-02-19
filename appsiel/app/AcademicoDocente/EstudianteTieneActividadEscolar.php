<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use DB;

class EstudianteTieneActividadEscolar extends Model
{
	protected $table = 'sga_estudiante_tiene_actividad_escolar';

    protected $fillable = [ 'estudiante_id', 'actividad_escolar_id' ];
}
