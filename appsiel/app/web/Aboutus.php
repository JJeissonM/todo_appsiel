<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Aboutus extends Model
{
    protected $table = 'pw_aboutuses';
    protected $fillable = ['id', 'titulo', 'descripcion', 'mision', 'vision', 'valores', 'imagen', 'disposicion', 'widget_id', 'resenia', 'mision_icono', 'vision_icono', 'valor_icono', 'resenia_icono', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
