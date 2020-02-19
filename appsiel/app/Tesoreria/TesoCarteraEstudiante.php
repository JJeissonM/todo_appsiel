<?php

namespace App\Tesoreria;

use Illuminate\Database\Eloquent\Model;

class TesoCarteraEstudiante extends Model
{
    protected $fillabel = ['id_libreta','id_estudiante','concepto',
    						'valor_cartera','valor_pagado','saldo_pendiente','fecha_vencimiento','estado'];
}
