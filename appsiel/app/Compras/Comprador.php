<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class Comprador extends Model
{
    protected $table = 'compras_compradores';
	protected $fillable = ['core_tercero_id', 'estado'];
	public $encabezado_tabla = ['Tercero', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = Comprador::leftJoin('core_terceros','core_terceros.id','=','compras_compradores.core_tercero_id')->select('core_terceros.descripcion AS campo1', 'core_terceros.direccion1 AS campo2', 'core_terceros.telefono1 AS campo3', 'compras_compradores.estado AS campo4', 'compras_compradores.id AS campo5')
	    ->get()
	    ->toArray();
	    return $registros;
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
