<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'vtas_zonas';
    protected $fillable = ['descripcion', 'zona_padre_id', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Zona padre', 'Estado'];
    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Zona::select(
            'vtas_zonas.descripcion AS campo1',
            'vtas_zonas.zona_padre_id AS campo2',
            'vtas_zonas.estado AS campo3',
            'vtas_zonas.id AS campo4'
        )
            ->where("vtas_zonas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_zonas.zona_padre_id", "LIKE", "%$search%")
            ->orderBy('vtas_zonas.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Zona::select(
            'vtas_zonas.descripcion AS DESCRIPCIÓN',
            'vtas_zonas.zona_padre_id AS ZONA_PADRE',
            'vtas_zonas.estado AS ESTADO'
        )
            ->where("vtas_zonas.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_zonas.zona_padre_id", "LIKE", "%$search%")
            ->orderBy('vtas_zonas.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ZONAS";
    }

    public static function opciones_campo_select()
    {
        $opciones = Zona::where('vtas_zonas.estado', 'Activo')
            ->select('vtas_zonas.id', 'vtas_zonas.descripcion')
            ->get();

        //$vec['']='';
        $vec = [];
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
