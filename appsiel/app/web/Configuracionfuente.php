<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Configuracionfuente extends Model
{
    protected $table = 'pw_configuracionfuentes';
    protected $fillable = ['id', 'fuente_id', 'configuracion_id', 'created_at', 'updated_at'];

    public function fuente()
    {
        return $this->belongsTo(Fuente::class);
    }

    public function configuracion()
    {
        return $this->belongsTo(Configuraciones::class);
    }
}
