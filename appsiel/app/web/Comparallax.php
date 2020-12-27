<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Comparallax extends Model
{
    protected $table = 'pw_comparallaxes';
    protected $fillable = ['id', 'titulo', 'descripcion', 'fondo', 'modo', 'textcolor', 'content_html', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
