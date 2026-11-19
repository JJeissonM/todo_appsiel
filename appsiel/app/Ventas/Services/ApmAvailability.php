<?php

namespace App\Ventas\Services;

class ApmAvailability
{
    public static function enabled()
    {
        return in_array('apm', [
            config('ventas.metodo_impresion_pedido_ventas'),
            config('ventas.metodo_impresion_pedido_restaurante'),
            config('ventas.metodo_impresion_factura_pos')
        ], true) || (int) config('ventas_pos.apm_scale_listener_activo') === 1
            || (int) config('tesoreria.usar_apm_pago_cxp') === 1;
    }

    public static function isApmModel($namespace)
    {
        return in_array(str_replace('\\\\', '\\', (string) $namespace), [
            'App\\Ventas\\ApmDevice', 'App\\Ventas\\ApmPrintJob', 'App\\Ventas\\ApmPrintStatus',
            'App\\Ventas\\ApmPrinterDevice'
        ], true);
    }
}
