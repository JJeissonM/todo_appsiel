<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class ModeloRelacionado extends Model
{
    protected $table = 'sys_modelos_relacionados';
	protected $fillable = ['modelo_principal_id', 'modelo_relacionado_id', 'orden', 'tipo_modelo_relacionado', 'estado'];
	public $encabezado_tabla = ['Modelo principal', 'Modelo relacionado', 'Orden', 'Tipo modelo relacionado', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ModeloRelacionado::select('sys_modelos_relacionados.modelo_principal_id AS campo1', 'sys_modelos_relacionados.modelo_relacionado_id AS campo2', 'sys_modelos_relacionados.orden AS campo3', 'sys_modelos_relacionados.tipo_modelo_relacionado AS campo4', 'sys_modelos_relacionados.estado AS campo5', 'sys_modelos_relacionados.id AS campo6')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
