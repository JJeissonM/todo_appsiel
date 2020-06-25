<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Articlecategory extends Model
{
    protected  $table = 'pw_articlecategories';
    protected  $fillable = ['id', 'titulo', 'descripcion', 'created_at', 'updated_at'];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}
