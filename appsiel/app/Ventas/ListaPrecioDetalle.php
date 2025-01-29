<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ListaPrecioDetalle extends Model
{
	// LOS PRECIOS SE MANEJAN CON IVA INCLUIDO
	protected $table = 'vtas_listas_precios_detalles';
	protected $fillable = ['lista_precios_id', 'inv_producto_id', 'fecha_activacion', 'precio'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Fecha activación',  'Lista de precios', 'Producto', 'Precio'];

	// Las acciones tienen valores predeterminados, si el modelo no va a tener una acción, se debe asignar la palabra "no" a la acción.
	public $urls_acciones = '{"imprimir":"no","cambiar_estado":"no","eliminar":"web_eliminar/id_fila","otros_enlaces":"no"}'; // El valor de otros_enlaces dede ser en formato JSON

	public static function consultar_registros($nro_registros, $search)
	{
		$registros = ListaPrecioDetalle::leftJoin('vtas_listas_precios_encabezados', 'vtas_listas_precios_encabezados.id', '=', 'vtas_listas_precios_detalles.lista_precios_id')
			->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_precios_detalles.inv_producto_id')
			->select(
				'vtas_listas_precios_detalles.fecha_activacion AS campo1',
				'vtas_listas_precios_encabezados.descripcion AS campo2',
				DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion) AS campo3'),
				'vtas_listas_precios_detalles.precio AS campo4',
				'vtas_listas_precios_detalles.id AS campo5'
			)
			->where("vtas_listas_precios_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion)'), "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_detalles.fecha_activacion", "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_detalles.precio", "LIKE", "%$search%")
			->orderBy('vtas_listas_precios_encabezados.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = ListaPrecioDetalle::leftJoin('vtas_listas_precios_encabezados', 'vtas_listas_precios_encabezados.id', '=', 'vtas_listas_precios_detalles.lista_precios_id')
			->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_precios_detalles.inv_producto_id')
			->select(
				'vtas_listas_precios_encabezados.descripcion AS LISTA_DE_PRECIO',
				DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion) AS PRODUCTO'),
				'vtas_listas_precios_detalles.fecha_activacion AS FECHA_ACTIVACIÓN',
				'vtas_listas_precios_detalles.precio AS PRECIO',
				'vtas_listas_precios_detalles.id AS campo5'
			)
			->where("vtas_listas_precios_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion)'), "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_detalles.fecha_activacion", "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_detalles.precio", "LIKE", "%$search%")
			->orderBy('vtas_listas_precios_encabezados.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE DETALLES DE LISTAS DE PRECIOS";
	}

	public static function get_precio_producto($lista_precios_id, $fecha_activacion, $inv_producto_id)
	{
		$registro = ListaPrecioDetalle::where('lista_precios_id', $lista_precios_id)
			->where('fecha_activacion', '<=', $fecha_activacion)
			->where('inv_producto_id', $inv_producto_id)
			->orderBy('fecha_activacion', 'ASC')
			->get()
			->last();

		if (is_null($registro)) {
			//return InvProducto::find($inv_producto_id)->precio_venta;
			return 0;
		} else {
			return $registro->precio;
		}
	}

	public static function get_precios_productos_de_la_lista($lista_precios_id)
	{
		$precios = ListaPrecioDetalle::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_precios_detalles.inv_producto_id')
			->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
			->where('vtas_listas_precios_detalles.lista_precios_id', $lista_precios_id)
			->select(
				'vtas_listas_precios_detalles.id',
				'vtas_listas_precios_detalles.precio',
				'vtas_listas_precios_detalles.fecha_activacion',
				'inv_productos.descripcion as producto_descripcion',
				'inv_productos.id as producto_codigo',
				'inv_productos.referencia',
				'inv_productos.tipo',
				'inv_productos.unidad_medida1',
				'contab_impuestos.tasa_impuesto'
			)
			->orderBy('vtas_listas_precios_detalles.fecha_activacion', 'DESC')
			->get();
		//dd( $precios );

		$productos = [];
		$i = 0;
		$precios2 = collect([]);
		foreach ($precios as $value)
		{
			if (!in_array($value->producto_codigo, $productos)) {
				$precios2[$i] = $value;
				$productos[$i] = $value->producto_codigo;
				$i++;
			}
		}

		return $precios2;
	}

	public static function get_precios_para_catalogos_pos()
	{
		$precios = ListaPrecioDetalle::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_precios_detalles.inv_producto_id')
			->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
			->select(
				'vtas_listas_precios_detalles.id',
				'vtas_listas_precios_detalles.lista_precios_id',
				'vtas_listas_precios_detalles.precio',
				'vtas_listas_precios_detalles.fecha_activacion',
				'inv_productos.descripcion as producto_descripcion',
				'inv_productos.id as producto_codigo',
				'inv_productos.referencia',
				'inv_productos.tipo',
				'inv_productos.unidad_medida1',
				'contab_impuestos.tasa_impuesto'
			)
			->orderBy('vtas_listas_precios_detalles.fecha_activacion', 'DESC')
			->get();

		$productos = [];
		$i = 0;
		$precios2 = collect([]);
		foreach ($precios as $value)
		{
			if (!in_array($value->producto_codigo, $productos)) {
				$precios2[$i] = $value;
				$productos[$i] = $value->producto_codigo;
				$i++;
			}
		}

		return $precios2;
	}
}
