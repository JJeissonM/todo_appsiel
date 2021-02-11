<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomConceptoCesantias extends NomConcepto
{
    protected $table = 'nom_conceptos';

	protected $fillable = ['modo_liquidacion_id','naturaleza', 'porcentaje_sobre_basico', 'valor_fijo', 'descripcion', 'abreviatura', 'forma_parte_basico', 'nom_agrupacion_id', 'estado'];

    public static function opciones_campo_select()
    {
        // 15: Cesantías consignadas, 17: Cesantías pagadas
        $opciones = NomConceptoCesantias::where('estado','Activo')
                                    ->whereIn('modo_liquidacion_id', [ 15, 17 ])
                                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
