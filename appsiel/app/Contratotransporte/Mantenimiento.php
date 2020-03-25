<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Mantenimiento extends Model
{
    protected $table = 'cte_mantenimientos';
    protected $fillable = ['id', 'anioperiodo_id', 'fecha', 'sede', 'revisado', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Mantenimiento::leftJoin('cte_anioperiodos', 'cte_anioperiodos.id', '=', 'cte_mantenimientos.anioperiodo_id')
                            ->leftJoin('cte_anios','cte_anios.id','=','cte_anioperiodos.anio_id')
        						->select('cte_anios.anio','cte_mantenimientos.id','cte_mantenimientos.sede','cte_anioperiodos.fin','cte_anioperiodos.inicio')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->sede.': '.$opcion->anio.' ('.$opcion->inicio.' - '.$opcion->fin.')';
        }

        return $vec;
    }
}
