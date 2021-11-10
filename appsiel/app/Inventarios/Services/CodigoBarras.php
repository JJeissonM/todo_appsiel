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
		$this->barcode = '7' . $this->formatea_item( $item_id ) . $this->formatea_color( $color_id ) . $talla->get_talla_formateada() . $referencia;
	}

	public function formatea_item( $item_id )
	{
		$largo_campo = strlen( $item_id );
        $longitud_campo = (int)config('codigo_barras.longitud_item') - $largo_campo;
        for ($i=0; $i < $longitud_campo; $i++)
        {
            $item_id = config('codigo_barras.caracter_relleno') . $item_id;
        }

        return $item_id;
	}

	public function formatea_color( $color_id )
	{
		$largo_campo = strlen( $color_id );
        $longitud_campo = (int)config('codigo_barras.longitud_color') - $largo_campo;
        for ($i=0; $i < $longitud_campo; $i++)
        {
            $color_id = config('codigo_barras.caracter_relleno') . $color_id;
        }

        return $color_id;
	}

	public function get_barcode()
	{
		// Validar codigo de barras Unico
		$item = InvProducto::where('codigo_barras',$this->barcode)->get()->first();
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
}