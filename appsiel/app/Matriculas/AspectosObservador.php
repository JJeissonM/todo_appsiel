<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class AspectosObservador extends Model
{
    protected $table = 'sga_aspectos_observador';

    protected $fillable = ['id_estudiante','id_aspecto','fecha_valoracion',
    						'valoracion_periodo1','valoracion_periodo2',
    						'valoracion_periodo3','valoracion_periodo4'];

    
}
