<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

use DB;

class TesoDocRegistroPago extends Model
{
    protected $table = 'teso_doc_registros_pagos';

    protected $fillable = ['teso_encabezado_pago_id','teso_motivo_id','core_tercero_id','detalle_operacion','valor','estado'];
}
