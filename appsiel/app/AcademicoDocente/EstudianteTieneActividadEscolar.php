<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

use App\Matriculas\PeriodoLectivo;
use App\Calificaciones\Periodo;

use DB;

/*
    El uso de esta clase desapareció
*/
class EstudianteTieneActividadEscolar extends Model
{
	protected $table = 'sga_estudiante_tiene_actividad_escolar';

    protected $fillable = [ 'estudiante_id', 'actividad_escolar_id' ];
}
