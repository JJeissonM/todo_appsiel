<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class CustomHtml extends Model
{
    protected $table = 'pw_custom_html';
    protected $fillable = [ 'contenido', 'estilos', 'scripts', 'links', 'parametros', 'widget_id'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }
}
