<?php

namespace App\Sistema;

use DB;

use Spatie\Permission\Models\Permission;

class Permiso extends Permission
{
    protected $fillable = ['core_app_id', 'modelo_id', 'name', 'descripcion', 'url', 'parent', 'orden', 'enabled', 'fa_icon'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'App', 'Modelo', 'Name', 'Descripción (Menú)', 'URL', 'Menú padre', 'Ordén', 'Mostrar en menú', 'Icono'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public function aplicacion()
    {
        return $this->belongsTo(Aplicacion::class, 'core_app_id');
    }

    public function modelo()
    {
        return $this->belongsTo(Modelo::class, 'modelo_id');
    }

    // METODO PARA LA VISTA INDEX
    public static function consultar_registros($nro_registros, $search)
    {
        return Permission::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'permissions.core_app_id')
            ->leftJoin('sys_modelos', 'sys_modelos.id', '=', 'permissions.modelo_id')
            ->orderBy('sys_aplicaciones.id', 'ASC')
            ->select(
                'sys_aplicaciones.descripcion AS campo1',
                DB::raw('CONCAT(sys_modelos.descripcion," (",sys_modelos.id,")") AS campo2'),
                'permissions.name AS campo3',
                'permissions.descripcion AS campo4',
                'permissions.url AS campo5',
                'permissions.parent AS campo6',
                'permissions.orden AS campo7',
                'permissions.enabled AS campo8',
                'permissions.fa_icon AS campo9',
                'permissions.id AS campo10'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(sys_modelos.descripcion," (",sys_modelos.id,")")'), "LIKE", "%$search%")
            ->orWhere("permissions.name", "LIKE", "%$search%")
            ->orWhere("permissions.descripcion", "LIKE", "%$search%")
            ->orWhere("permissions.url", "LIKE", "%$search%")
            ->orWhere("permissions.parent", "LIKE", "%$search%")
            ->orWhere("permissions.orden", "LIKE", "%$search%")
            ->orWhere("permissions.enabled", "LIKE", "%$search%")
            ->orWhere("permissions.fa_icon", "LIKE", "%$search%")
            ->orderBy('permissions.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Permission::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'permissions.core_app_id')
            ->leftJoin('sys_modelos', 'sys_modelos.id', '=', 'permissions.modelo_id')
            ->orderBy('sys_aplicaciones.id', 'ASC')
            ->select(
                'sys_aplicaciones.descripcion AS APP',
                DB::raw('CONCAT(sys_modelos.descripcion," (",sys_modelos.id,")") AS MODELO'),
                'permissions.name AS NAME',
                'permissions.descripcion AS DESCRIPCIÓN_(MENÚ)',
                'permissions.url AS URL',
                'permissions.parent AS MENÚ_PADRE',
                'permissions.orden AS ORDÉN',
                'permissions.enabled AS MOSTRAR_EN_MENÚ',
                'permissions.fa_icon AS ICONO'
            )
            ->where("sys_aplicaciones.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(sys_modelos.descripcion," (",sys_modelos.id,")")'), "LIKE", "%$search%")
            ->orWhere("permissions.name", "LIKE", "%$search%")
            ->orWhere("permissions.descripcion", "LIKE", "%$search%")
            ->orWhere("permissions.url", "LIKE", "%$search%")
            ->orWhere("permissions.parent", "LIKE", "%$search%")
            ->orWhere("permissions.orden", "LIKE", "%$search%")
            ->orWhere("permissions.enabled", "LIKE", "%$search%")
            ->orWhere("permissions.fa_icon", "LIKE", "%$search%")
            ->orderBy('permissions.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PERMISOS";
    }

    //  METODOS LLAMADOS PARA LA VISTA CREATE
    public static function table_core_apps()
    {
        return DB::table('sys_aplicaciones')->get();
    }

    public static function table_permisos()
    {
        return DB::table('permissions')->get();
    }

    public static function table_core_modelos()
    {
        return DB::table('sys_modelos')->get();
    }
}
