<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    protected $table = 'pw_archivos';
    protected $fillable = ['id', 'formato', 'titulo', 'descripcion', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function archivoitems()
    {
        return $this->hasMany(Archivoitem::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
