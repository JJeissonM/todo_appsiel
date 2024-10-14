<?php

namespace App\Http\Controllers\VentasPos;

use App\Http\Controllers\Core\TransaccionController;
use App\Inventarios\Services\RecipeServices;

class AplicacionController extends TransaccionController
{
    
    public function testing()
    {
        /**
         * 
         */

        
        $obj_acumm_serv = new RecipeServices();

        $obj_acumm_serv->get_recetas_items_manejan_contornos();

    }

}