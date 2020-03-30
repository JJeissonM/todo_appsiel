<?php

namespace App\Contratotransporte;

use Illuminate\Database\Eloquent\Model;

class Plantillaarticulo extends Model
{
    protected $table = 'cte_plantillaarticulos';
    protected $fillable = ['id', 'titulo', 'texto', 'plantilla_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['Título', 'Texto', 'Plantilla', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Plantillaarticulo::leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select('cte_plantillaarticulos.id', 'cte_plantillaarticulos.titulo AS articulo_titulo', 'cte_plantillas.titulo AS plantilla_titulo')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->plantilla_titulo . ' > ' . $opcion->articulo_titulo;
        }

        return $vec;
    }

    public static function consultar_registros2()
    {
        return Plantillaarticulo::leftJoin('cte_plantillas', 'cte_plantillas.id', '=', 'cte_plantillaarticulos.plantilla_id')
            ->select(
                'cte_plantillaarticulos.titulo AS campo1',
                'cte_plantillaarticulos.texto AS campo2',
                'cte_plantillas.titulo AS campo3',
                'cte_plantillaarticulos.id AS campo4'
            )
            ->orderBy('cte_plantillaarticulos.created_at', 'DESC')
            ->paginate(100);
    }
}
