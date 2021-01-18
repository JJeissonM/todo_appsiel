<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class TipoLente extends Model
{
    protected $table = 'salud_tipo_lentes';
    protected $fillable = ['descripcion', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = TipoLente::select(
            'salud_tipo_lentes.descripcion AS campo1',
            'salud_tipo_lentes.estado AS campo2',
            'salud_tipo_lentes.id AS campo3'
        )
            ->where("salud_tipo_lentes.descripcion", "LIKE", "%$search%")
            ->orWhere("salud_tipo_lentes.estado", "LIKE", "%$search%")
            ->orderBy('salud_tipo_lentes.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = TipoLente::select(
            'salud_tipo_lentes.descripcion AS DESCRIPCIÓN',
            'salud_tipo_lentes.estado AS ESTADO'
        )
            ->where("salud_tipo_lentes.descripcion", "LIKE", "%$search%")
            ->orWhere("salud_tipo_lentes.estado", "LIKE", "%$search%")
            ->orderBy('salud_tipo_lentes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TIPOS DE LENTES";
    }

    public static function opciones_campo_select()
    {
        $opciones = TipoLente::select('salud_tipo_lentes.id', 'salud_tipo_lentes.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
