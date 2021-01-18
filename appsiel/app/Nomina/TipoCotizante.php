<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class TipoCotizante extends Model
{
    protected $table = 'nom_pila_tipos_cotizantes';
    protected $fillable = ['codigo', 'descripcion', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Estado'];
    public static function consultar_registros($nro_registros, $search)
    {
        return TipoCotizante::select(
            'nom_pila_tipos_cotizantes.codigo AS campo1',
            'nom_pila_tipos_cotizantes.descripcion AS campo2',
            'nom_pila_tipos_cotizantes.estado AS campo3',
            'nom_pila_tipos_cotizantes.id AS campo4'
        )
            ->where("nom_pila_tipos_cotizantes.codigo", "LIKE", "%$search%")
            ->orWhere("nom_pila_tipos_cotizantes.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_pila_tipos_cotizantes.estado", "LIKE", "%$search%")

            ->orderBy('nom_pila_tipos_cotizantes.created_at', 'DESC')
            ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $string = TipoCotizante::select(
            'nom_pila_tipos_cotizantes.codigo AS CÓDIGO',
            'nom_pila_tipos_cotizantes.descripcion AS DESCRIPCIÓN',
            'nom_pila_tipos_cotizantes.estado AS ESTADO'
        )
            ->where("nom_pila_tipos_cotizantes.codigo", "LIKE", "%$search%")
            ->orWhere("nom_pila_tipos_cotizantes.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_pila_tipos_cotizantes.estado", "LIKE", "%$search%")

            ->orderBy('nom_pila_tipos_cotizantes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TIPOS DE COTIZANTE";
    }

    public static function opciones_campo_select()
    {
        $opciones = TipoCotizante::where('nom_pila_tipos_cotizantes.estado', 'Activo')
            ->select('nom_pila_tipos_cotizantes.id', 'nom_pila_tipos_cotizantes.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
