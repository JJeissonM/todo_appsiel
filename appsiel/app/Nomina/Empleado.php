<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use App\Nomina\NomContrato;

class Empleado extends NomContrato
{
    protected $table = 'nom_contratos';

    public static function opciones_campo_select()
    {
        $opciones = NomContrato::leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')->where('nom_contratos.estado','Activo')
                    ->select('nom_contratos.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                    ->orderby('core_terceros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->numero_identificacion . ')';
        }

        return $vec;
    }
}
