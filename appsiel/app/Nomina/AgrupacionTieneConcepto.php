<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class AgrupacionTieneConcepto extends Model
{
    protected $table = 'nom_agrupacion_tiene_conceptos';
	protected $fillable = ['nom_agrupacion_id', 'nom_concepto_id', 'orden'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Agrupación', 'Concepto', 'Orden'];
    public static function consultar_registros($nro_registros)
    {
        $registros = AgrupacionTieneConcepto::select('nom_agrupacion_tiene_conceptos.nom_agrupacion_id AS campo1', 'nom_agrupacion_tiene_conceptos.nom_concepto_id AS campo2', 'nom_agrupacion_tiene_conceptos.orden AS campo3', 'nom_agrupacion_tiene_conceptos.id AS campo4')
            ->orderBy('nom_agrupacion_tiene_conceptos.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function opciones_campo_select()
    {
        $opciones = AgrupacionTieneConcepto::where('nom_agrupacion_tiene_conceptos.estado','Activo')
                    ->select('nom_agrupacion_tiene_conceptos.id','nom_agrupacion_tiene_conceptos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
