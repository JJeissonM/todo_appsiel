<?php 

namespace App\Inventarios\Services;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvProducto;
use App\VentasPos\Movimiento;

class FiltroMovimientos
{
	public $movimiento;
	/*
	public function __construct()
	{
		$this->movimiento = new InvMovimiento();
	}*/

	public function filtro_entre_fechas( $fecha_ini, $fecha_fin )
	{
		if ( is_null($fecha_ini) )
		{
			$fecha_ini = InvMovimiento::first()->value('fecha');
		}
		
		$this->movimiento = $this->movimiento->whereBetween('fecha',[$fecha_ini,$fecha_fin]);
	}

	public function filtro_por_item_id( $item_id )
	{
		if( $item_id != 0 && $item_id != '' )
		{
			$this->movimiento = $this->movimiento->where('inv_producto_id', $item_id);
		}
	}

	public function filtro_por_bodega_id( $bodega_id )
	{
		if( $bodega_id != 0 && $bodega_id != '' )
		{
			$this->movimiento = $this->movimiento->where('inv_bodega_id', $bodega_id);
		}
	}

	public function filtro_por_inv_grupo_id( $inv_grupo_id )
	{
		if( $inv_grupo_id != 0 && $inv_grupo_id != '' )
		{
			$this->movimiento = $this->movimiento->leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')->leftJoin('inv_grupos','inv_grupos.id','=','inv_productos.inv_grupo_id')->where('inv_grupo_id', $inv_grupo_id)->select('inv_movimientos.*');
		}
	}

	public function aplicar_filtros( $fecha_ini, $fecha_fin, $inv_bodega_id, $inv_grupo_id, $item_id, $tipo_prenda_id )
	{
        $array_wheres = [ 
			['inv_movimientos.fecha', '<=', $fecha_fin],
			['inv_productos.estado', '=', 'Activo']
		];

        if ( $inv_grupo_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_productos.inv_grupo_id' => $inv_grupo_id] );
        }

        if ( $inv_bodega_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_movimientos.inv_bodega_id' => $inv_bodega_id] );
        }

        if ( $item_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_movimientos.inv_producto_id' => $item_id] );
        }	

		$movin_filtrado = InvMovimiento::leftJoin('inv_productos','inv_productos.id','=','inv_movimientos.inv_producto_id')
					->where($array_wheres)
					->select('inv_movimientos.*','inv_productos.estado')
					->get();

		$new_movim = collect([]);
        if( $tipo_prenda_id != 0)
        {
            foreach ($movin_filtrado as $linea_movimiento) {

                $item_mandatario = $linea_movimiento->producto->item_mandatario();
                if ( $item_mandatario != null) {
                    if( $item_mandatario->tipo_prenda_id != $tipo_prenda_id)
                    {
                        continue;
                    }
                }

                $new_movim->push($linea_movimiento);
            }

        }else{
            $new_movim = $movin_filtrado;
        }

		return $new_movim;
	}

	public function items_con_movimientos( $fecha_fin, $inv_bodega_id, array $items_ids )
	{
        $array_wheres = [ 
			['inv_movimientos.fecha', '<=', $fecha_fin]
		];

        if ( $inv_bodega_id != '' )
        {
            $array_wheres = array_merge( $array_wheres, ['inv_movimientos.inv_bodega_id' => $inv_bodega_id] );
        }

		return InvMovimiento::where($array_wheres)
					->whereIn('inv_movimientos.inv_producto_id',$items_ids)
					->select('inv_movimientos.*')
					->get();
	}

	public function get_movimiento_filtrado()
	{
		return $this->movimiento;
	}
}