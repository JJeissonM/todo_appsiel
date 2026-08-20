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
        $linea_contable = $this->getAccountingLineByCxcPosition($movimiento_cxc, $naturaleza);

        if (!is_null($linea_contable)) {
            return (int) $linea_contable->contab_cuenta_id;
        }

        $cuenta_id = $this->baseAccountingQuery($movimiento_cxc, $naturaleza)
            ->value('contab_cuenta_id');

        return is_null($cuenta_id) ? null : (int) $cuenta_id;
    }

    protected function getAccountingLineByCxcPosition(CxcMovimiento $movimiento_cxc, $naturaleza)
    {
        $movimientos_cxc = $this->baseCxcQuery($movimiento_cxc, $naturaleza)
            ->orderBy('id')
            ->get(['id']);

        $posicion = $movimientos_cxc->pluck('id')->search($movimiento_cxc->id);

        if ($posicion === false) {
            return null;
        }

        $lineas_contables = $this->baseAccountingQuery($movimiento_cxc, $naturaleza)
            ->orderBy('id')
            ->get(['id', 'contab_cuenta_id']);

        if (!$lineas_contables->has($posicion)) {
            return null;
        }

        return $lineas_contables->get($posicion);
    }

    protected function baseCxcQuery(CxcMovimiento $movimiento_cxc, $naturaleza)
    {
        $query = CxcMovimiento::where([
            'core_empresa_id' => $movimiento_cxc->core_empresa_id,
            'core_tipo_transaccion_id' => $movimiento_cxc->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $movimiento_cxc->core_tipo_doc_app_id,
            'consecutivo' => $movimiento_cxc->consecutivo,
            'core_tercero_id' => $movimiento_cxc->core_tercero_id,
        ]);

        if ($naturaleza == 'debito') {
            return $query->where('valor_documento', '>', 0);
        }

        return $query->where('valor_documento', '<', 0);
    }

    protected function baseAccountingQuery(CxcMovimiento $movimiento_cxc, $naturaleza)
    {
        $query = ContabMovimiento::where([
            'core_empresa_id' => $movimiento_cxc->core_empresa_id,
            'core_tipo_transaccion_id' => $movimiento_cxc->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $movimiento_cxc->core_tipo_doc_app_id,
            'consecutivo' => $movimiento_cxc->consecutivo,
            'core_tercero_id' => $movimiento_cxc->core_tercero_id,
        ]);

        if ($naturaleza == 'debito') {
            // La cuenta por cobrar nace en el débito del documento origen.
            return $query->where('valor_debito', '>', 0);
        }

        // El anticipo o saldo a favor del cliente nace en el crédito.
        return $query->where('valor_credito', '<', 0);
    }
}
