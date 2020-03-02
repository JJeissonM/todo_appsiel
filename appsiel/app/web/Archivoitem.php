<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Archivoitem extends Model
{
    protected $table = 'pw_archivoitems';
    protected $fillable = ['id', 'file', 'estado', 'archivo_id', 'created_at', 'updated_at'];

    public function archivo()
    {
        return $this->belongsTo(Archivo::class);
    }
}
