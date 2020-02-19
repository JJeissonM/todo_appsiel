<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomCargo extends Model
{
    //protected $table = 'nom_cargos';
	protected $fillable = ['descripcion', 'estado', 'cargo_padre_id', 'rango_salarial_id'];
	public $encabezado_tabla = ['Descripción', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = NomCargo::select('nom_cargos.descripcion AS campo1', 'nom_cargos.estado AS campo2', 'nom_cargos.id AS campo3')
	    ->get()
	    ->toArray();
	    return $registros;
	}

    public static function opciones_campo_select()
    {
        $opciones = NomCargo::where('estado','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
