<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class ExamenTieneOrganos extends Model
{
	protected $table = 'salud_examen_tiene_organos';
	protected $fillable = ['examen_id', 'organo_id', 'orden'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Exámen', 'Órgano', 'Orden'];

	public static function consultar_registros($nro_registros, $search)
	{
		$registros = ExamenTieneOrganos::leftJoin('salud_examenes', 'salud_examenes.id', '=', 'salud_examen_tiene_organos.examen_id')->leftJoin('salud_organos_del_cuerpo', 'salud_organos_del_cuerpo.id', '=', 'salud_examen_tiene_organos.organo_id')->select(
			'salud_examenes.descripcion AS campo1',
			'salud_organos_del_cuerpo.descripcion AS campo2',
			'salud_examen_tiene_organos.orden AS campo3',
			'salud_examen_tiene_organos.id AS campo4'
		)
			->where("salud_examenes.descripcion", "LIKE", "%$search%")
			->orWhere("salud_organos_del_cuerpo.descripcion", "LIKE", "%$search%")
			->orWhere("salud_examen_tiene_organos.orden", "LIKE", "%$search%")
			->orderBy('salud_examen_tiene_organos.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}
	public static function sqlString($search)
	{
		$string = ExamenTieneOrganos::leftJoin('salud_examenes', 'salud_examenes.id', '=', 'salud_examen_tiene_organos.examen_id')->leftJoin('salud_organos_del_cuerpo', 'salud_organos_del_cuerpo.id', '=', 'salud_examen_tiene_organos.organo_id')->select(
			'salud_examenes.descripcion AS EXÁMEN',
			'salud_organos_del_cuerpo.descripcion AS ÓRGANO',
			'salud_examen_tiene_organos.orden AS ORDEN'
		)
			->where("salud_examenes.descripcion", "LIKE", "%$search%")
			->orWhere("salud_organos_del_cuerpo.descripcion", "LIKE", "%$search%")
			->orWhere("salud_examen_tiene_organos.orden", "LIKE", "%$search%")
			->orderBy('salud_examen_tiene_organos.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE ASIGNAR ÓRGANOS A EXÁMEN";
	}
}
