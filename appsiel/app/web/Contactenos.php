<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Contactenos extends Model
{
    protected $table = 'pw_contactenos';
    protected $fillable = ['id', 'empresa', 'telefono', 'ciudad', 'correo', 'direccion', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
