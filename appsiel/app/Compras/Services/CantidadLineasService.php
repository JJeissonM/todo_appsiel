<?php

namespace App\Compras\Services;

class CantidadLineasService
{
    public static function oculta()
    {
        return filter_var(config('compras.ocultar_cantidad_lineas', false), FILTER_VALIDATE_BOOLEAN);
    }

    public static function prepararRequest($request)
    {
        if (!self::oculta()) {
            return;
        }
        $lineas = json_decode($request->input('lineas_registros', '[]'));
        if (!is_array($lineas)) {
            return;
        }
        foreach ($lineas as $linea) {
            // Las referencias a entradas existentes conservan sus cantidades originales.
            if (!is_object($linea) || empty($linea->inv_producto_id)) {
                continue;
            }
            $linea->cantidad = 1;
            $precio = isset($linea->precio_unitario) ? (float)$linea->precio_unitario : 0;
            $descuento = $precio * (isset($linea->tasa_descuento) ? (float)$linea->tasa_descuento : 0) / 100;
            $linea->precio_total = $precio - $descuento;
            $linea->valor_total_descuento = $descuento;
            $tasa = isset($linea->tasa_impuesto) ? (float)$linea->tasa_impuesto : 0;
            $linea->base_impuesto = $linea->precio_total / (1 + $tasa / 100);
            $linea->valor_impuesto = $linea->precio_total - $linea->base_impuesto;
            $linea->costo_total = isset($linea->costo_unitario) ? (float)$linea->costo_unitario : $linea->base_impuesto;
        }
        $request->merge(['lineas_registros' => json_encode($lineas)]);
    }
}
