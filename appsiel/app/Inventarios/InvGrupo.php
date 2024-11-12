<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvGrupo extends Model
{
    //protected $table = 'inv_grupos'; 

    protected $fillable = ['core_empresa_id','descripcion','nivel_padre','tipo_nivel','orden','cta_inventarios_id', 'cta_ingresos_id','estado','imagen','mostrar_en_pagina_web'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Cta. Inventarios/Gastos', 'Cta. Ingresos', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = InvGrupo::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'inv_grupos.cta_inventarios_id')
            ->leftJoin('contab_cuentas AS ctas_ingresos', 'ctas_ingresos.id', '=', 'inv_grupos.cta_ingresos_id')
            ->where('inv_grupos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_grupos.descripcion AS campo1',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo2'),
                DB::raw('CONCAT(ctas_ingresos.codigo," ",ctas_ingresos.descripcion) AS campo3'),
                'inv_grupos.estado AS campo4',
                'inv_grupos.id AS campo5'
            )
            ->where("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(ctas_ingresos.codigo," ",ctas_ingresos.descripcion)'), "LIKE", "%$search%")
            ->orWhere("inv_grupos.estado", "LIKE", "%$search%")
            ->orderBy('inv_grupos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = InvGrupo::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'inv_grupos.cta_inventarios_id')
            ->leftJoin('contab_cuentas AS ctas_ingresos', 'ctas_ingresos.id', '=', 'inv_grupos.cta_ingresos_id')
            ->where('inv_grupos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'inv_grupos.id AS ID',
                'inv_grupos.descripcion AS DESCRIPCIÓN',
                DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS CTA_INVENTARIOS_GASTOS'),
                DB::raw('CONCAT(ctas_ingresos.codigo," ",ctas_ingresos.descripcion) AS CTA_INGRESOS'),
                'inv_grupos.estado AS ESTADO'
            )
            ->where("inv_grupos.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(ctas_ingresos.codigo," ",ctas_ingresos.descripcion)'), "LIKE", "%$search%")
            ->orWhere("inv_grupos.estado", "LIKE", "%$search%")
            ->orderBy('inv_grupos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE GRUPOS DE INVENTARIO";
    }
    

    public static function opciones_campo_select()
    {
        $opciones = InvGrupo::where('estado','Activo')
                            ->where('core_empresa_id', Auth::user()->empresa_id)
                            ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
