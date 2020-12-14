<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $table = 'pw_servicios';
    protected $fillable = ['id', 'titulo', 'descripcion', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'disposicion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function itemservicios()
    {
        return $this->hasMany(Itemservicio::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
