<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class TesoArchivoPlanoBanco extends Model
{
    protected $fillable = ['codigo_transaccion','ciudad','valor_transaccion','fecha_transaccion','codigo_referencia_tercero','estado'];
}
