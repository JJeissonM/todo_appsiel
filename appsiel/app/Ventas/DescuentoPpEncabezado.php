<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class DescuentoPpEncabezado extends Model
{
    protected $table = 'vtas_descuentos_pp_encabezados';
    protected $fillable = ['descripcion', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = DescuentoPpEncabezado::select(
            'vtas_descuentos_pp_encabezados.descripcion AS campo1',
            'vtas_descuentos_pp_encabezados.estado AS campo2',
            'vtas_descuentos_pp_encabezados.id AS campo3'
        )
            ->where("vtas_descuentos_pp_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_descuentos_pp_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_descuentos_pp_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = DescuentoPpEncabezado::select(
            'vtas_descuentos_pp_encabezados.descripcion AS DESCRIPCIÓN',
            'vtas_descuentos_pp_encabezados.estado AS ESTADO'
        )
            ->where("vtas_descuentos_pp_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_descuentos_pp_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_descuentos_pp_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ENCABEZADO DESCUENTO PP";
    }

    public static function opciones_campo_select()
    {
        $opciones = DescuentoPpEncabezado::where('vtas_descuentos_pp_encabezados.estado', 'Activo')
            ->select('vtas_descuentos_pp_encabezados.id', 'vtas_descuentos_pp_encabezados.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
