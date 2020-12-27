<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

class TipoModulo extends Model
{
    protected $table = 'pw_tipos_modulos';
	protected $fillable = ['descripcion', 'detalle', 'modelo', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Detalle', 'Modelo', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = TipoModulo::select('pw_tipos_modulos.descripcion AS campo1', 'pw_tipos_modulos.detalle AS campo2', 'pw_tipos_modulos.modelo AS campo3', 'pw_tipos_modulos.estado AS campo4', 'pw_tipos_modulos.id AS campo5')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
