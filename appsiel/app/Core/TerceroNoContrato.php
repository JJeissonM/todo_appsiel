<?php

namespace App\Core;

use Illuminate\Support\Facades\Auth;

class TerceroNoContrato extends Tercero
{
    protected $table = 'core_terceros';

    public static function opciones_campo_select()
    {
        $opciones = Tercero::leftJoin('nom_contratos','nom_contratos.core_tercero_id','=','core_terceros.id')
                        ->where('core_terceros.core_empresa_id',Auth::user()->empresa_id)
                        ->select(
                                    'core_terceros.id',
                                    'core_terceros.descripcion',
                                    'core_terceros.nombre1',
                                    'core_terceros.otros_nombres',
                                    'core_terceros.apellido1',
                                    'core_terceros.apellido2',
                                    'core_terceros.razon_social',
                                    'core_terceros.numero_identificacion',
                                    'nom_contratos.estado'
                                )
                        ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            if ($opcion->estado == 'Activo' )
            {
                continue;
            }
            
            $vec[$opcion->id] = $opcion->numero_identificacion . ' ' . $opcion->get_label_to_show();
        }

        return $vec;
    }
}
