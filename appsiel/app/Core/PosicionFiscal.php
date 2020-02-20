<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class PosicionFiscal extends Model
{
    protected $table = 'core_posiciones_fiscales'; 

    protected $fillable = ['core_empresa_id','descripcion'];
}
