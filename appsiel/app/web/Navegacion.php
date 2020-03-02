<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Navegacion extends Model
{

    protected  $table = 'pw_navegacion';
    protected  $fillable = ['id', 'logo','color','width_logo','heigth_logo', 'created_at', 'updated_at'];

    public function menus(){
        return $this->hasMany(Menunavegacion::class,'navegacion_id')->orderBy('orden');
    }

}
