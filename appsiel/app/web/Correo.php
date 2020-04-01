<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Correo extends Model
{
    protected $table = 'pw_correos';
    protected $fillable = ['id', 'nombre_remitente', 'email_remitente', 'color_base', 'color_fondo', 'color_texto', 'created_at', 'updated_at'];

    public function itemcorreos()
    {
        return $this->hasMany(Itemcorreo::class);
    }
}
