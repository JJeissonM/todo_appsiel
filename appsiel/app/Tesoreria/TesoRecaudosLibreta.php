<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoRecaudosLibreta extends Model
{
    public $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','id_libreta', 'id_cartera', 'concepto', 'fecha_recaudo', 'teso_medio_recaudo_id', 'cantidad_cuotas','valor_recaudo','mi_token','creado_por','modificado_por'];
}
