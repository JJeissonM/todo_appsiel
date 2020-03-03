<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Archivo extends Model
{
    protected $table = 'pw_archivos';
    protected $fillable = ['id', 'titulo', 'descripcion', 'widget_id', 'created_at', 'updated_at'];

    public function widget()
    {
        return $this->belongsTo(Widget::class);
    }

    public function archivoitems()
    {
        return $this->hasMany(Archivoitem::class);
    }
}
