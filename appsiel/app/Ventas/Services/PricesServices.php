<?php 

namespace App\Ventas\Services;

use App\Inventarios\InvProducto;
use App\Ventas\ListaPrecioDetalle;
use App\Ventas\VtasMovimiento;

class PricesServices
{
    public function create_item_price( $data )
    {
        ListaPrecioDetalle::create( $data );

        if (!isset($data['precio_venta'])) {
            $data['precio_venta'] = $data['precio'];
        }

        $item = InvProducto::find( (int)$data['inv_producto_id'] );
        $item->precio_venta = $data['precio_venta'];
        $item->save();
    }

    public function create_or_update_item_price( $data )
    {
        $nuevo_precio_venta = 0;
        if (isset($data['precio_venta'])) {
            $nuevo_precio_venta = $data['precio_venta'];
        }

        if (!isset($data['precio'])) {
            $data['precio'] = $data['precio_venta'];
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
                
                $item = InvProducto::find( (int)$data['inv_producto_id'] );
                $item->precio_venta = $nuevo_precio_venta;
                $item->save();
            }
        }        
    }

    public function get_item_price( $lista_precios_id, $fecha, $producto_id, $cliente_id )
    {
        $precio_unitario = ListaPrecioDetalle::get_precio_producto( $lista_precios_id, $fecha, $producto_id );
        
        if ( $precio_unitario != 0 ) {
            return $precio_unitario;
        }
                    
        // Precios traido del movimiento de ventas. El último precio liquidado al cliente para ese producto.
        $precio_unitario = VtasMovimiento::get_ultimo_precio_producto( $cliente_id, $producto_id );
        
        if ( $precio_unitario != 0 ) {
            return $precio_unitario;
        }

        $item = InvProducto::find( $producto_id );
        return $item->precio_venta;
    }
}