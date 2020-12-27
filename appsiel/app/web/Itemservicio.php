<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Itemservicio extends Model
{
    protected $table = 'pw_itemservicios';
    protected $fillable = ['id', 'titulo', 'descripcion', 'icono', 'servicio_id', 'created_at', 'updated_at'];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}
