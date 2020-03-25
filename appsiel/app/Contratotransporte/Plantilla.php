<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = 'cte_plantillas';
    protected $fillable = ['id', 'titulo', 'direccion', 'telefono', 'correo', 'firma', 'pie_pagina1', 'titulo_atras', 'estado', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Plantilla::all();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->titulo;
        }

        return $vec;
    }
}
