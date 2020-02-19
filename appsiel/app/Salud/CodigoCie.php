<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class CodigoCie extends Model
{
    protected $table = 'salud_cie10';
	protected $fillable = ['codigo', 'descripcion', 'estado'];
	public $encabezado_tabla = ['Código', 'Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = CodigoCie::select('salud_cie10.codigo AS campo1', 'salud_cie10.descripcion AS campo2', 'salud_cie10.estado AS campo3', 'salud_cie10.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
