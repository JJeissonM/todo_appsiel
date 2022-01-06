<?php 

namespace App\CxC;

use App\Sistema\ValueObjects\TransactionPrimaryKeyVO;

use App\CxC\CxcAbono;

use App\Core\Transactions\TraitTransactionDocument;

class AccountsReceivablePayment
{
    use TraitTransactionDocument;

	public function store( CxcAbono $model, $data )
	{
        $this->validate_data_fillables($model->getFillable(),$data);
        return CxcAbono::create( $data );
	}

    public function delete_payment( TransactionPrimaryKeyVO $trans_prim_key )
    {
        CxcAbono::where( $trans_prim_key->tpk )->delete();
    }
}