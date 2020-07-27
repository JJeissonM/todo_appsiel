<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class CondicionPago extends Model
{
    protected $table = 'vtas_condiciones_pago';
	protected $fillable = ['descripcion', 'dias_plazo', 'estado'];
	public $encabezado_tabla = ['Tercero', 'Días de plazo', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = CondicionPago::select('vtas_condiciones_pago.descripcion AS campo1', 'vtas_condiciones_pago.dias_plazo AS campo2', 'vtas_condiciones_pago.estado AS campo3', 'vtas_condiciones_pago.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = CondicionPago::where('vtas_condiciones_pago.estado','Activo')
                    ->select('vtas_condiciones_pago.id','vtas_condiciones_pago.descripcion')
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
