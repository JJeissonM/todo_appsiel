<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class EquipoVentas extends Model
{
    protected $table = 'vtas_equipos_ventas';
	protected $fillable = ['descripcion', 'equipo_padre_id', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Equipo padre', 'Descripción', 'Estado'];
	public static function consultar_registros($nro_registros, $search)
	{
		$registros = EquipoVentas::leftJoin('vtas_equipos_ventas AS equipos_padre','equipos_padre.id', '=', 'vtas_equipos_ventas.equipo_padre_id')
									->select(
											'equipos_padre.descripcion AS campo1',
											'vtas_equipos_ventas.descripcion AS campo2',
											'vtas_equipos_ventas.estado AS campo3',
											'vtas_equipos_ventas.id AS campo4'
										)
									->where("vtas_equipos_ventas.descripcion", "LIKE", "%$search%")
									->orWhere("vtas_equipos_ventas.equipo_padre_id", "LIKE", "%$search%")
									->orWhere("vtas_equipos_ventas.estado", "LIKE", "%$search%")
									->orderBy('vtas_equipos_ventas.created_at', 'ASC')
									->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = EquipoVentas::select(
			'vtas_equipos_ventas.descripcion AS DESCRIPCIÓN',
			'vtas_equipos_ventas.equipo_padre_id AS CLASE_PADRE',
			'vtas_equipos_ventas.estado AS ESTADO'
		)
			->where("vtas_equipos_ventas.descripcion", "LIKE", "%$search%")
			->orWhere("vtas_equipos_ventas.equipo_padre_id", "LIKE", "%$search%")
			->orWhere("vtas_equipos_ventas.estado", "LIKE", "%$search%")
			->orderBy('vtas_equipos_ventas.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE EQUIPOS DE VENTAS";
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
