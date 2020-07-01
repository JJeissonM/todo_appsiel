<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class ConceptoCuota extends NomConcepto
{
    protected $table = 'nom_conceptos';

    protected $fillable = ['modo_liquidacion_id','naturaleza', 'porcentaje_sobre_basico', 'valor_fijo', 'descripcion', 'abreviatura', 'forma_parte_basico', 'nom_agrupacion_id', 'estado'];

    public static function opciones_campo_select()
    {
        $modo_liquidacion_id = 3; // Cuota
        $opciones = NomConcepto::where('estado','Activo')->where( 'modo_liquidacion_id', $modo_liquidacion_id)->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
