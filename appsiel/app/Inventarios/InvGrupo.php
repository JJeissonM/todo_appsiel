<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class InvGrupo extends Model
{
    //protected $table = 'inv_grupos'; 

    protected $fillable = ['core_empresa_id', 'descripcion', 'nivel_padre', 'tipo_nivel', 'orden', 'cta_inventarios_id', 'cta_ingresos_id', 'estado', 'imagen', 'mostrar_en_pagina_web'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Nivel padre', 'Tipo nivel', 'Orden', 'Cta. Inventarios/Gastos', 'Cta. Ingresos', 'Estado'];

    public static function consultar_registros($nro_registros)
    {
        $registros = InvGrupo::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'inv_grupos.cta_inventarios_id')
            ->leftJoin('contab_cuentas AS ctas_ingresos', 'ctas_ingresos.id', '=', 'inv_grupos.cta_ingresos_id')
            ->where('inv_grupos.core_empresa_id', Auth::user()->empresa_id)
            ->select('inv_grupos.descripcion AS campo1', 'inv_grupos.nivel_padre AS campo2', 'inv_grupos.tipo_nivel AS campo3', 'inv_grupos.orden AS campo4', DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo5'), DB::raw('CONCAT(ctas_ingresos.codigo," ",ctas_ingresos.descripcion) AS campo6'), 'inv_grupos.estado AS campo7', 'inv_grupos.id AS campo8')
            ->orderBy('inv_grupos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }


    public static function opciones_campo_select()
    {
        $opciones = InvGrupo::where('estado', 'Activo')
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
