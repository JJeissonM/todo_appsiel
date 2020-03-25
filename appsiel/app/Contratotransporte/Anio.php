<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Anio extends Model
{
    protected $table = 'cte_anios';
    protected $fillable = ['id', 'anio', 'created_at', 'updated_at'];



    public static function opciones_campo_select()
    {
        $opciones = Anio::all();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->anio;
        }

        return $vec;
    }
}
