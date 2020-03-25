<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Conductor extends Model
{
    protected $table = 'cte_conductors';
    protected $fillable = ['id', 'tercero_id', 'estado', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Conductor::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_conductors.tercero_id')
                            ->select('cte_conductors.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->numero_identificacion.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
