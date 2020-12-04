<?php

namespace App\web;

use Illuminate\Database\Eloquent\Model;

class Fuente extends Model
{
    protected $table = 'pw_fuentes';
    protected $fillable = ['id', 'font', 'path', 'created_at', 'updated_at'];

    public function configuracionfuentes()
    {
        return $this->hasMany(Configuracionfuente::class);
    }
}
