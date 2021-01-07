<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class TipoCotizante extends Model
{
    protected $table = 'nom_pila_tipos_cotizantes';
	protected $fillable = ['codigo', 'descripcion', 'estado'];
	public $encabezado_tabla = ['Código', 'Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    return TipoCotizante::select('nom_pila_tipos_cotizantes.codigo AS campo1', 'nom_pila_tipos_cotizantes.descripcion AS campo2', 'nom_pila_tipos_cotizantes.estado AS campo3', 'nom_pila_tipos_cotizantes.id AS campo4')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = TipoCotizante::where('nom_pila_tipos_cotizantes.estado','Activo')
                    ->select('nom_pila_tipos_cotizantes.id','nom_pila_tipos_cotizantes.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
