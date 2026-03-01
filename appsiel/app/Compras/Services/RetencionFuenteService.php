<?php

namespace App\Compras\Services;

use App\Contabilidad\Retencion;

class RetencionFuenteService
{
    public function get_retenciones_activas()
    {
        return Retencion::where('estado', 'Activo')
            ->select('id', 'descripcion', 'nombre_corto', 'tasa_retencion')
            ->orderBy('descripcion')
            ->get();
    }

    public function calcular_valor_retencion_linea($precio_unitario, $cantidad, $tasa_impuesto, $tasa_retencion)
    {
        $valor_bruto_linea = (float)$precio_unitario * (float)$cantidad;

        $divisor_iva = 1 + ((float)$tasa_impuesto / 100);
        if ($divisor_iva <= 0) {
            $divisor_iva = 1;
        }

        $base_sin_iva = $valor_bruto_linea / $divisor_iva;
        $valor_retencion = $base_sin_iva * ((float)$tasa_retencion / 100);

        return [
            'base_sin_iva' => round($base_sin_iva, 2),
            'valor_retencion' => round($valor_retencion, 2),
        ];
    }
}
