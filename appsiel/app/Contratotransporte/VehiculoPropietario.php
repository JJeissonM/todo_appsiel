<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehiculoPropietario extends Vehiculo
{   
    public $urls_acciones = '{"show":"cte_vehiculos/id_fila/show"}';
}
