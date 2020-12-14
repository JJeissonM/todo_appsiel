<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Navegacion extends Model
{

    protected  $table = 'pw_navegacion';
    protected  $fillable = ['id', 'logo', 'color', 'fixed', 'alpha', 'disposicion', 'background', 'configuracionfuente_id', 'widget_id', 'created_at', 'updated_at'];

    public function menus()
    {
        return $this->hasMany(Menunavegacion::class, 'navegacion_id')->orderBy('orden');
    }

    public function configuracionfuente()
    {
        return $this->belongsTo(Configuracionfuente::class);
    }
}
