<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Testimoniale extends Model
{
    protected $table = 'pw_testimoniales';
    protected $fillable = ['id', 'titulo', 'descripcion', 'disposicion', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function itemtestimonials()
    {
        return $this->hasMany(Itemtestimonial::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
