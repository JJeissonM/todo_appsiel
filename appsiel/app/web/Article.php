<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected  $table = 'pw_articles';
    protected  $fillable = ['id', 'titulo', 'contenido', 'estado', 'articlesetup_id', 'created_at', 'updated_at'];

    public function articlesetup()
    {
        return $this->belongsTo(Articlesetup::class);
    }
}
