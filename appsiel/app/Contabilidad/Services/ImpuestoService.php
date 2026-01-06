<?php 

namespace App\Contabilidad\Services;

use App\Compras\Proveedor;
use App\Inventarios\InvProducto;
use App\Ventas\Cliente;

class ImpuestoService
{
    public function get_tasa_item($producto_id, $proveedor_id, $cliente_id)
    {
        $tasa_impuesto = 0;

        // SI LA EMPRESA NO LIQUIDA IMPUESTOS
        if (!config('configuracion.liquidacion_impuestos')) {
            return 0;
        }

        // SI EL PRODUCTO NO LIQUIDA IMPUESTOS
        $tasa_impuesto = InvProducto::get_tasa_impuesto($producto_id);
        if ($tasa_impuesto == 0) {
            return 0;
        }


        if ($proveedor_id != 0) {
            $liquida_impuestos = Proveedor::find($proveedor_id)->liquida_impuestos;
            if (!$liquida_impuestos) {
                return 0;
            }
        }


        if ($cliente_id != 0) {
            $liquida_impuestos = Cliente::find($cliente_id)->liquida_impuestos;
            if (!$liquida_impuestos) {
                return 0;
            }
        }

        return $tasa_impuesto;
    }
}