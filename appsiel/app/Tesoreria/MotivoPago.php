<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class MotivoPago extends Model
{
    protected $table = 'teso_motivos';

    public static function opciones_campo_select()
    {
        $opciones = MotivoPago::where('movimiento','salida')
                    ->select('id','descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
