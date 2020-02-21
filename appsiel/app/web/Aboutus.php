<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Aboutus extends Model
{
    protected  $table = 'pw_aboutuses';
    protected  $fillable = ['id', 'titulo','descripcion','mision','vision','valores','imagen','widget_id', 'created_at', 'updated_at'];

    public function widget(){
        return $this->belongsTo(Widget::class);
    }
}
