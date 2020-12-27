<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class SemanasCalendario extends Model
{
    protected $table = 'sga_semanas_calendario';

    protected $fillable = ['descripcion', 'numero', 'fecha_inicio', 'fecha_fin', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Número', 'Descripción', 'Fecha inicial', 'Fecha final', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        $registros = SemanasCalendario::select('sga_semanas_calendario.numero AS campo1', 'sga_semanas_calendario.descripcion AS campo2', 'sga_semanas_calendario.fecha_inicio AS campo3', 'sga_semanas_calendario.fecha_fin AS campo4', 'sga_semanas_calendario.estado AS campo5', 'sga_semanas_calendario.id AS campo6')
            ->orderBy('sga_semanas_calendario.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function get_array_to_select()
    {
        $opciones = SemanasCalendario::where('estado', '=', 'Activo')->orderBy('numero')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->numero . ") " . $opcion->descripcion;
        }

        return $vec;
    }


    public static function opciones_campo_select()
    {
        $opciones = SemanasCalendario::all();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
