<?php

namespace App\PropiedadHorizontal;

use Illuminate\Database\Eloquent\Model;

use DB;
use Input;
use Auth;


class PhNotasPqr extends Model
{
    //protected $table = 'ph_notaasdadass_pqrs';

    protected $fillable = ['ph_pqr_id','detalle','fecha','creado_por','modificado_por','estado'];
}
