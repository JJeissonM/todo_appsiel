<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaDctoDetalle extends Model
{
	protected $table = 'vtas_listas_dctos_detalles';
	protected $fillable = ['lista_descuentos_id', 'inv_producto_id', 'fecha_activacion', 'descuento1', 'descuento2'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Lista de descuentos', 'Producto', 'Fecha activación', '% Dcto.'];
	public static function consultar_registros($nro_registros, $search)
	{
		return ListaDctoDetalle::leftJoin('vtas_listas_dctos_encabezados', 'vtas_listas_dctos_encabezados.id', '=', 'vtas_listas_dctos_detalles.lista_descuentos_id')
			->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_dctos_detalles.inv_producto_id')
			->select(
				'vtas_listas_dctos_encabezados.descripcion AS campo1',
				'inv_productos.descripcion AS campo2',
				'vtas_listas_dctos_detalles.fecha_activacion AS campo3',
				'vtas_listas_dctos_detalles.descuento1 AS campo4',				'vtas_listas_dctos_detalles.id AS campo5'
			)
			->where("vtas_listas_dctos_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
			->orWhere("vtas_listas_dctos_detalles.fecha_activacion", "LIKE", "%$search%")
			->orWhere("vtas_listas_dctos_detalles.descuento1", "LIKE", "%$search%")
			->orderBy('vtas_listas_dctos_detalles.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = ListaDctoDetalle::leftJoin('vtas_listas_dctos_encabezados', 'vtas_listas_dctos_encabezados.id', '=', 'vtas_listas_dctos_detalles.lista_descuentos_id')
			->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_dctos_detalles.inv_producto_id')
			->select(
				'vtas_listas_dctos_encabezados.descripcion AS LISTA_DE_DESCUENTOS',
				'inv_productos.descripcion AS PRODUCTO',
				'vtas_listas_dctos_detalles.fecha_activacion AS FECHA_ACTIVACIÓN',
				'vtas_listas_dctos_detalles.descuento1 AS DCTO_1'
			)
			->where("vtas_listas_dctos_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("inv_productos.descripcion", "LIKE", "%$search%")
			->orWhere("vtas_listas_dctos_detalles.fecha_activacion", "LIKE", "%$search%")
			->orWhere("vtas_listas_dctos_detalles.descuento1", "LIKE", "%$search%")
			->orderBy('vtas_listas_dctos_detalles.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE DETALLES DE LISTAS DE DESCUENTO";
	}

	public static function get_descuento_producto($lista_descuentos_id, $fecha_activacion, $inv_producto_id)
	{
		$registro = ListaDctoDetalle::where('lista_descuentos_id', $lista_descuentos_id)
			->where('fecha_activacion', '<=', $fecha_activacion)
			->where('inv_producto_id', $inv_producto_id)
			->get()
			->last();

		if (is_null($registro)) {
			return 0;
		} else {
			return $registro->descuento1;
		}
	}

	public static function get_descuentos_productos_de_la_lista($lista_descuentos_id)
	{		
        $array_wheres = [
            ['vtas_listas_dctos_detalles.id','>', 0]
        ];

        if ($lista_descuentos_id != null ) {
            $array_wheres = array_merge($array_wheres,[['vtas_listas_dctos_detalles.lista_descuentos_id','=', $lista_descuentos_id]]);
        }

		$descuentos = ListaDctoDetalle::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_listas_dctos_detalles.inv_producto_id')
			->leftJoin('contab_impuestos', 'contab_impuestos.id', '=', 'inv_productos.impuesto_id')
			->where($array_wheres)
			->select(
				'vtas_listas_dctos_detalles.id',
				'vtas_listas_dctos_detalles.lista_descuentos_id',
				'vtas_listas_dctos_detalles.descuento1',
				'vtas_listas_dctos_detalles.fecha_activacion',
				'inv_productos.descripcion as producto_descripcion',
				'inv_productos.id as producto_codigo',
				'inv_productos.tipo',
				'inv_productos.unidad_medida1',
				'contab_impuestos.tasa_impuesto'
			)
			->orderBy('vtas_listas_dctos_detalles.fecha_activacion', 'DESC')
			->get();
		//dd( $precios );

		$productos = [];
		$i = 0;
		$descuentos2 = collect([]);
		foreach ($descuentos as $value) {
			if (!in_array($value->producto_codigo, $productos)) {
				$descuentos2[$i] = $value;
				$productos[$i] = $value->producto_codigo;
				$i++;
			}
		}

		return $descuentos2;
	}
}
