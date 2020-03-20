<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantilla extends Model
{
    protected $table = 'cte_plantillas';
    protected $fillable = ['id', 'estado', 'titulo', 'direccion', 'telefono', 'correo', 'firma', 'pie_pagina1', 'titulo_atras', 'created_at', 'updated_at'];
}
