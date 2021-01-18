<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class CondicionPago extends Model
{
    protected $table = 'vtas_condiciones_pago';
    protected $fillable = ['descripcion', 'dias_plazo', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tercero', 'Días de plazo', 'Estado'];
    public static function consultar_registros($nro_registros, $search)
    {
        $registros = CondicionPago::select(
            'vtas_condiciones_pago.descripcion AS campo1',
            'vtas_condiciones_pago.dias_plazo AS campo2',
            'vtas_condiciones_pago.estado AS campo3',
            'vtas_condiciones_pago.id AS campo4'
        )
            ->where("vtas_condiciones_pago.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_condiciones_pago.dias_plazo", "LIKE", "%$search%")
            ->orWhere("vtas_condiciones_pago.estado", "LIKE", "%$search%")
            ->orderBy('vtas_condiciones_pago.created_at', 'DESC')
            ->paginate($nro_registros);
        return $registros;
    }

    public static function sqlString($search)
    {
        $string = CondicionPago::select(
            'vtas_condiciones_pago.descripcion AS TERCERO',
            'vtas_condiciones_pago.dias_plazo AS DÍAS_DE_PLAZO',
            'vtas_condiciones_pago.estado AS ESTADO'
        )
            ->where("vtas_condiciones_pago.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_condiciones_pago.dias_plazo", "LIKE", "%$search%")
            ->orWhere("vtas_condiciones_pago.estado", "LIKE", "%$search%")
            ->orderBy('vtas_condiciones_pago.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONDICION DE PAGO";
    }

    public static function opciones_campo_select()
    {
        $opciones = CondicionPago::where('vtas_condiciones_pago.estado', 'Activo')
            ->select('vtas_condiciones_pago.id', 'vtas_condiciones_pago.descripcion')
            ->get();

        //$vec['']='';
        $vec = [];
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
