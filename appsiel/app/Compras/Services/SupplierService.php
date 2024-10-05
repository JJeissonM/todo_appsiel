<?php 

namespace App\Compras\Services;

use App\Compras\Proveedor;

class SupplierService
{

    public function get_linea_item_sugerencia( Proveedor $linea, $clase, $primer_item, $ultimo_item )
    {
        $descripcion = $linea->nombre_proveedor;
        if ( $linea->razon_social != '' ) {
            $descripcion .=  ' ('. $linea->razon_social . ')';
        }

        return '<a class="list-group-item list-group-item-proveedor '.$clase.'" data-proveedor_id="'.$linea->proveedor_id.
                            '" data-primer_item="'.$primer_item.
                            '" data-ultimo_item="'.$ultimo_item.
                            '" data-nombre_proveedor="'.$linea->nombre_proveedor.
                            '" data-clase_proveedor_id="'.$linea->clase_proveedor_id.
                            '" data-liquida_impuestos="'.$linea->liquida_impuestos.
                            '" data-core_tercero_id="'.$linea->core_tercero_id.
                            '" data-numero_identificacion="'.$linea->numero_identificacion.
                            '" data-inv_bodega_id="'.$linea->inv_bodega_id.
                            '" data-dias_plazo="'.$linea->dias_plazo.
                            '" > ' . $descripcion . ' ('.number_format($linea->numero_identificacion,0,',','.').') </a>';
    }
}