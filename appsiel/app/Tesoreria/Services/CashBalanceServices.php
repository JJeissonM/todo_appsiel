<?php 

namespace App\Tesoreria\Services;

use App\Tesoreria\TesoMovimiento;

class CashBalanceServices
{
    public function cash_balance_amount($cash_account_id, $fecha_corte)
    {
        $saldo = 0;
        $movs = TesoMovimiento::where('teso_caja_id', $cash_account_id)
                            ->where('fecha', '<=', $fecha_corte)
                            ->get();
                            
        if (count($movs) > 0) {
            foreach ($movs as $m) {
                $saldo += $m->valor_movimiento;
            }
        }

        return $saldo;
    }
}