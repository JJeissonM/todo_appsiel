<?php 

namespace App\Contabilidad\Services;

use App\Sistema\ValueObjects\TransactionPrimaryKeyVO;

use App\Contabilidad\ContabMovimiento;

use App\Core\Transactions\TraitTransactionDocument;

class AccountingMovement
{
    use TraitTransactionDocument;

	public function store( ContabMovimiento $model, $data )
	{
        $this->validate_data_fillables($model->getFillable(),$data);
        return ContabMovimiento::create( $data );
	}

    public function delete_move( TransactionPrimaryKeyVO $trans_prim_key )
    {
        ContabMovimiento::where( $trans_prim_key->tpk )->delete();
    }
}