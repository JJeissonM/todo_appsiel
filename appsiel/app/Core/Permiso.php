<?php

namespace App\Core;

use DB;

use Spatie\Permission\Models\Permission;

class Permiso extends Permission
{
    protected $fillable = ['core_app_id', 'modelo_id','name','descripcion','url','parent','orden','enabled','fa_icon'];

    public $encabezado_tabla = ['ID','App','Modelo','Name','Descripción (Menú)','URL','Menú padre','Ordén','Mostrar en menú','Icono','Acción'];

    // METODO PARA LA VISTA INDEX
    public static function consultar_registros()
    {
        $registros = Permission::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'permissions.core_app_id')
                    ->leftJoin('sys_modelos', 'sys_modelos.id', '=', 'permissions.modelo_id')
                    ->orderBy('sys_aplicaciones.id','ASC')
                    ->select('permissions.id AS campo1','sys_aplicaciones.descripcion AS campo2','sys_modelos.descripcion AS campo3','permissions.name AS campo4','permissions.descripcion AS campo5','permissions.url AS campo6','permissions.parent AS campo7','permissions.orden AS campo8','permissions.enabled AS campo9','permissions.fa_icon AS campo10','permissions.id AS campo11')
                    ->get()
                    ->toArray();

        return $registros;
    }

    //  METODOS LLAMADOS PARA LA VISTA CREATE
    public static function table_core_apps()
    {
        $registros = DB::table('sys_aplicaciones')->get();

        return $registros;
    }

    public static function table_permisos()
    {
        $registros = DB::table('permissions')->get();

        return $registros;
    }

    public static function table_core_modelos()
    {
        $registros = DB::table('sys_modelos')->get();

        return $registros;
    }
}
