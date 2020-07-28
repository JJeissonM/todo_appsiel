<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaDctoDetalle extends Model
{
    protected $table = 'vtas_listas_dctos_detalles';
	protected $fillable = ['lista_descuentos_id', 'inv_producto_id', 'fecha_activacion', 'descuento1', 'descuento2'];
	public $encabezado_tabla = ['Lista de descuentos', 'Producto', 'Fecha activación', 'Dcto. 1', 'Dcto. 2', 'Acción'];
	public static function consultar_registros()
	{
	    return ListaDctoDetalle::leftJoin('vtas_listas_dctos_encabezados', 'vtas_listas_dctos_encabezados.id', '=', 'vtas_listas_dctos_detalles.lista_descuentos_id')
	    							->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_dctos_detalles.inv_producto_id')
	    							->select(
	    									'vtas_listas_dctos_encabezados.descripcion AS campo1',
	    									'inv_productos.descripcion AS campo2',
	    									'vtas_listas_dctos_detalles.fecha_activacion AS campo3',
	    									'vtas_listas_dctos_detalles.descuento1 AS campo4',
	    									'vtas_listas_dctos_detalles.descuento2 AS campo5',
	    									'vtas_listas_dctos_detalles.id AS campo6')
								    ->get()
								    ->toArray();
	}


	public static function get_descuento_producto( $lista_descuentos_id, $fecha_activacion, $inv_producto_id )
	{
		$registro = ListaDctoDetalle::where('lista_descuentos_id', $lista_descuentos_id)
									->where('fecha_activacion', '<=', $fecha_activacion)
									->where('inv_producto_id', $inv_producto_id)
									->get()
									->last();

		if ( is_null($registro) )
		{
			return 0;
		}else{
			return $registro->descuento1;
		}
	}

	public static function get_descuentos_productos_de_la_lista( $lista_descuentos_id )
	{
		$descuentos = ListaDctoDetalle::leftJoin('inv_productos','inv_productos.id','=','vtas_listas_dctos_detalles.inv_producto_id')
								->leftJoin('contab_impuestos','contab_impuestos.id','=','inv_productos.impuesto_id')
								->where('vtas_listas_dctos_detalles.lista_descuentos_id', $lista_descuentos_id)
								->select(
											'vtas_listas_dctos_detalles.id',
											'vtas_listas_dctos_detalles.descuento1',
											'vtas_listas_dctos_detalles.fecha_activacion',
											'inv_productos.descripcion as producto_descripcion',
											'inv_productos.id as producto_codigo',
											'inv_productos.tipo',
											'inv_productos.unidad_medida1',
											'contab_impuestos.tasa_impuesto')
                    			->orderBy('vtas_listas_dctos_detalles.fecha_activacion','DESC')
								->get();
		//dd( $precios );

		$productos = [];
		$i = 0;
		$descuentos2 = collect( [] );
		foreach ($descuentos as $value)
		{
			if ( !in_array( $value->producto_codigo, $productos) )
			{
				$descuentos2[$i] = $value;
				$productos[$i] = $value->producto_codigo;
				$i++;
			}
		}

		return $descuentos2;
	}
}
