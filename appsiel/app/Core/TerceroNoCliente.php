<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Matriculas\Estudiante;

class TerceroNoCliente extends Tercero
{
    protected $table = 'core_terceros';

    public static function opciones_campo_select()
    {
        $opciones = Tercero::leftJoin('vtas_clientes','vtas_clientes.core_tercero_id','=','core_terceros.id')
                        ->whereNull('vtas_clientes.core_tercero_id')
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
