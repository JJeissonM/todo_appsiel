<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomActualizacionSueldoDetalle extends Model
{
    protected $table = 'nom_actualizaciones_sueldos_detalles';

    protected $fillable = [
        'nom_actualizacion_sueldo_id',
        'nom_contrato_id',
        'salario_anterior',
        'salario_nuevo',
        'aplicado',
        'revertido'
    ];

    public function proceso()
    {
        return $this->belongsTo(NomActualizacionSueldo::class, 'nom_actualizacion_sueldo_id');
    }
}
