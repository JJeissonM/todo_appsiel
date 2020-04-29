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

    public function dibujar_individual()
    {
        //$fotos = $this->fotos;
        return '<a style="margin: 5px; font-size: 0.9em; color: black;" href="#" title="'.$this->titulo.'">
                    <img src="'.$this->fotos->first()->nombre.'" style="border-radius: 5px; height: 210px; width=210px;">
                    <div style="font-weight: bold;">'.$this->titulo.'</div>
                    <div style="margin-top: -10px;">'.count($this->fotos).' elementos</div>
                </a>';
    }
}
