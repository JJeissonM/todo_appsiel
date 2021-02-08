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
                        ->select(
                                    'core_terceros.id',
                                    'core_terceros.descripcion',
                                    'core_terceros.nombre1',
                                    'core_terceros.otros_nombres',
                                    'core_terceros.apellido1',
                                    'core_terceros.apellido2',
                                    'core_terceros.razon_social',
                                    'core_terceros.numero_identificacion'
                                )
                        ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $nombre_completo = $opcion->descripcion;

            if ( $nombre_completo == '' )
            {
                $nombre_completo = $opcion->razon_social;
            }

            if ( $nombre_completo == '' )
            {
                $nombre_completo = $opcion->apellido1 . ' ' . $opcion->apellido2 . ' ' . $opcion->nombre1 . ' ' . $opcion->otros_nombres;
            }

            $vec[$opcion->id] = $opcion->numero_identificacion . ' ' . $nombre_completo;
        }

        return $vec;
    }
}
