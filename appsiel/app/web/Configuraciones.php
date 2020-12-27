<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Configuraciones extends Model
{
    protected $table = 'pw_configuracion_general';
    protected $fillable = ['color_primario', 'color_segundario', 'color_terciario', 'created_at', 'updated_at'];

    public function configuracionfuentes()
    {
        return $this->hasMany(Configuracionfuente::class);
    }
}
