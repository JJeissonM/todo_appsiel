<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Anioperiodo extends Model
{
    protected $table = 'cte_anioperiodos';
    protected $fillable = ['id', 'anio_id', 'inicio', 'fin', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Anioperiodo::leftJoin('cte_anios','cte_anios.id','=','cte_anioperiodos.anio_id')
        						->select('cte_anios.anio','cte_anioperiodos.inicio','cte_anioperiodos.fin','cte_anioperiodos.id')
        						->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->anio.' ('.$opcion->inicio.' - '.$opcion->fin.')';
        }

        return $vec;
    }
}
