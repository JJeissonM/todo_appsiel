<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TerceroNoProveedor extends Tercero
{
    protected $table = 'core_terceros';

    public static function opciones_campo_select()
    {
        $opciones = Tercero::leftJoin('compras_proveedores','compras_proveedores.core_tercero_id','=','core_terceros.id')
                        ->whereNull('compras_proveedores.core_tercero_id')
                        ->where('core_terceros.core_empresa_id',Auth::user()->empresa_id)
                        ->select('core_terceros.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                        ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->numero_identificacion.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
