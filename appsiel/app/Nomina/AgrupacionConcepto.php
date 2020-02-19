<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class AgrupacionConcepto extends Model
{
    protected $table = 'nom_agrupaciones_conceptos';
	protected $fillable = ['core_empresa_id', 'descripcion', 'nombre_corto', 'estado'];
	public $encabezado_tabla = ['', 'Descripción', 'Nombre corto', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = AgrupacionConcepto::select(, 'nom_agrupaciones_conceptos.descripcion AS campo1', 'nom_agrupaciones_conceptos.nombre_corto AS campo2', 'nom_agrupaciones_conceptos.estado AS campo3', 'nom_agrupaciones_conceptos.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}

	public static function opciones_campo_select()
    {
        $opciones = AgrupacionConcepto::where('nom_agrupaciones_conceptos.estado','Activo')
                    ->select('nom_agrupaciones_conceptos.id','nom_agrupaciones_conceptos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
