<?php

namespace App\CxP\Services;

use App\Contabilidad\ContabMovimiento;
use App\CxP\CxpMovimiento;

class CxpAccountingAccountResolver
{
    public function getPayableAccountId(CxpMovimiento $movimiento_cxp)
    {
        return $this->getAccountId($movimiento_cxp, 'credito');
    }

    public function getAdvanceAccountId(CxpMovimiento $movimiento_cxp)
    {
        return $this->getAccountId($movimiento_cxp, 'debito');
    }

    protected function getAccountId(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $linea_contable = $this->getAccountingLineByCxpPosition($movimiento_cxp, $naturaleza);

        if (!is_null($linea_contable)) {
            return $linea_contable->contab_cuenta_id;
        }

        return $this->getAccountIdByDocumentFallback($movimiento_cxp, $naturaleza);
    }

    protected function getAccountingLineByCxpPosition(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $movimientos_cxp = $this->baseCxpQuery($movimiento_cxp, $naturaleza)
            ->orderBy('id')
            ->get(['id']);

        $posicion = $movimientos_cxp->pluck('id')->search($movimiento_cxp->id);

        if ($posicion === false) {
            return null;
        }

        $lineas_contables = $this->baseAccountingQuery($movimiento_cxp, $naturaleza)
            ->orderBy('id')
            ->get(['id', 'contab_cuenta_id']);

        if (!$lineas_contables->has($posicion)) {
            return null;
        }

        return $lineas_contables->get($posicion);
    }

    protected function getAccountIdByDocumentFallback(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $query = $this->baseAccountingQuery($movimiento_cxp, $naturaleza);

        if ($naturaleza == 'credito') {
            $query->where('valor_debito', 0);
        } else {
            $query->where('valor_credito', 0);
        }

        return $query->value('contab_cuenta_id');
    }

    protected function baseCxpQuery(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $query = CxpMovimiento::where([
            'core_empresa_id' => $movimiento_cxp->core_empresa_id,
            'core_tipo_transaccion_id' => $movimiento_cxp->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $movimiento_cxp->core_tipo_doc_app_id,
            'consecutivo' => $movimiento_cxp->consecutivo,
            'core_tercero_id' => $movimiento_cxp->core_tercero_id,
        ]);

        if ($naturaleza == 'credito') {
            return $query->where('valor_documento', '>', 0);
        }

        return $query->where('valor_documento', '<', 0);
    }

    protected function baseAccountingQuery(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $query = ContabMovimiento::where([
            'core_empresa_id' => $movimiento_cxp->core_empresa_id,
            'core_tipo_transaccion_id' => $movimiento_cxp->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $movimiento_cxp->core_tipo_doc_app_id,
            'consecutivo' => $movimiento_cxp->consecutivo,
            'core_tercero_id' => $movimiento_cxp->core_tercero_id,
        ]);

        if ($naturaleza == 'credito') {
            return $query->where('valor_credito', '<', 0)
                ->where('tipo_transaccion', 'crear_cxp');
        }

        return $query->where('valor_debito', '>', 0)
            ->whereIn('tipo_transaccion', ['anticipo_cxp', 'crear_anticipo_cxp']);
    }
}
