<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected  $table = 'pw_articles';
    protected  $fillable = ['id', 'titulo', 'contenido', 'descripcion', 'estado', 'imagen', 'articlecategory_id', 'created_at', 'updated_at'];

    public function articlecategory()
    {
        return $this->belongsTo(Articlecategory::class);
    }

    public function articlesetup()
    {
        return $this->hasOne(Articlesetup::class);
    }
}
