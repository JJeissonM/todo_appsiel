<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class DifoCamposSeccion extends Model
{
    protected $table = 'difo_campos_secciones';

    protected $fillable = ['descripcion'];
}
