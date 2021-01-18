<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class GrupoEmpleado extends Model
{
    protected $table = 'nom_grupos_empleados';
    protected $fillable = ['core_empresa_id', 'grupo_padre_id', 'descripcion', 'nombre_corto', 'estado'];
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Nombre corto', 'Grupo padre', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        return GrupoEmpleado::select(
            'nom_grupos_empleados.descripcion AS campo1',
            'nom_grupos_empleados.nombre_corto AS campo2',
            'nom_grupos_empleados.grupo_padre_id AS campo3',
            'nom_grupos_empleados.estado AS campo4',
            'nom_grupos_empleados.id AS campo5'
        )
            ->where("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.nombre_corto", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.grupo_padre_id", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.estado", "LIKE", "%$search%")
            ->orderBy('nom_grupos_empleados.created_at', 'DESC')
            ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $string = GrupoEmpleado::select(
            'nom_grupos_empleados.descripcion AS DESCRIPCIÓN',
            'nom_grupos_empleados.nombre_corto AS NOMBRE_CORTO',
            'nom_grupos_empleados.grupo_padre_id AS GRUPO_PADRE',
            'nom_grupos_empleados.estado AS ESTADO'
        )
            ->where("nom_grupos_empleados.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.nombre_corto", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.grupo_padre_id", "LIKE", "%$search%")
            ->orWhere("nom_grupos_empleados.estado", "LIKE", "%$search%")
            ->orderBy('nom_grupos_empleados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE GRUPOS DE EMPLEADOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = GrupoEmpleado::where('nom_grupos_empleados.estado', 'Activo')
            ->select('nom_grupos_empleados.id', 'nom_grupos_empleados.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
