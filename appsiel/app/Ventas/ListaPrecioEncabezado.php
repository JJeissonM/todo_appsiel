<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaPrecioEncabezado extends Model
{
	protected $table = 'vtas_listas_precios_encabezados';
	protected $fillable = ['descripcion', 'impuestos_incluidos', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Impuestos incluidos', 'Estado'];

	public $vistas = '{"show":"ventas.lista_precios_show"}';

	public static function consultar_registros($nro_registros, $search)
	{
		$registros = ListaPrecioEncabezado::select(
			'vtas_listas_precios_encabezados.descripcion AS campo1',
			'vtas_listas_precios_encabezados.impuestos_incluidos AS campo2',
			'vtas_listas_precios_encabezados.estado AS campo3',
			'vtas_listas_precios_encabezados.id AS campo4'
		)
			->where("vtas_listas_precios_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_encabezados.impuestos_incluidos", "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_encabezados.estado", "LIKE", "%$search%")
			->orderBy('vtas_listas_precios_encabezados.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = ListaPrecioEncabezado::select(
			'vtas_listas_precios_encabezados.descripcion AS DESCRIPCIÓN',
			'vtas_listas_precios_encabezados.impuestos_incluidos AS IMPUESTOS_INCLUIDOS',
			'vtas_listas_precios_encabezados.estado AS ESTADO'
		)
			->where("vtas_listas_precios_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_encabezados.impuestos_incluidos", "LIKE", "%$search%")
			->orWhere("vtas_listas_precios_encabezados.estado", "LIKE", "%$search%")
			->orderBy('vtas_listas_precios_encabezados.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE LISTAS DE PRECIOS";
	}

	public static function opciones_campo_select()
	{
		$opciones = ListaPrecioEncabezado::where('vtas_listas_precios_encabezados.estado', 'Activo')
			->select('vtas_listas_precios_encabezados.id', 'vtas_listas_precios_encabezados.descripcion')
			->get();

		//$vec['']='';
		$vec = [];
		foreach ($opciones as $opcion) {
			$vec[$opcion->id] = $opcion->descripcion;
		}

		return $vec;
	}
}
