<?php 

namespace App\Inventarios\Services;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\Inventarios\Services\TallaItem;

class CodigoBarras
{
	public $barcode;

	public function __construct( $item_id, $color_id, $talla_id, $referencia )
	{
		$talla = new TallaItem( $talla_id );
		$talla_formateada = $talla->get_talla_formateada();
		
		$item_formateado = $this->formatea_item( $item_id, $talla_formateada, $referencia );

		// El codigo de barras se almacena con 13 digitos
		$this->barcode = '7' . $item_formateado . $talla_formateada . $referencia . $this->ean13_checksum( '7' . $item_formateado . $talla_formateada . $referencia );
	}

	public function formatea_item( $item_id, $talla, $referencia )
	{
		// EAN13: la longitud del codigo de barras debe ser de 13 caracteres NUMERICOS
		$longitud_ean13 = 11; // Se le agregará un 7 al comienzo y el dígito de control al final para completar los 13 digitos

		$largo_campo = strlen( $item_id );

        $longitud_campo = $longitud_ean13 - $largo_campo - strlen($talla) - strlen($referencia);

        //dd( $longitud_ean13, $largo_campo, strlen($talla), $referencia, strlen($referencia), $longitud_campo);

        for ($i=0; $i < $longitud_campo; $i++)
        {
            $item_id = config('inventarios.caracter_relleno') . $item_id;
        }

        return $item_id;
	}

	// NO se esta usando
	public function formatea_color( $color_id )
	{
		$largo_campo = strlen( $color_id );
        $longitud_campo = (int)config('inventarios.longitud_color') - $largo_campo;
        for ($i=0; $i < $longitud_campo; $i++)
        {
            $color_id = config('inventarios.caracter_relleno') . $color_id;
        }

        return $color_id;
	}

	public function get_barcode( $item_id )
	{
		//dd($this->barcode);
		// Validar codigo de barras Unico
		$item = InvProducto::where([
									['id','<>', $item_id ],
									['codigo_barras','=',$this->barcode]
								])->get()->first();
		if( !is_null( $item ) )
		{
			$barcode_item = explode( '-', $item->codigo_barras );
			$numero_siguiente = 1;
			if ( isset( $barcode_item[1] ) )
			{
				$numero_siguiente = (int)$barcode_item[1] + 1;
			}
			$this->barcode .= '-' . $numero_siguiente;
		}

        return $this->barcode;
	}

	// Cálculo del dígito de control EAN
	public function ean13_checksum($codigo)
	{
	  $checksum = 0;
	  foreach (str_split(strrev($codigo)) as $pos => $val) {
	    $checksum += $val * (3 - 2 * ($pos % 2));
	  }
	  return ((10 - ($checksum % 10)) % 10);
	}
}