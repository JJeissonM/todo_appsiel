<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class Acl extends Model
{
    protected $table = 'core_acl';
    
    protected $fillable = [ 'modelo_recurso_id','recurso_id', 'user_id', 'permiso_denegado', 'permiso_concedido' ];
}
