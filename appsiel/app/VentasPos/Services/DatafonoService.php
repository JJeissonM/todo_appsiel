<?php

namespace App\VentasPos\Services;

class DatafonoService
{
    public function get_datafono_amount($invoice)
    {
        if (!(int)config('ventas_pos.manejar_datafono')) {
            return 0;
        }
        return (new PaymentReconciliationService())->sumar_por_motivo(
            $invoice->lineas_registros_medios_recaudos,
            config('ventas_pos.motivo_tesoreria_datafono')
        );
    }
}
