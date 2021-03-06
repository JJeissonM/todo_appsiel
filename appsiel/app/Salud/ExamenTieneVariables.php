<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class ExamenTieneVariables extends Model
{
	protected $table = 'salud_examen_tiene_variables';
	protected $fillable = ['examen_id', 'variable_id', 'orden', 'tipo_campo'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Exámen', 'Variable', 'Orden', 'Tipo de campo'];

	public static function consultar_registros($nro_registros, $search)
	{
		$registros = ExamenTieneVariables::leftJoin('salud_examenes', 'salud_examenes.id', '=', 'salud_examen_tiene_variables.examen_id')->leftJoin('salud_catalogo_variables_examenes', 'salud_catalogo_variables_examenes.id', '=', 'salud_examen_tiene_variables.variable_id')
			->select(
				'salud_examenes.descripcion AS campo1',
				'salud_catalogo_variables_examenes.descripcion AS campo2',
				'salud_examen_tiene_variables.orden AS campo3',
				'salud_examen_tiene_variables.tipo_campo AS campo4',
				'salud_examen_tiene_variables.id AS campo5'
			)
			->where("salud_examenes.descripcion", "LIKE", "%$search%")
			->orWhere("salud_catalogo_variables_examenes.descripcion", "LIKE", "%$search%")
			->orWhere("salud_examen_tiene_variables.orden", "LIKE", "%$search%")
			->orWhere("salud_examen_tiene_variables.tipo_campo", "LIKE", "%$search%")
			->orderBy('salud_examen_tiene_variables.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = ExamenTieneVariables::leftJoin('salud_examenes', 'salud_examenes.id', '=', 'salud_examen_tiene_variables.examen_id')->leftJoin('salud_catalogo_variables_examenes', 'salud_catalogo_variables_examenes.id', '=', 'salud_examen_tiene_variables.variable_id')
			->select(
				'salud_examenes.descripcion AS EXÁMEN',
				'salud_catalogo_variables_examenes.descripcion AS VARIABLE',
				'salud_examen_tiene_variables.orden AS ORDEN',
				'salud_examen_tiene_variables.tipo_campo AS TIPO_DE_CAMPO'
			)
			->where("salud_examenes.descripcion", "LIKE", "%$search%")
			->orWhere("salud_catalogo_variables_examenes.descripcion", "LIKE", "%$search%")
			->orWhere("salud_examen_tiene_variables.orden", "LIKE", "%$search%")
			->orWhere("salud_examen_tiene_variables.tipo_campo", "LIKE", "%$search%")
			->orderBy('salud_examen_tiene_variables.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE ASIGNAR VARIABLES A EXÁMEN";
	}
}
