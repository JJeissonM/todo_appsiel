<?php

namespace App\Core\Transactions;

use Exception;

trait TraitTransactionDocument
{
	public function validate_data_fillables($fillables,$data)
    {
        $missed_fields = '';
        $data_keys = array_keys($data);
        foreach ($fillables as $key => $value) {
            if (!in_array($value,$data_keys)) {
                $missed_fields .= ',' . $value;
            }
        }

        if ($missed_fields!='') {
            throw new Exception('Faltan los siguientes campos en Los datos enviados: ' . $missed_fields);
        }
    }
}