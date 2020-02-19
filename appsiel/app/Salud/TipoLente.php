<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class TipoLente extends Model
{
    protected $table = 'salud_tipo_lentes';
	protected $fillable = ['descripcion','estado'];
	public $encabezado_tabla = ['Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = TipoLente::select('salud_tipo_lentes.descripcion AS campo1', 'salud_tipo_lentes.estado AS campo2', 'salud_tipo_lentes.id AS campo3')
	    ->get()
	    ->toArray();
	    return $registros;
	}
    

    public static function opciones_campo_select()
    {
        $opciones = TipoLente::select('salud_tipo_lentes.id','salud_tipo_lentes.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}