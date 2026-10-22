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
        $linea_contable = $this->getAccountingLineByCxpMarker($movimiento_cxp, $naturaleza);

        if (!is_null($linea_contable)) {
            return (int) $linea_contable->contab_cuenta_id;
        }

        $linea_contable = $this->getUniqueAccountingLineByAmount($movimiento_cxp, $naturaleza);

        if (!is_null($linea_contable)) {
            return (int) $linea_contable->contab_cuenta_id;
        }

        $linea_contable = $this->getAccountingLineByCxpPosition($movimiento_cxp, $naturaleza);

        if (!is_null($linea_contable)) {
            return (int) $linea_contable->contab_cuenta_id;
        }

        $cuenta_id = $this->getAccountIdByDocumentFallback($movimiento_cxp, $naturaleza);

        return is_null($cuenta_id) ? null : (int) $cuenta_id;
    }

    /**
     * Los documentos que identifican la causación de cartera permiten obtener
     * directamente la cuenta que originó la CxP. Esto evita confundirla con
     * otros créditos del mismo tercero (por ejemplo, anticipos de nómina).
     */
    protected function getAccountingLineByCxpMarker(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $tipos = $naturaleza == 'credito'
            ? ['crear_cxp']
            : ['anticipo_cxp', 'crear_anticipo_cxp'];

        $lineas = $this->baseAccountingQuery($movimiento_cxp, $naturaleza)
            ->whereIn('tipo_transaccion', $tipos)
            ->orderBy('id')
            ->get(['id', 'contab_cuenta_id', 'valor_debito', 'valor_credito']);

        if ($lineas->isEmpty()) {
            return null;
        }

        $coincidentes = $this->filterAccountingLinesByAmount($lineas, $movimiento_cxp, $naturaleza);
        if ($coincidentes->count() == 1) {
            return $coincidentes->first();
        }

        if ($lineas->count() == 1) {
            return $lineas->first();
        }

        return $this->getAccountingLineAtCxpPosition($movimiento_cxp, $naturaleza, $lineas);
    }

    /**
     * Compatibilidad con documentos históricos que no marcaron la línea con
     * crear_cxp: solo se acepta la coincidencia por valor cuando es inequívoca.
     */
    protected function getUniqueAccountingLineByAmount(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $lineas = $this->baseAccountingQuery($movimiento_cxp, $naturaleza)
            ->orderBy('id')
            ->get(['id', 'contab_cuenta_id', 'valor_debito', 'valor_credito']);

        $coincidentes = $this->filterAccountingLinesByAmount($lineas, $movimiento_cxp, $naturaleza);

        return $coincidentes->count() == 1 ? $coincidentes->first() : null;
    }

    protected function filterAccountingLinesByAmount($lineas, CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $valor_documento = abs((float) $movimiento_cxp->valor_documento);

        return $lineas->filter(function ($linea) use ($valor_documento, $naturaleza) {
            $valor_contable = $naturaleza == 'credito'
                ? abs((float) $linea->valor_credito)
                : abs((float) $linea->valor_debito);

            return abs($valor_contable - $valor_documento) < 0.01;
        })->values();
    }

    protected function getAccountingLineByCxpPosition(CxpMovimiento $movimiento_cxp, $naturaleza)
    {
        $movimientos_cxp = $this->baseCxpQuery($movimiento_cxp, $naturaleza)
            ->orderBy('id')
            ->get(['id']);

        $lineas_contables = $this->baseAccountingQuery($movimiento_cxp, $naturaleza)
            ->orderBy('id')
            ->get(['id', 'contab_cuenta_id']);

        return $this->getAccountingLineAtCxpPosition(
            $movimiento_cxp,
            $naturaleza,
            $lineas_contables,
            $movimientos_cxp
        );
    }

    protected function getAccountingLineAtCxpPosition(
        CxpMovimiento $movimiento_cxp,
        $naturaleza,
        $lineas_contables,
        $movimientos_cxp = null
    ) {
        if (is_null($movimientos_cxp)) {
            $movimientos_cxp = $this->baseCxpQuery($movimiento_cxp, $naturaleza)
                ->orderBy('id')
                ->get(['id']);
        }

        $posicion = $movimientos_cxp->pluck('id')->search($movimiento_cxp->id);

        if ($posicion === false) {
            return null;
        }

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
            // La etiqueta tipo_transaccion no es uniforme entre los distintos
            // documentos que generan CxP. La naturaleza del movimiento es la
            // fuente confiable: el pasivo nace en el crédito.
            return $query->where('valor_credito', '<', 0);
        }

        // Algunos anticipos históricos no tienen tipo_transaccion. La cuenta
        // de anticipo se identifica por el débito del documento origen.
        return $query->where('valor_debito', '>', 0);
    }
}
