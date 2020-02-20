<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class ExamenTieneOrganos extends Model
{
    protected $table = 'salud_examen_tiene_organos';
	protected $fillable = ['examen_id', 'organo_id', 'orden'];
	public $encabezado_tabla = ['Exámen', 'Órgano', 'Orden', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ExamenTieneOrganos::leftJoin('salud_examenes','salud_examenes.id','=','salud_examen_tiene_organos.examen_id')->leftJoin('salud_organos_del_cuerpo','salud_organos_del_cuerpo.id','=','salud_examen_tiene_organos.organo_id')->select('salud_examenes.descripcion AS campo1', 'salud_organos_del_cuerpo.descripcion AS campo2', 'salud_examen_tiene_organos.orden AS campo3', 'salud_examen_tiene_organos.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
