<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'pw_clientes';
    protected $fillable = ['id', 'title', 'descripcion', 'tipo_fondo', 'fondo', 'repetir', 'direccion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function clienteitems()
    {
        return $this->hasMany(Clienteitem::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
