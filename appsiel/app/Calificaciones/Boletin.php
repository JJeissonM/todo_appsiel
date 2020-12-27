<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

class Boletin extends Model
{
    protected $table='sga_boletines';
	
	protected $fillable = ['codigo_matricula','anio','id_periodo','curso_id','id_estudiante','ciudad_colegio','puesto','observaciones'];

    /**
     * Obtenet todos las calificaciones para un Boletín.
     */
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }
}
