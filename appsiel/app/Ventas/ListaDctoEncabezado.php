<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaDctoEncabezado extends Model
{
    protected $table = 'vtas_listas_dctos_encabezados';
	protected $fillable = ['descripcion', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ListaDctoEncabezado::select('vtas_listas_dctos_encabezados.descripcion AS campo1', 'vtas_listas_dctos_encabezados.estado AS campo2', 'vtas_listas_dctos_encabezados.id AS campo3')
	    ->get()
	    ->toArray();
	    return $registros;
	}
    public static function opciones_campo_select()
    {
        $opciones = ListaDctoEncabezado::where('vtas_listas_dctos_encabezados.estado','Activo')
                    ->select('vtas_listas_dctos_encabezados.id','vtas_listas_dctos_encabezados.descripcion')
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
