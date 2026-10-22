<?php 

namespace App\Inventarios\Services;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\VentasPos\Movimiento;
use Illuminate\Support\Facades\Schema;

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

        $prefijo = isset($filters->prefijo_referencia_id) ? trim((string)$filters->prefijo_referencia_id) : '';
        $tipoPrenda = isset($filters->tipo_prenda_id) ? trim((string)$filters->tipo_prenda_id) : '';
        $query = InvProducto::where($array_wheres);

        // Los formularios pueden enviar filtros vacíos en instalaciones sin indumentaria.
        if ($prefijo === '' && $tipoPrenda === '') {
            return $query->orderBy('descripcion')->get();
        }

        $columnas = [
            'inv_mandatario_tiene_items' => ['item_id', 'mandatario_id'],
            'inv_items_mandatarios' => ['id', 'descripcion']
        ];
        if ($prefijo !== '') {
            $columnas['inv_items_mandatarios'][] = 'prefijo_referencia_id';
            $columnas['inv_indum_prefijos_referencias'] = ['id'];
        }
        if ($tipoPrenda !== '') {
            $columnas['inv_items_mandatarios'][] = 'tipo_prenda_id';
            $columnas['inv_indum_tipos_prendas'] = ['id'];
        }
        foreach ($columnas as $tabla => $campos) {
            foreach ($campos as $campo) {
                if (!Schema::hasColumn($tabla, $campo)) {
                    throw new \InvalidArgumentException('No se puede aplicar el filtro de indumentaria: falta ' . $tabla . '.' . $campo . '.');
                }
            }
        }

        $query->leftJoin('inv_mandatario_tiene_items', 'inv_mandatario_tiene_items.item_id', '=', 'inv_productos.id')
            ->leftJoin('inv_items_mandatarios', 'inv_items_mandatarios.id', '=', 'inv_mandatario_tiene_items.mandatario_id');
        if ($prefijo !== '') {
            $query->leftJoin('inv_indum_prefijos_referencias', 'inv_indum_prefijos_referencias.id', '=', 'inv_items_mandatarios.prefijo_referencia_id')
                ->where('inv_indum_prefijos_referencias.id', (int)$prefijo);
        }
        if ($tipoPrenda !== '') {
            $query->leftJoin('inv_indum_tipos_prendas', 'inv_indum_tipos_prendas.id', '=', 'inv_items_mandatarios.tipo_prenda_id')
                ->where('inv_indum_tipos_prendas.id', (int)$tipoPrenda);
        }
        return $query->select('inv_productos.*', 'inv_items_mandatarios.descripcion AS descripcion_prenda')
            ->orderBy('descripcion_prenda')->get();
    }
}
