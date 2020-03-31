<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Itemcorreo extends Model
{
    protected $table = 'pw_itemcorreos';
    protected $fillable = ['id', 'activo', 'correo', 'asunto', 'encabezado', 'contenido', 'destinatario', 'correo_id', 'created_at', 'updated_at'];

    public function correo(){
        return $this->belongsTo(Correo::class);
    }

}
