<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;

use App\Http\Controllers\Core\TransaccionController;
use App\VentasPos\Services\AccumulationService;
use Illuminate\Support\Facades\Auth;

class AplicacionController extends TransaccionController
{
    
    public function testing()
    {
        $pdv_id = 1;

        $obj_acumm_serv = new AccumulationService( $pdv_id );

        // Un documento de ENSAMBLE (MK) por cada Item Platillo vendido
        $obj_acumm_serv->hacer_preparaciones_recetas();
    }

}