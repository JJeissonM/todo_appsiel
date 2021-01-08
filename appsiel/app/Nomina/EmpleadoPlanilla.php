<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class EmpleadoPlanilla extends Model
{
    protected $table = 'nom_pila_empleados_planilla';
	
	protected $fillable = ['orden', 'planilla_generada_id', 'nom_contrato_id', 'tipo_linea'];
	
	public $encabezado_tabla = ['Orden', 'Planilla generada', 'Empleado', 'Acción'];
	
	public static function consultar_registros()
	{
	    return EmpleadoPlanilla::select('nom_pila_empleados_planilla.orden AS campo1', 'nom_pila_empleados_planilla.planilla_generada_id AS campo2', 'nom_pila_empleados_planilla.nom_contrato_id AS campo3', 'nom_pila_empleados_planilla.id AS campo4')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = EmpleadoPlanilla::leftJoin('nom_pila_planillas_generadas','nom_pila_planillas_generadas.id','=','nom_pila_datos_empresa.planilla_generada_id')
        						->where('nom_pila_empleados_planilla.estado','Activo')
			                    ->select('nom_pila_empleados_planilla.id','nom_pila_planillas_generadas.descripcion')
			                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
