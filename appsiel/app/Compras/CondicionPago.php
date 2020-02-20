<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class CondicionPago extends Model
{
    protected $table = 'compras_condiciones_pago';
	protected $fillable = ['descripcion', 'dias_plazo', 'estado'];
	public $encabezado_tabla = ['Condición de pago', 'Días de plazo', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = CondicionPagoProv::select('compras_condiciones_pago.descripcion AS campo1', 'compras_condiciones_pago.dias_plazo AS campo2', 'compras_condiciones_pago.estado AS campo3', 'compras_condiciones_pago.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

	    public static function opciones_campo_select()
	    {
	        $opciones = CondicionPagoProv::where('compras_condiciones_pago.estado','Activo')
	                    ->select('compras_condiciones_pago.id','compras_condiciones_pago.descripcion')
	                    ->get();

	        $vec['']='';
	        foreach ($opciones as $opcion)
	        {
	            $vec[$opcion->id] = $opcion->descripcion;
	        }

	        return $vec;
	    }
}
