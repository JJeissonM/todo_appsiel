<?php

namespace App\PaginaWeb;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'pw_menus';
	protected $fillable = ['descripcion', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = Menu::select('pw_menus.descripcion AS campo1', 'pw_menus.estado AS campo2', 'pw_menus.id AS campo3')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = Menu::select('id','descripcion')
                    ->get();
        
        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
