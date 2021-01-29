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
        $primera_foto = $this->fotos->first();
        $url_primera_foto = 'img/avatar.png';

        if (!is_null($primera_foto)) {
            $url_primera_foto = $primera_foto->nombre;
        }

        return '<div class="abrir_modal" data-elemento_id="' . $this->id . '" style="padding: 5px; font-size: 0.9em; color: black;" title="' . $this->titulo . ' text-align:center;">
                    <img src="' . $url_primera_foto . '" style="border-radius: 5px; height: 300px; object-fit: contain; /*max-width: 210px;*/">
                    <div class="titulo" style="font-weight: bold;">' . $this->titulo . '</div>
                    <div class="subtitulo" style="margin-top: -10px;">' . count($this->fotos) . ' elementos</div>
                </div>';
    }
}
