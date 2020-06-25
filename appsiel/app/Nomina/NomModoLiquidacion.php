<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomModoLiquidacion extends Model
{
    protected $table = 'nom_modos_liquidacion';
	protected $fillable = ['descripcion','detalle', 'estado'];
	public $encabezado_tabla = ['Descripción', 'Detalle', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    return NomModoLiquidacion::select(
                                            'nom_modos_liquidacion.descripcion AS campo1',
                                            'nom_modos_liquidacion.detalle AS campo2',
                                            'nom_modos_liquidacion.estado AS campo3',
                                            'nom_modos_liquidacion.id AS campo4')
                            	    ->get()
                            	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = NomModoLiquidacion::where('estado','Activo')->orderBy('descripcion')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
