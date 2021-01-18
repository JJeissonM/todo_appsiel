<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Aboutus extends Model
{
    protected $table = 'pw_aboutuses';
    protected $fillable = ['id', 'titulo', 'descripcion', 'mision', 'vision', 'valores', 'imagen', 'disposicion', 'widget_id', 'resenia', 'mision_icono', 'vision_icono', 'valor_icono', 'resenia_icono', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'mostrar_leermas', 'configuracionfuente_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
