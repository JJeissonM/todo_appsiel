<?php 

namespace App\Inventarios\Services;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\VentasPos\Movimiento;

class ItemsFiltersServices
{
	public function get_listado_de_items( $filters, $solo_activos = false )
	{
        if ( $filters->item_id != '' )
        {
            $query = InvProducto::where('id', (int)$filters->item_id );

            if ( $solo_activos ) {
                $query->where('estado', 'Activo');
            }

            return $query->get();
        }

        $array_wheres = [
			['inv_productos.id', '>', 0]
		];

        if ( $solo_activos )
        {
            $array_wheres = array_merge( $array_wheres, [ 'inv_productos.estado' => 'Activo' ] );
        }

        if ( $filters->grupo_inventario_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, [ 'inv_productos.inv_grupo_id' => (int)$filters->grupo_inventario_id ] );
        }

		$mandatarios = false;
		if ( isset($filters->prefijo_referencia_id) || isset($filters->tipo_prenda_id) )
        {
			$mandatarios = true;
			if ( $filters->prefijo_referencia_id != '' )
			{
				$array_wheres = array_merge( $array_wheres, [ 'inv_indum_prefijos_referencias.id' => (int)$filters->prefijo_referencia_id ] );
			}
			
			if ( $filters->tipo_prenda_id != '' )
			{
				$array_wheres = array_merge( $array_wheres, [ 'inv_indum_tipos_prendas.id' => (int)$filters->tipo_prenda_id ] );
			}
        }

		if ( !$mandatarios )
		{
			return InvProducto::where($array_wheres)
								->orderBy('descripcion')
								->get();
		}

		$items = InvProducto::leftJoin('inv_mandatario_tiene_items', 'inv_mandatario_tiene_items.item_id', '=', 'inv_productos.id')
							->leftJoin('inv_items_mandatarios', 'inv_items_mandatarios.id', '=', 'inv_mandatario_tiene_items.mandatario_id')
							->leftJoin('inv_indum_prefijos_referencias', 'inv_indum_prefijos_referencias.id', '=', 'inv_items_mandatarios.prefijo_referencia_id')
							->leftJoin('inv_indum_tipos_prendas', 'inv_indum_tipos_prendas.id', '=', 'inv_items_mandatarios.tipo_prenda_id')
							->where($array_wheres)
							->select('inv_productos.*', 'inv_items_mandatarios.descripcion AS descripcion_prenda')
							->orderBy('descripcion_prenda')
							->get();

		return $items;
	}
}
