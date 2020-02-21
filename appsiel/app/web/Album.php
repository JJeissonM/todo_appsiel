<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    protected $table = 'pw_albums';
    protected $fillable = ['id', 'titulo', 'descripcion', 'galeria_id', 'created_at', 'updated_at'];

    public function galeria()
    {
        return $this->belongsTo(Galeria::class);
    }

    public function fotos()
    {
        return $this->hasMany(Foto::class);
    }
}
