<?php

namespace App\CxC\Services;

use App\Contabilidad\ContabMovimiento;
use App\CxC\CxcMovimiento;

class CxcAccountingAccountResolver
{
    public function getReceivableAccountId(CxcMovimiento $movimiento_cxc)
    {
        return $this->getAccountId($movimiento_cxc, 'debito');
    }

    public function getAdvanceAccountId(CxcMovimiento $movimiento_cxc)
    {
        return $this->getAccountId($movimiento_cxc, 'credito');
    }

    protected function getAccountId(CxcMovimiento $movimiento_cxc, $naturaleza)
    {
        $query = ContabMovimiento::where([
            'core_empresa_id'=>$movimiento_cxc->core_empresa_id,
            'core_tipo_transaccion_id'=>$movimiento_cxc->core_tipo_transaccion_id,
            'core_tipo_doc_app_id'=>$movimiento_cxc->core_tipo_doc_app_id,
            'consecutivo'=>$movimiento_cxc->consecutivo,
            'core_tercero_id'=>$movimiento_cxc->core_tercero_id
        ]);
        $campo = $naturaleza === 'debito' ? 'valor_debito' : 'valor_credito';
        $query->where($campo, $naturaleza === 'debito' ? '>' : '<', 0);
        if (!empty($movimiento_cxc->contab_cuenta_id)) {
            return $query->where('contab_cuenta_id', $movimiento_cxc->contab_cuenta_id)->exists()
                ? (int)$movimiento_cxc->contab_cuenta_id : null;
        }
        $lineas = $query->get();
        $tipo = $naturaleza === 'debito' ? 'crear_cxc' : 'crear_anticipo_cxc';
        $origenes = $lineas->filter(function ($linea) use ($tipo) { return $linea->tipo_transaccion === $tipo; });
        if (!$origenes->isEmpty()) {
            $lineas = $origenes;
        }
        $coincidentes = $lineas->filter(function ($linea) use ($movimiento_cxc, $campo) {
            return abs(abs($linea->$campo) - abs($movimiento_cxc->valor_documento)) < 0.005;
        });
        if (!$coincidentes->isEmpty()) {
            $lineas = $coincidentes;
        }
        $cuentas = $lineas->pluck('contab_cuenta_id')->unique();
        // No se elige la primera cuenta si el documento tiene varias posibles.
        return $cuentas->count() === 1 ? (int)$cuentas->first() : null;
    }
}
