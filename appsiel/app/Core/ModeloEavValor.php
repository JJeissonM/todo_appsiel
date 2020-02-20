<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class ModeloEavValor extends Model
{
    protected $table = 'core_eav_valores';
	protected $fillable = ['modelo_padre_id', 'registro_modelo_padre_id', 'modelo_entidad_id', 'core_campo_id', 'valor'];

	public $encabezado_tabla = ['Modelo Padre','Descripcion registro mod. padre','Entidad','Atributo','Valor','Acción'];

    public static function consultar_registros()
    {
    	return ModeloEavValor::leftJoin('sys_modelos','sys_modelos.id','=','core_eav_valores.modelo_entidad_id')
                                ->leftJoin('sys_campos','sys_campos.id','=','core_eav_valores.core_campo_id')
                                ->select(
                                            'core_eav_valores.modelo_padre_id AS campo1',
                                            'core_eav_valores.registro_modelo_padre_id AS campo2',
                                            'sys_modelos.descripcion AS campo3',
                                            'sys_campos.descripcion AS campo4',
                                            'core_eav_valores.valor AS campo5',
                                            'core_eav_valores.id AS campo6')
            ->get()
            ->toArray();
    }
}
