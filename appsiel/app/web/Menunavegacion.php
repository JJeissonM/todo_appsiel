<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Builder;

class Menunavegacion extends Model
{

    protected  $table = 'pw_menunavegacion';
    protected  $fillable = ['titulo', 'descripcion','icono', 'enlace','navegacion_id', 'parent_id', 'estado', 'created_at', 'updated_at'];

    public function navegacion(){
        return $this->belongsTo(Navegacion::class,'navegacion_id');
    }

    public function subMenus(){
        return Menunavegacion::where('parent_id',$this->id)->get();
    }

}
