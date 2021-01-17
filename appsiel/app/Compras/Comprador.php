<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class Comprador extends Model
{
    protected $table = 'compras_compradores';
	protected $fillable = ['core_tercero_id', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tercero', 'Dirección', 'Telefono', 'Estado'];

	public static function consultar_registros($nro_registros, $search)
	{
		return Comprador::leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_compradores.core_tercero_id')->select(
			'core_terceros.descripcion AS campo1',
			'core_terceros.direccion1 AS campo2',
			'core_terceros.telefono1 AS campo3',
			'compras_compradores.estado AS campo4',
			'compras_compradores.id AS campo5'
		)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
			->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
			->orWhere("compras_compradores.estado", "LIKE", "%$search%")
			->orderBy('compras_compradores.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = Comprador::leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_compradores.core_tercero_id')->select(
			'core_terceros.descripcion AS TERCERO',
			'core_terceros.direccion1 AS DIRECCION',
			'core_terceros.telefono1 AS TELEFONO',
			'compras_compradores.estado AS ESTADO'
		)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("core_terceros.direccion1", "LIKE", "%$search%")
			->orWhere("core_terceros.telefono1", "LIKE", "%$search%")
			->orWhere("compras_compradores.estado", "LIKE", "%$search%")
			->orderBy('compras_compradores.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE COMPRADORES";
	}

	    public static function opciones_campo_select()
	    {
	        $opciones = Comprador::where('compras_compradores.estado','Activo')
	                    ->select('compras_compradores.id','compras_compradores.descripcion')
	                    ->get();

	        $vec['']='';
	        foreach ($opciones as $opcion)
	        {
	            $vec[$opcion->id] = $opcion->descripcion;
	        }

	        return $vec;
	    }
}
