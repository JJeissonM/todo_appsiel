<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class Consultorio extends Model
{
    protected $table = 'salud_consultorios';
	protected $fillable = ['descripcion', 'sede', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Sede', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = Consultorio::select('salud_consultorios.descripcion AS campo1', 'salud_consultorios.sede AS campo2', 'salud_consultorios.estado AS campo3', 'salud_consultorios.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = Consultorio::select('id','descripcion')
                    ->get();

        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
