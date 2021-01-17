<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Numeraltabla extends Model
{
    protected $table = 'cte_numeraltablas';
    protected $fillable = ['id', 'plantillaarticulonumeral_id', 'campo', 'valor', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Campo', 'Valor', 'Numeral Artículo', 'Artículo', 'Plantilla'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros2($nro_registros, $search)
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
            )->where("cte_numeraltablas.campo", "LIKE", "%$search%")
            ->orWhere("cte_numeraltablas.valor", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(cte_plantillaarticulonumerals.numeracion," ",cte_plantillaarticulonumerals.texto)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto)'), "LIKE", "%$search%")
            ->orWhere("cte_plantillas.titulo", "LIKE", "%$search%")
            ->orderBy('cte_numeraltablas.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Numeraltabla::leftJoin('cte_plantillaarticulonumerals', 'cte_plantillaarticulonumerals.id', '=', 'cte_numeraltablas.plantillaarticulonumeral_id')
            ->leftJoin('cte_plantillaarticulos', 'cte_plantillaarticulos.id', '=', 'cte_plantillaarticulonumerals.plantillaarticulo_id')
            ->leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_numeraltablas.campo AS CAMPO',
                'cte_numeraltablas.valor AS VALOR',
                DB::raw('CONCAT(cte_plantillaarticulonumerals.numeracion," ",cte_plantillaarticulonumerals.texto) AS NUMERAL_ARTÍCULO'),
                DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto) AS ARTÍCULO'),
                'cte_plantillas.titulo AS PLANTILLA'
            )->where("cte_numeraltablas.campo", "LIKE", "%$search%")
            ->orWhere("cte_numeraltablas.valor", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(cte_plantillaarticulonumerals.numeracion," ",cte_plantillaarticulonumerals.texto)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(cte_plantillaarticulos.titulo," ",cte_plantillaarticulos.texto)'), "LIKE", "%$search%")
            ->orWhere("cte_plantillas.titulo", "LIKE", "%$search%")
            ->orderBy('cte_numeraltablas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE NUMERALES DE TABLAS DE PLANTILLAS FORMATO FUEC";
    }

    public function plantillaarticulonumeral()
    {
        return $this->belongsTo(Plantillaarticulonumeral::class);
    }
}
