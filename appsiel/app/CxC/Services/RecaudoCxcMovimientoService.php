<?php

namespace App\CxC\Services;

use App\CxC\CxcMovimiento;

class RecaudoCxcMovimientoService
{
    public function seleccionar($documento, $id, $abono)
    {
        if (!is_numeric($abono) || !is_finite((float)$abono) || round((float)$abono, 2) <= 0) {
            throw new \InvalidArgumentException('Cada abono debe ser un valor positivo.');
        }
        $movimiento = CxcMovimiento::where('id', (int)$id)
            ->where('core_empresa_id', $documento->core_empresa_id)
            ->where('core_tercero_id', $documento->core_tercero_id)
            ->lockForUpdate()->first();
        if (!$movimiento || (float)$movimiento->valor_documento <= 0) {
            throw new \InvalidArgumentException('El movimiento de CxC seleccionado no pertenece al tercero y empresa del recaudo.');
        }
        if (round((float)$abono, 2) > round((float)$movimiento->saldo_pendiente, 2)) {
            throw new \InvalidArgumentException('El abono supera el saldo pendiente del movimiento de CxC seleccionado.');
        }
        $movimiento->validar_saldo_disponible_para_abono(round((float)$abono, 2));
        return $movimiento;
    }

    public function cuenta(CxcMovimiento $movimiento)
    {
        $cuenta = (new CxcAccountingAccountResolver)->getReceivableAccountId($movimiento);
        if (!$cuenta) {
            throw new \InvalidArgumentException('No se pudo identificar de forma unica la cuenta contable del movimiento de CxC '.$movimiento->id.'. Revise el documento origen.');
        }
        return $cuenta;
    }
}
