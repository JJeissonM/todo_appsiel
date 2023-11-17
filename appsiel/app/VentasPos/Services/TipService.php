<?php 

namespace App\VentasPos\Services;

class TipService
{
    public function get_tip_amount($invoice)
    {
        if (!(int)config('ventas_pos.manejar_propinas')) {
            return 0;
        }

        $lineas_recaudos = json_decode($invoice->lineas_registros_medios_recaudos);


        $tip_value = 0;
        if ( !is_null($lineas_recaudos) )
        {
            foreach ($lineas_recaudos as $linea)
            {
                if ( (int)explode("-", $linea->teso_motivo_id)[0] == (int)config('ventas_pos.motivo_tesoreria_propinas') ) {
                    $tip_value += (float)substr($linea->valor, 1);
                };
            }
        }

        return $tip_value;
    }
}