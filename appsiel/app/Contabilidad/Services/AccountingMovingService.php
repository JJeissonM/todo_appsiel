<?php 

namespace App\Contabilidad\Services;

use App\Sistema\ValueObjects\TransactionPrimaryKeyVO;

use App\Contabilidad\ContabMovimiento;

class AccountingMovingService
{
    public function delete_move( TransactionPrimaryKeyVO $trans_prim_key )
    {
        ContabMovimiento::where( $trans_prim_key->tpk )->delete();
    }
}