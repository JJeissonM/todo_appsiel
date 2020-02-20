<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Builder;

class Menunavegacion extends Model
{

    protected  $table = 'pw_menunavegacion';
    protected  $fillable = ['id', 'titulo', 'descripcion','icono', 'enlace', 'parent_id', 'estado', 'created_at', 'updated_at'];

    public function navegacion(){
        return $this->belongsTo(Navegacion::class,'navegacion_id');
    }

    public function scopeSubmenus($query){
          return $query->where('parent_id',$this->id);
    }

}
