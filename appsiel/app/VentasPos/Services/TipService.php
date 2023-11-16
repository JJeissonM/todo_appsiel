<?php 

namespace App\VentasPos\Services;

use \View;

class TipService
{
    public function get_tip_amount($invoice)
    {
        if (!(int)config('ventas_pos.manejar_propinas')) {
            return 0;
        }

        return 4000;
    }
}