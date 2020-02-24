<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class InvGrupo extends Model
{
    //protected $table = 'inv_grupos'; 

    protected $fillable = ['core_empresa_id','descripcion','nivel_padre','tipo_nivel','orden','cta_inventarios_id', 'cta_ingresos_id','estado','imagen','mostrar_en_pagina_web'];

    public $encabezado_tabla = ['ID','Descripción','Nivel padre','Tipo nivel','Orden','Cta. Inventarios/Gastos','Cta. Ingresos','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = InvGrupo::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'inv_grupos.cta_inventarios_id')
                    ->leftJoin('contab_cuentas AS ctas_ingresos', 'ctas_ingresos.id', '=', 'inv_grupos.cta_ingresos_id')
                    ->where('inv_grupos.core_empresa_id', Auth::user()->empresa_id)
                    ->select('inv_grupos.id AS campo1','inv_grupos.descripcion AS campo2','inv_grupos.nivel_padre AS campo3','inv_grupos.tipo_nivel AS campo4','inv_grupos.orden AS campo5',DB::raw('CONCAT(contab_cuentas.codigo," ",contab_cuentas.descripcion) AS campo6'),DB::raw('CONCAT(ctas_ingresos.codigo," ",ctas_ingresos.descripcion) AS campo7'),'inv_grupos.estado AS campo8','inv_grupos.id AS campo9')
                    ->get()
                    ->toArray();

        return $registros;
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
