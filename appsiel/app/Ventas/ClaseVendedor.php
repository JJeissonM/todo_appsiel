<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ClaseVendedor extends Model
{
    protected $table = 'vtas_clases_vendedores';
    protected $fillable = ['descripcion', 'clase_padre_id', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Clase padre', 'Estado'];
    public static function consultar_registros($nro_registros, $search)
    {
        $registros = ClaseVendedor::select(
            'vtas_clases_vendedores.descripcion AS campo1',
            'vtas_clases_vendedores.clase_padre_id AS campo2',
            'vtas_clases_vendedores.estado AS campo3',
            'vtas_clases_vendedores.id AS campo4'
        )
            ->where("vtas_clases_vendedores.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_clases_vendedores.clase_padre_id", "LIKE", "%$search%")
            ->orWhere("vtas_clases_vendedores.estado", "LIKE", "%$search%")
            ->orderBy('vtas_clases_vendedores.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }
    public static function sqlString($search)
    {
        $string = ClaseVendedor::select(
            'vtas_clases_vendedores.descripcion AS DESCRIPCIÓN',
            'vtas_clases_vendedores.clase_padre_id AS CLASE_PADRE',
            'vtas_clases_vendedores.estado AS ESTADO'
        )
            ->where("vtas_clases_vendedores.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_clases_vendedores.clase_padre_id", "LIKE", "%$search%")
            ->orWhere("vtas_clases_vendedores.estado", "LIKE", "%$search%")
            ->orderBy('vtas_clases_vendedores.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CLASES DE VENDEDORES";
    }

    public static function opciones_campo_select()
    {
        $opciones = ClaseVendedor::where('vtas_clases_vendedores.estado', 'Activo')
            ->select('vtas_clases_vendedores.id', 'vtas_clases_vendedores.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
