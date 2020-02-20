<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomModoLiquidacion extends Model
{
    protected $table = 'nom_modos_liquidacion';
	protected $fillable = ['descripcion', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = NomModoLiquidacion::select('nom_modos_liquidacion.descripcion AS campo1', 'nom_modos_liquidacion.estado AS campo2', 'nom_modos_liquidacion.id AS campo3')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = NomModoLiquidacion::where('estado','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
