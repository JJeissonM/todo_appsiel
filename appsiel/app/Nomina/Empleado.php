<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\NomContrato;

class Empleado extends NomContrato
{
    protected $table = 'nom_contratos';
}
