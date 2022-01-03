<?php

namespace App\Core\Transactions;

use App\Sistema\Modelo;

use Exception;

class TransactionMovements
{
	public $model;

	function __construct( string $model_name )
	{
		$this->model = Modelo::where('modelo',$model_name)->get()->first();
        if ( $this->model == null ) {
            throw new Exception('Modelo ' . $model_name . ' no existe.');
        }
	}

	public function create( array $data )
	{
        foreach ($data as $line) {
            $this->store( $line );
        }
	}

	public function store( $data )
	{
        app( $this->model->name_space )->create( $data );
	}
}