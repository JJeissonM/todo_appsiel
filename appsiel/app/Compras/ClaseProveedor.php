<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use DB;

class ClaseProveedor extends Model
{
	protected $table = 'compras_clases_proveedores';

	protected $fillable = ['descripcion', 'cta_x_pagar_id', 'cta_anticipo_id', 'clase_padre_id', 'estado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Cta x pagar default', 'Cta anticipo default', 'Clase padre', 'Estado'];

	public static function consultar_registros($nro_registros, $search)
	{
		$registros = ClaseProveedor::leftJoin('contab_cuentas as cta_x_pagar', 'cta_x_pagar.id', '=', 'compras_clases_proveedores.cta_x_pagar_id')
			->leftJoin('contab_cuentas as cta_anticipo', 'cta_anticipo.id', '=', 'compras_clases_proveedores.cta_anticipo_id')
			->select(
				'compras_clases_proveedores.descripcion AS campo1',
				DB::raw('CONCAT(cta_x_pagar.codigo," ",cta_x_pagar.descripcion) AS campo2'),
				DB::raw('CONCAT(cta_anticipo.codigo," ",cta_anticipo.descripcion) AS campo3'),
				'compras_clases_proveedores.clase_padre_id AS campo4',
				'compras_clases_proveedores.estado AS campo5',
				'compras_clases_proveedores.id AS campo6'
			)
			->where("compras_clases_proveedores.descripcion", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(cta_x_pagar.codigo," ",cta_x_pagar.descripcion)'), "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(cta_anticipo.codigo," ",cta_anticipo.descripcion)'), "LIKE", "%$search%")
			->orWhere("compras_clases_proveedores.clase_padre_id", "LIKE", "%$search%")
			->orWhere("compras_clases_proveedores.estado", "LIKE", "%$search%")
			->orderBy('compras_clases_proveedores.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = ClaseProveedor::leftJoin('contab_cuentas as cta_x_pagar', 'cta_x_pagar.id', '=', 'compras_clases_proveedores.cta_x_pagar_id')
			->leftJoin('contab_cuentas as cta_anticipo', 'cta_anticipo.id', '=', 'compras_clases_proveedores.cta_anticipo_id')
			->select(
				'compras_clases_proveedores.descripcion AS DESCRIPCIÓN',
				DB::raw('CONCAT(cta_x_pagar.codigo," ",cta_x_pagar.descripcion) AS CTA_X_PAGAR_DEFAULT'),
				DB::raw('CONCAT(cta_anticipo.codigo," ",cta_anticipo.descripcion) AS CTA_ANTICIPO_DEFAULT'),
				'compras_clases_proveedores.clase_padre_id AS CLASE_PADRE',
				'compras_clases_proveedores.estado AS ESTADO'
			)
			->where("compras_clases_proveedores.descripcion", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(cta_x_pagar.codigo," ",cta_x_pagar.descripcion)'), "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(cta_anticipo.codigo," ",cta_anticipo.descripcion)'), "LIKE", "%$search%")
			->orWhere("compras_clases_proveedores.clase_padre_id", "LIKE", "%$search%")
			->orWhere("compras_clases_proveedores.estado", "LIKE", "%$search%")
			->orderBy('compras_clases_proveedores.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE CLASES DE PROVEEDORES";
	}

	public static function opciones_campo_select()
	{
		$opciones = ClaseProveedor::where('compras_clases_proveedores.estado', 'Activo')
			->select('compras_clases_proveedores.id', 'compras_clases_proveedores.descripcion')
			->get();

		$vec[''] = '';
		foreach ($opciones as $opcion) {
			$vec[$opcion->id] = $opcion->descripcion;
		}

		return $vec;
	}
}
