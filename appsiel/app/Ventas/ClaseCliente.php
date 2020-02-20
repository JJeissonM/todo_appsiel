<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ClaseCliente extends Model
{
    protected $table = 'vtas_clases_clientes';
	protected $fillable = ['descripcion', 'cta_x_cobrar_id', 'cta_anticipo_id', 'clase_padre_id', 'estado'];
	public $encabezado_tabla = ['Tercero', 'Cta x cobrar default', 'Cta anticipo default', 'Clase padre', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ClaseCliente::leftJoin('contab_cuentas as cta_x_cobrar','cta_x_cobrar.id','=','vtas_clases_clientes.cta_x_cobrar_id')->leftJoin('contab_cuentas as cta_anticipo','cta_anticipo.id','=','vtas_clases_clientes.cta_anticipo_id')->select('vtas_clases_clientes.descripcion AS campo1', 'cta_x_cobrar.descripcion AS campo2', 'cta_anticipo.descripcion AS campo3', 'vtas_clases_clientes.clase_padre_id AS campo4', 'vtas_clases_clientes.estado AS campo5', 'vtas_clases_clientes.id AS campo6')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = ClaseCliente::where('vtas_clases_clientes.estado','Activo')
                    ->select('vtas_clases_clientes.id','vtas_clases_clientes.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
