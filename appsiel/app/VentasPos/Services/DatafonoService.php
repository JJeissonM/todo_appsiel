<?php 

namespace App\VentasPos\Services;

class DatafonoService
{
    public function get_datafono_amount($invoice)
    {
        if (!(int)config('ventas_pos.manejar_datafono')) {
            return 0;
        }

        $lineas_recaudos = json_decode($invoice->lineas_registros_medios_recaudos);

        $datafono_value = 0;
        if ( !is_null($lineas_recaudos) )
        {
            foreach ($lineas_recaudos as $linea)
            {
                if ( (int)explode("-", $linea->teso_motivo_id)[0] == (int)config('ventas_pos.motivo_tesoreria_datafono') ) {
                    $datafono_value += (float)substr($linea->valor, 1);
                };
            }
        }

        return $datafono_value;
    }
}