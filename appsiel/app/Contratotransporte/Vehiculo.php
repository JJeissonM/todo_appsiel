<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'cte_vehiculos';
    protected $fillable = ['id', 'propietario_id', 'int', 'placa', 'numero_vin', 'numero_motor', 'modelo', 'marca', 'clase', 'color', 'cilindraje', 'capacidad', 'fecha_control_kilometraje', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Vehiculo::all();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->clase.' '.$opcion->marca.' '.$opcion->modelo.' '.$opcion->placa.')';
        }

        return $vec;
    }
}
