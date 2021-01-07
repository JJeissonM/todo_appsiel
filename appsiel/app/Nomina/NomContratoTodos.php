<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomContratoTodos extends Model
{
    protected $table = 'nom_contratos';

    public static function opciones_campo_select()
    {
        $opciones = NomContratoTodos::leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                                ->select('core_terceros.id','core_terceros.descripcion','core_terceros.numero_identificacion')
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
