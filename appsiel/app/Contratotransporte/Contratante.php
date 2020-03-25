<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Contratante extends Model
{
    protected $table = 'cte_contratantes';
    protected $fillable = ['id', 'tercero_id', 'estado', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Contratante::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_contratantes.tercero_id')
                            ->select('cte_contratantes.id','core_terceros.descripcion','core_terceros.numero_identificacion')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->numero_identificacion.' '.$opcion->descripcion;
        }

        return $vec;
    }
}
