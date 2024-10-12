<?php 

namespace App\Ventas\Services;

use App\Ventas\ListaPrecioDetalle;

class PricesServices
{
    public function create_item_price( $data )
    {
        ListaPrecioDetalle::create( $data );
    }

    public function create_or_update_item_price( $data )
    {
        $nuevo_precio_venta = 0;
            if (isset($data['precio_venta'])) {
                $nuevo_precio_venta = $data['precio_venta'];
            }

            $reg_precio_actual = ListaPrecioDetalle::where([
                ['lista_precios_id', '=', $data['lista_precios_id']],
                ['inv_producto_id', '=', $data['inv_producto_id']]
            ])
            ->get()
            ->last();

            if ($reg_precio_actual == null) {
                $this->create_item_price($data);
            }else{
                if ($nuevo_precio_venta != $reg_precio_actual->precio) {
                    $reg_precio_actual->precio = $nuevo_precio_venta;
                    $reg_precio_actual->save();
                }
            }
    }
}