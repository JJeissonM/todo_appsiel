<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected  $table = 'pw_articles';
    protected  $fillable = ['id', 'titulo', 'contenido', 'descripcion', 'estado', 'articlesetup_id', 'imagen', 'created_at', 'updated_at'];

    public function articlesetup()
    {
        return $this->belongsTo(Articlesetup::class);
    }
}
