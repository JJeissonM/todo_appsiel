<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Componente extends Model
{
    protected $table = 'pw_componente';
    protected $fillable = ['id','nombre','descripcion','path_componente','created_at','update_at'];

    public function widgets(){
        return $this->hasMany(Widget::class,'componente_id');
    }
}
