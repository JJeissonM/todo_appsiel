<?php

namespace App\Contabilidad;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class ContabArbolGruposCuenta extends Model
{
    protected $fillable = ['core_empresa_id','abuelo_id','padre_id','hijo_id','nivel','abuelo_descripcion','padre_descripcion','hijo_descripcion'];
}