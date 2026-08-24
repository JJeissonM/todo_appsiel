<?php

namespace App\VentasPos\Services;

use App\VentasPos\Pdv;

class PosTransactionDateService
{
    /**
     * Resuelve la fecha contable de una operacion iniciada desde Ventas POS.
     */
    public function resolve(Pdv $pdv, $defaultDate = null)
    {
        if ((int)config('ventas_pos.asignar_fecha_apertura_a_facturas') === 1) {
            // Estos movimientos deben conservar la fecha del turno incluso si
            // las facturas POS se acumulan en tiempo real.
            return $pdv->ultima_fecha_apertura(false);
        }

        $defaultDate = trim((string)$defaultDate);

        return $defaultDate === '' ? date('Y-m-d') : $defaultDate;
    }
}
