<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;

class Vendedor extends Model
{
    protected $table = 'vtas_vendedores';
	protected $fillable = ['core_tercero_id', 'equipo_ventas_id', 'clase_vendedor_id', 'estado'];
	public $encabezado_tabla = ['Tercero', 'Equipo de ventas', 'Clase de vendedor', 'Estado', 'Acción'];
    
	public static function consultar_registros()
	{
	    $registros = Vendedor::leftJoin('core_terceros','core_terceros.id','=','vtas_vendedores.core_tercero_id')->leftJoin('vtas_equipos_ventas','vtas_equipos_ventas.id','=','vtas_vendedores.equipo_ventas_id')->leftJoin('vtas_clases_vendedores','vtas_clases_vendedores.id','=','vtas_vendedores.clase_vendedor_id')->select('core_terceros.descripcion AS campo1', 'vtas_equipos_ventas.descripcion AS campo2', 'vtas_clases_vendedores.descripcion AS campo3', 'vtas_vendedores.estado AS campo4', 'vtas_vendedores.id AS campo5')
	    ->get()
	    ->toArray();
	    return $registros;
	}
    public static function opciones_campo_select()
    {
        $raw = 'CONCAT(core_terceros.apellido1, " ",core_terceros.apellido2, " ",core_terceros.nombre1, " ",core_terceros.otros_nombres) AS descripcion';

        $opciones = Vendedor::leftJoin('core_terceros','core_terceros.id','=','vtas_vendedores.core_tercero_id')->where('vtas_vendedores.estado','Activo')
                    ->select('vtas_vendedores.id',DB::raw($raw))
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
