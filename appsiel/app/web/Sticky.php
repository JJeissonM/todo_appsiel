<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Sticky extends Model
{
    protected $table = 'pw_stickies';
    protected $fillable = ['id', 'posicion', 'ancho_boton', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function stickybotons()
    {
        return $this->hasMany(Stickyboton::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
