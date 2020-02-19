<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Pagina extends Model
{

    protected  $table = 'pw_paginas';
    protected $fillable = ['descripcion', 'meta_description', 'meta_keywords', 'codigo_google_analitics', 'favicon', 'titulo', 'pagina_inicio', 'logo', 'email_interno', 'estado', 'created_at', 'updated_at'];

    public function widgets(){
        return $this->hasMany(Widget::class,'pagina_id');
    }

}
