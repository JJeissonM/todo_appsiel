<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class PrecioProveedor extends Model
{
	protected $table = 'compras_precios_proveedores';
	protected $fillable = ['proveedor_id', 'producto_id', 'fecha_activacion', 'precio'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Proveedor', 'Producto', 'Fecha Activación', 'Precio'];

	public static function consultar_registros($nro_registros, $search)
	{
		return PrecioProveedor::select(
			'compras_precios_proveedores.proveedor_id AS campo1',
			'compras_precios_proveedores.producto_id AS campo2',
			'compras_precios_proveedores.fecha_activacion AS campo3',
			'compras_precios_proveedores.precio AS campo4',
			'compras_precios_proveedores.id AS campo5'
		)
			->where("compras_precios_proveedores.proveedor_id", "LIKE", "%$search%")
			->orWhere("compras_precios_proveedores.producto_id", "LIKE", "%$search%")
			->orWhere("compras_precios_proveedores.fecha_activacion", "LIKE", "%$search%")
			->orWhere("compras_precios_proveedores.precio", "LIKE", "%$search%")
			->orderBy('compras_precios_proveedores.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = PrecioProveedor::select(
			'compras_precios_proveedores.proveedor_id AS PROVEEDOR',
			'compras_precios_proveedores.producto_id AS PRODUCTO',
			'compras_precios_proveedores.fecha_activacion AS FECHA_ACTIVACIÓN',
			'compras_precios_proveedores.precio AS PRECIO'
		)
			->where("compras_precios_proveedores.proveedor_id", "LIKE", "%$search%")
			->orWhere("compras_precios_proveedores.producto_id", "LIKE", "%$search%")
			->orWhere("compras_precios_proveedores.fecha_activacion", "LIKE", "%$search%")
			->orWhere("compras_precios_proveedores.precio", "LIKE", "%$search%")
			->orderBy('compras_precios_proveedores.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE PRECIOS DE PROVEEDORES";
	}

	public static function opciones_campo_select()
	{
		$opciones = PrecioProveedor::where('compras_precios_proveedores.estado', 'Activo')
			->select('compras_precios_proveedores.id', 'compras_precios_proveedores.descripcion')
			->get();

		$vec[''] = '';
		foreach ($opciones as $opcion) {
			$vec[$opcion->id] = $opcion->descripcion;
		}

		return $vec;
	}
}
