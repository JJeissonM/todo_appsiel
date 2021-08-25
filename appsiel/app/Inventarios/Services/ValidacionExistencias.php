<?php 

namespace App\Inventarios\Services;

use App\Inventarios\InvMovimiento;

class ValidacionExistencias
{
	private $fecha_corte;
	private $item_id;
	private $bodega_id;
	//private $existencia_item;

	public function __construct( $bodega_id, $fecha_corte )
	{
		$this->fecha_corte = \Carbon\Carbon::parse( $fecha_corte )->format('Y-m-d');
		$this->bodega_id = $bodega_id;
		
	}

	public function set_item($item_id)
	{
		$this->item_id = $item_id;
		return $this;
	}

    public function get_existencia()
    {        
        return InvMovimiento::get_cantidad_existencia_item( $this->item_id, $this->bodega_id, $this->fecha_corte );
    }

    public function existencia_es_negativa( $existencia_item )
    {
    	if ( $existencia_item >= 0 )
    	{
    		return false;
    	}

    	return true;
    }

	public function lista_items_con_existencias_negativas( array $lista_comparacion )
	{
		$lista_items = [];
		foreach( $lista_comparacion as $item_id => $cantidad_a_disminuir )
		{
			$existencia = $this->set_item( $item_id )->get_existencia();
			
			$nuevo_saldo = $existencia - $cantidad_a_disminuir;
			
			if ( $this->existencia_es_negativa( $nuevo_saldo ) )
			{
				$lista_items[] = (object)[ 
											'item_id' => $item_id,
											'existencia' => $existencia,
											'cantidad_a_disminuir' => $cantidad_a_disminuir,
											'nuevo_saldo' => $nuevo_saldo
										];
			}
		}
		
		return $lista_items;
	}

}