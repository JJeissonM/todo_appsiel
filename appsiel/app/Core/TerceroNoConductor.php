<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TerceroNoConductor extends Tercero
{
    protected $table = 'core_terceros';

    public static function opciones_campo_select()
    {
        $opciones = Tercero::leftJoin('cte_conductors','cte_conductors.tercero_id','=','core_terceros.id')
                        ->whereNull('cte_conductors.tercero_id')
                        ->where('core_terceros.core_empresa_id',Auth::user()->empresa_id)
                        ->select('core_terceros.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                        ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->numero_identificacion . ')';
        }

        return $vec;
    }
}
