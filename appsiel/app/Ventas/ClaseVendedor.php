<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ClaseVendedor extends Model
{
    protected $table = 'vtas_clases_vendedores';
	protected $fillable = ['descripcion', 'clase_padre_id', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Clase padre', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ClaseVendedor::select('vtas_clases_vendedores.descripcion AS campo1', 'vtas_clases_vendedores.clase_padre_id AS campo2', 'vtas_clases_vendedores.estado AS campo3', 'vtas_clases_vendedores.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = ClaseVendedor::where('vtas_clases_vendedores.estado','Activo')
                    ->select('vtas_clases_vendedores.id','vtas_clases_vendedores.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
