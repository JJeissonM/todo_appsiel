<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomPagoAutomaticoDetalle extends Model
{
    protected $table = 'nom_pagos_automaticos_detalles';

    protected $fillable = [
        'nom_pago_automatico_id', 'nom_contrato_id', 'core_tercero_id',
        'cxp_movimiento_id', 'cxp_abono_id', 'valor_pagado'
    ];
}
