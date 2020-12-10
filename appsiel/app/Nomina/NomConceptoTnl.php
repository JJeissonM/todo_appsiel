<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomConceptoTnl extends NomConcepto
{
    protected $table = 'nom_conceptos';

	protected $fillable = ['modo_liquidacion_id','naturaleza', 'porcentaje_sobre_basico', 'valor_fijo', 'descripcion', 'abreviatura', 'forma_parte_basico', 'nom_agrupacion_id', 'estado'];

    public static function opciones_campo_select()
    {
        $opciones = NomConceptoTnl::where('estado','Activo')->where('modo_liquidacion_id', 7)->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
