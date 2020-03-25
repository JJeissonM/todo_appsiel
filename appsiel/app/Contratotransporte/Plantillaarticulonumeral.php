<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantillaarticulonumeral extends Model
{
    protected $table = 'cte_plantillaarticulonumerals';
    protected $fillable = ['id', 'numeracion', 'texto', 'plantillaarticulo_id', 'created_at', 'updated_at'];

    public static function opciones_campo_select()
    {
        $opciones = Plantillaarticulonumeral::leftJoin('cte_plantillaarticulos', 'cte_plantillaarticulos.id', '=', 'cte_plantillaarticulonumerals.plantillaarticulo_id')
                            ->select('cte_plantillaarticulonumerals.id','cte_plantillaarticulonumerals.numeracion','cte_plantillaarticulonumerals.texto','cte_plantillaarticulos.titulo AS articulo_titulo')
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->articulo_titulo.' > '.$opcion->numeracion.') '.$opcion->texto;
        }

        return $vec;
    }
}
