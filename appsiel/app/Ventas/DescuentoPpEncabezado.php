<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class DescuentoPpEncabezado extends Model
{
    protected $table = 'vtas_descuentos_pp_encabezados';
	protected $fillable = ['descripcion', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Estado', 'Acción'];
	
	public static function consultar_registros()
	{
	    $registros = DescuentoPpEncabezado::select('vtas_descuentos_pp_encabezados.descripcion AS campo1', 'vtas_descuentos_pp_encabezados.estado AS campo2', 'vtas_descuentos_pp_encabezados.id AS campo3')
	    ->get()
	    ->toArray();
	    return $registros;
	}

	public static function opciones_campo_select()
    {
        $opciones = DescuentoPpEncabezado::where('vtas_descuentos_pp_encabezados.estado','Activo')
                    ->select('vtas_descuentos_pp_encabezados.id','vtas_descuentos_pp_encabezados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
