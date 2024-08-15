<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaDctoEncabezado extends Model
{
    protected $table = 'vtas_listas_dctos_encabezados';
    
    protected $fillable = ['descripcion', 'estado'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = ListaDctoEncabezado::select(
            'vtas_listas_dctos_encabezados.descripcion AS campo1',
            'vtas_listas_dctos_encabezados.estado AS campo2',
            'vtas_listas_dctos_encabezados.id AS campo3'
        )
            ->where("vtas_listas_dctos_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_listas_dctos_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_listas_dctos_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = ListaDctoEncabezado::select(
            'vtas_listas_dctos_encabezados.descripcion AS DESCRIPCIÓN',
            'vtas_listas_dctos_encabezados.estado AS ESTADO'
        )
            ->where("vtas_listas_dctos_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_listas_dctos_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_listas_dctos_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DESCUENTOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = ListaDctoEncabezado::where('vtas_listas_dctos_encabezados.estado', 'Activo')
            ->select('vtas_listas_dctos_encabezados.id', 'vtas_listas_dctos_encabezados.descripcion')
            ->get();

        //$vec['']='';
        $vec = [];
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
