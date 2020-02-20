<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class ExamenTieneVariables extends Model
{
    protected $table = 'salud_examen_tiene_variables';
	protected $fillable = ['examen_id', 'variable_id', 'orden', 'tipo_campo'];
	public $encabezado_tabla = ['Exámen', 'Variable', 'Orden', 'Tipo de campo', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ExamenTieneVariables::leftJoin('salud_examenes','salud_examenes.id','=','salud_examen_tiene_variables.examen_id')->leftJoin('salud_catalogo_variables_examenes','salud_catalogo_variables_examenes.id','=','salud_examen_tiene_variables.variable_id')->select('salud_examenes.descripcion AS campo1', 'salud_catalogo_variables_examenes.descripcion AS campo2', 'salud_examen_tiene_variables.orden AS campo3', 'salud_examen_tiene_variables.tipo_campo AS campo4', 'salud_examen_tiene_variables.id AS campo5')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
