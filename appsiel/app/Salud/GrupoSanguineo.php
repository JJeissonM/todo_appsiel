<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use DB;

class GrupoSanguineo extends Model
{
    protected $table = 'core_eav_valores';

	protected $fillable = ['modelo_padre_id', 'registro_modelo_padre_id', 'modelo_entidad_id', 'core_campo_id', 'valor'];
	
	public $encabezado_tabla = ['Descripción', 'Acción'];

	public static function consultar_registros()
	{
		$modelo_padre_id = 224; // Grupos Sanguineos
	    return GrupoSanguineo::leftJoin('sys_campos', 'sys_campos.id', '=', 'core_eav_valores.core_campo_id')
	    			->where('core_eav_valores.modelo_padre_id',$modelo_padre_id)
                    ->select(
                    			'core_eav_valores.valor AS campo1',
                    			'core_eav_valores.id AS campo2')
				    ->get()
				    ->toArray();
	}
}
