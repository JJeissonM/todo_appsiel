<?php 

namespace App\Inventarios\Services;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;

class TallaItem
{
	public $talla;

	public function __construct( $talla_id )
	{
		$this->talla = $talla_id;
	}

	public function get_talla_formateada()
	{
		$this->convertir_mayusculas();
		$this->convertir_a_numero();

		$largo_campo = strlen( $this->talla );
        $longitud_campo = (int)config('codigo_barras.longitud_talla') - $largo_campo;
        for ($i=0; $i < $longitud_campo; $i++)
        {
            $this->talla = config('codigo_barras.caracter_relleno') . $this->talla;
        }

        return $this->talla;
	}

	public function convertir_mayusculas()
	{
		if( !is_numeric( $this->talla ) )
		{
			$this->talla = strtoupper( $this->talla );
		}

		return $this->talla;
	}

	public function convertir_a_numero()
	{
		if ( is_numeric($this->talla) )
		{
			return false;
		}
		
		switch( $this->talla )
		{
			case 'S':
				$this->talla = 1;
				break;
			case 'M':
				$this->talla = 2;
				break;
			case 'L':
				$this->talla = 3;
				break;
			case 'XS':
				$this->talla = 4;
				break;
			case 'XL':
				$this->talla = 5;
				break;
			case 'XXL':
				$this->talla = 6;
				break;

			default:
				$this->talla = 99;
				break;
		}
	}
}