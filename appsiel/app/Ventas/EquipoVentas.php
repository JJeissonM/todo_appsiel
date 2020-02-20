<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class EquipoVentas extends Model
{
    protected $table = 'vtas_equipos_ventas';
	protected $fillable = ['descripcion', 'equipo_padre_id', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Clase padre', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = EquipoVentas::select('vtas_equipos_ventas.descripcion AS campo1', 'vtas_equipos_ventas.equipo_padre_id AS campo2', 'vtas_equipos_ventas.estado AS campo3', 'vtas_equipos_ventas.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}
	public static function opciones_campo_select()
	{
	    $opciones = EquipoVentas::where('vtas_equipos_ventas.estado','Activo')
	                ->select('vtas_equipos_ventas.id','vtas_equipos_ventas.descripcion')
	                ->get();

	    $vec['']='';
	    foreach ($opciones as $opcion)
	    {
	        $vec[$opcion->id] = $opcion->descripcion;
	    }

	    return $vec;
	}
}
