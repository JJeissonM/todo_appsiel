<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class Tiporesponsable extends Model
{
    protected $table = 'sga_tiporesponsables';

    protected $fillable = ['id', 'descripcion', 'created_at', 'updated_at'];

    public function responsableestudiantes()
    {
        return $this->hasMany(Responsableestudiante::class);
    }
}
