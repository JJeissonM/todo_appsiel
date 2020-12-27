<?php

namespace App\Sistema;

use DB;

use Spatie\Permission\Models\Permission;

class Permiso extends Permission
{
    protected $fillable = ['core_app_id', 'modelo_id','name','descripcion','url','parent','orden','enabled','fa_icon'];

    public $encabezado_tabla = ['ID','App','Modelo','Name','Descripción (Menú)','URL','Menú padre','Ordén','Mostrar en menú','Icono','Acción'];

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
    public static function consultar_registros()
    {
        return Permission::leftJoin('sys_aplicaciones', 'sys_aplicaciones.id', '=', 'permissions.core_app_id')
                    ->leftJoin('sys_modelos', 'sys_modelos.id', '=', 'permissions.modelo_id')
                    ->orderBy('sys_aplicaciones.id','ASC')
                    ->select(
                                'permissions.id AS campo1',
                                'sys_aplicaciones.descripcion AS campo2',
                                DB::raw('CONCAT(sys_modelos.descripcion," (",sys_modelos.id,")") AS campo3'),
                                'permissions.name AS campo4',
                                'permissions.descripcion AS campo5',
                                'permissions.url AS campo6',
                                'permissions.parent AS campo7',
                                'permissions.orden AS campo8',
                                'permissions.enabled AS campo9',
                                'permissions.fa_icon AS campo10',
                                'permissions.id AS campo11'
                            )
                    ->get()
                    ->toArray();
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
