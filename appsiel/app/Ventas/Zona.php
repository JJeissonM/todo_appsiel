<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'vtas_zonas';
	protected $fillable = ['descripcion', 'zona_padre_id', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Zona padre', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = Zona::select('vtas_zonas.descripcion AS campo1', 'vtas_zonas.zona_padre_id AS campo2', 'vtas_zonas.estado AS campo3', 'vtas_zonas.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = Zona::where('vtas_zonas.estado','Activo')
                    ->select('vtas_zonas.id','vtas_zonas.descripcion')
                    ->get();

        //$vec['']='';
        $vec = [];
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
