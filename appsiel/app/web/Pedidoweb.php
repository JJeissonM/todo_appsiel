<?php

namespace App\web;

use App\Inventarios\InvProducto;
use Illuminate\Database\Eloquent\Model;

class Pedidoweb extends Model
{
    protected $table = 'pw_pedidowebs';
    protected $fillable = ['id', 'titulo', 'descripcion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
