<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Numeraltabla extends Model
{
    protected $table = 'cte_numeraltablas';
    protected $fillable = ['id', 'plantillaarticulonumeral_id', 'campo', 'valor', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Campo', 'Valor', 'Numeral Artículo', 'Artículo', 'Plantilla', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros2()
    {
        return Numeraltabla::leftJoin('cte_plantillaarticulonumerals', 'cte_plantillaarticulonumerals.id', '=', 'cte_numeraltablas.plantillaarticulonumeral_id')
            ->leftJoin('cte_plantillaarticulos', 'cte_plantillaarticulos.id', '=', 'cte_plantillaarticulonumerals.plantillaarticulo_id')
            ->leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_numeraltablas.campo AS campo1',
                'cte_numeraltablas.valor AS campo2',
                DB::raw('CONCAT(cte_plantillaarticulonumerals.numeracion," ",cte_plantillaarticulonumerals.texto) AS campo3'),
                DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto) AS campo4'),
                'cte_plantillas.titulo AS campo5',
                'cte_numeraltablas.id AS campo6'
            )
            ->orderBy('cte_numeraltablas.created_at', 'DESC')
            ->paginate(100);
    }

    public function plantillaarticulonumeral()
    {
        return $this->belongsTo(Plantillaarticulonumeral::class);
    }
}
