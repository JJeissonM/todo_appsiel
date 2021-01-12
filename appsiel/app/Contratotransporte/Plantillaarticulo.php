<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantillaarticulo extends Model
{
    protected $table = 'cte_plantillaarticulos';
    protected $fillable = ['id', 'titulo', 'texto', 'plantilla_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Título', 'Texto', 'Plantilla'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Plantillaarticulo::leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_plantillaarticulos.id',
                'cte_plantillaarticulos.titulo AS articulo_titulo',
                'cte_plantillas.titulo AS plantilla_titulo'
            )
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->plantilla_titulo . ' > ' . $opcion->articulo_titulo;
        }

        return $vec;
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        return Plantillaarticulo::leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_plantillaarticulos.titulo AS campo1',
                'cte_plantillaarticulos.texto AS campo2',
                'cte_plantillas.titulo AS campo3',
                'cte_plantillaarticulos.id AS campo4'
            )->where("cte_plantillaarticulos.titulo", "LIKE", "%$search%")
            ->orWhere("cte_plantillaarticulos.texto", "LIKE", "%$search%")
            ->orWhere("cte_plantillas.titulo", "LIKE", "%$search%")
            ->orderBy('cte_plantillaarticulos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Plantillaarticulo::leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_plantillaarticulos.titulo AS TÍTULO',
                'cte_plantillaarticulos.texto AS TEXTO',
                'cte_plantillas.titulo AS PLANTILLA'
            )->where("cte_plantillaarticulos.titulo", "LIKE", "%$search%")
            ->orWhere("cte_plantillaarticulos.texto", "LIKE", "%$search%")
            ->orWhere("cte_plantillas.titulo", "LIKE", "%$search%")
            ->orderBy('cte_plantillaarticulos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ARTÍCULOS DE PLANTILLAS FORMATO FUEC";
    }

    public function plantillaarticulonumerals()
    {
        return $this->hasMany(Plantillaarticulonumeral::class);
    }

    public function plantilla()
    {
        return $this->belongsTo(Plantilla::class);
    }
}
