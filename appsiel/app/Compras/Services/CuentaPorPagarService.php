<?php

namespace App\Compras\Services;

use App\Compras\Proveedor;
use App\Contabilidad\ContabCuenta;

class CuentaPorPagarService
{
    public function resolver($proveedor_id, $cuenta_directa_id, $empresa_id)
    {
        if ($cuenta_directa_id === null || $cuenta_directa_id === '') {
            return Proveedor::get_cuenta_por_pagar($proveedor_id);
        }

        $cuenta = ContabCuenta::where('id', $cuenta_directa_id)
            ->where('core_empresa_id', $empresa_id)->where('estado', 'Activo')->first();
        if (!$cuenta) {
            throw new \InvalidArgumentException('La cuenta por pagar directa debe existir, estar activa y pertenecer a la empresa de la compra.');
        }
        return (int)$cuenta->id;
    }
}
