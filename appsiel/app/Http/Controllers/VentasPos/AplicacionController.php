<?php

namespace App\Http\Controllers\VentasPos;

use Illuminate\Http\Request;

use App\Http\Controllers\Sistema\ModeloController;

use App\Http\Controllers\Core\TransaccionController;
use App\VentasPos\Services\AccumulationService;
use App\VentasPos\Services\RecipeServices;
use Illuminate\Support\Facades\Auth;

class AplicacionController extends TransaccionController
{
    
    public function testing()
    {
        $pdv_id = 1;

        /**
         * 
         */

        $lineas_registros = json_decode( '[{"inv_producto_id":"2925","precio_unitario":"8000","base_impuesto":"8000","tasa_impuesto":"0","valor_impuesto":"0","base_impuesto_total":"8000","cantidad":"1","precio_total":"8000","tasa_descuento":"0","valor_total_descuento":"0","lista_oculta_items_contorno_ids":"2928","Item":"2925++1/4+Pollo+++Yuca","Cantidad":"1+++(UND)","Precio+Unit.":"8000","Dcto.":"0%+(+$0+)","IVA":"0","Total":"$8.000"},{"inv_producto_id":"2925","precio_unitario":"8000","base_impuesto":"8000","tasa_impuesto":"0","valor_impuesto":"0","base_impuesto_total":"8000","cantidad":"1","precio_total":"8000","tasa_descuento":"0","valor_total_descuento":"0","lista_oculta_items_contorno_ids":"2929","Item":"2925++1/4+Pollo+++Plátano","Cantidad":"1+++(UND)","Precio+Unit.":"8000","Dcto.":"0%+(+$0+)","IVA":"0","Total":"$8.000"},{"inv_producto_id":"","precio_unitario":"","base_impuesto":"","tasa_impuesto":"","valor_impuesto":"","base_impuesto_total":"","cantidad":"","precio_total":"","tasa_descuento":"","valor_total_descuento":"","lista_oculta_items_contorno_ids":"2929","Item":"++ContornoYucaPlátanoBolloPapa+cocidaArepa","Cantidad":"","Precio+Unit.":"","Dcto.":"","IVA":"","Total":""}]' );

        $obj_acumm_serv = new RecipeServices( $pdv_id );

        return $obj_acumm_serv->cambiar_items_con_contornos($lineas_registros);

        // Un documento de ENSAMBLE (MK) por cada Item Platillo vendido
        $obj_acumm_serv->hacer_preparaciones_recetas();
    }

}