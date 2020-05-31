<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class GrupoEmpleado extends Model
{
    protected $table = 'nom_grupos_empleados';
	protected $fillable = ['core_empresa_id', 'grupo_padre_id', 'descripcion', 'nombre_corto', 'estado'];
	public $encabezado_tabla = ['', 'Grupo padre', 'Descripción', 'Nombre corto', 'Estado', 'Acción'];
	public static function consultar_registros()
	{
	    return GrupoEmpleado::select('nom_grupos_empleados.grupo_padre_id AS campo1', 'nom_grupos_empleados.descripcion AS campo2', 'nom_grupos_empleados.nombre_corto AS campo3', 'nom_grupos_empleados.estado AS campo4', 'nom_grupos_empleados.id AS campo5')
	    ->get()
	    ->toArray();
	}
	public static function opciones_campo_select()
    {
        $opciones = GrupoEmpleado::where('nom_grupos_empleados.estado','Activo')
                    ->select('nom_grupos_empleados.id','nom_grupos_empleados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
