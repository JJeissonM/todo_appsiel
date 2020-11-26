<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    protected $table = 'pw_logins';
    protected $fillable = ['id', 'titulo', 'ruta', 'imagen', 'ondas', 'tipo_fondo', 'fondo', 'disposicion', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
