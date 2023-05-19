<?php 

namespace App\VentasPos\Services;

use App\Inventarios\Services\ValidacionExistencias;
use App\Inventarios\InvProducto;
use App\Inventarios\InvBodega;

use \View;

class ServiciosInventarios
{
	public static function tabla_items_existencias_negativas( $bodega_id, $fecha_corte, $lista_items )
	{
		$obj = new ValidacionExistencias( $bodega_id, $fecha_corte );
        $lista_items_existencia_negativa = $obj->lista_items_con_existencias_negativas( $lista_items );
        $items = [];
        foreach ($lista_items_existencia_negativa as $linea)
        {
        	$item = InvProducto::find($linea->item_id);

        	if ( $item->tipo == 'servicio' )
        	{
        		continue;
        	}
        	
        	$obj_aux = (object)[ 
						'referencia' => $linea->referencia,
						'item_id' => $linea->item_id,
						'descripcion' => $item->descripcion,
						'existencia' => $linea->existencia,
						'cantidad_facturada' => $linea->cantidad_a_disminuir,
						'nuevo_saldo' => $linea->nuevo_saldo
						];
			$items[] = $obj_aux;
        }

        if ( empty($items) )
        {
        	return 1;
        }

        $bodega = InvBodega::find($bodega_id);		

		if(config('inventarios.codigo_principal_manejo_productos') != 'referencia')
			$lbl_code = 'Cód.';
	
		if(config('inventarios.codigo_principal_manejo_productos') == 'referencia')
			$lbl_code = 'Ref.';

        $lbl_encabezados = [$lbl_code, 'Item', 'Existencia', 'Cant. Facturada', 'Nuevo saldo'];

        return View::make( 'inventarios.incluir.cantidad_existencias_tabla', compact( 'bodega', 'fecha_corte', 'lbl_encabezados', 'items' ) )->render();
	}
}