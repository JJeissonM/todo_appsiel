<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class ValorRegistroModeloRelacionado extends Model
{
    protected $table = 'core_valores_relacionados';
	protected $fillable = ['modelo_principal_id', 'registro_modelo_principal_id', 'modelo_relacionado_id', 'core_campo_id', 'valor'];
	public $encabezado_tabla = ['Nombre completo', 'Especialidad', 'Registro médico', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ValorRegistroModeloRelacionado::select('core_valores_relacionados.modelo_principal_id AS campo1', 'core_valores_relacionados.registro_modelo_principal_id AS campo2', 'core_valores_relacionados.modelo_relacionado_id AS campo3', 'core_valores_relacionados.core_campo_id AS campo4', 'core_valores_relacionados.id AS campo5')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
