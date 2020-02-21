<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $table = 'pw_fotos';
    protected $fillable = ['id', 'nombre', 'album_id', 'created_at', 'updated_at'];

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
