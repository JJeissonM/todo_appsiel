<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'cte_vehiculos';
    protected $fillable = ['id', 'int', 'placa', 'numero_vin', 'numero_motor', 'modelo', 'marca', 'clase', 'color', 'cilindraje', 'capacidad', 'fecha_control_kilometraje', 'propietario_id', 'created_at', 'updated_at'];
}
