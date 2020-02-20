<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'pw_seccion';
    protected $fillable = ['id','nombre','descripcion','preview','created_at','updated_at'];

    public function widgets(){
        return $this->hasMany(Widget::class,'seccion_id');
    }

}
