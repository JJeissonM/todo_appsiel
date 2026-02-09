<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomActualizacionSueldo extends Model
{
    protected $table = 'nom_actualizaciones_sueldos';

    protected $fillable = [
        'core_empresa_id',
        'grupo_empleado_id',
        'porcentaje',
        'fecha',
        'estado',
        'observacion',
        'creado_por',
        'modificado_por'
    ];

    public function detalles()
    {
        return $this->hasMany(NomActualizacionSueldoDetalle::class, 'nom_actualizacion_sueldo_id');
    }
}
