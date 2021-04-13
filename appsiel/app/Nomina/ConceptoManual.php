<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class ConceptoManual extends NomConcepto
{
    protected $table = 'nom_conceptos';

    public static function opciones_campo_select()
    {
        $modo_liquidacion_id = 2; // Manual
        $opciones = NomConcepto::where('estado','Activo')->where( 'modo_liquidacion_id', $modo_liquidacion_id)->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
