<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class EmpleadoPlanilla extends Model
{
	protected $table = 'nom_pila_empleados_planilla';

	protected $fillable = ['orden', 'planilla_generada_id', 'nom_contrato_id', 'tipo_linea'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Orden', 'Planilla generada', 'Empleado'];

	public function empleado()
	{
		return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
	}

	public static function consultar_registros($nro_registros, $search)
	{
		return EmpleadoPlanilla::select(
			'nom_pila_empleados_planilla.orden AS campo1',
			'nom_pila_empleados_planilla.planilla_generada_id AS campo2',
			'nom_pila_empleados_planilla.nom_contrato_id AS campo3',
			'nom_pila_empleados_planilla.id AS campo4'
		)
			->where("nom_pila_empleados_planilla.orden", "LIKE", "%$search%")
			->orWhere("nom_pila_empleados_planilla.planilla_generada_id", "LIKE", "%$search%")
			->orWhere("nom_pila_empleados_planilla.nom_contrato_id", "LIKE", "%$search%")
			->orderBy('nom_pila_empleados_planilla.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = EmpleadoPlanilla::select(
			'nom_pila_empleados_planilla.orden AS ORDEN',
			'nom_pila_empleados_planilla.planilla_generada_id AS PLANILLA_GENERADA',
			'nom_pila_empleados_planilla.nom_contrato_id AS EMPLEADO'
		)
			->where("nom_pila_empleados_planilla.orden", "LIKE", "%$search%")
			->orWhere("nom_pila_empleados_planilla.planilla_generada_id", "LIKE", "%$search%")
			->orWhere("nom_pila_empleados_planilla.nom_contrato_id", "LIKE", "%$search%")
			->orderBy('nom_pila_empleados_planilla.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE EMPLEADOS PLANILLA";
	}

	public static function opciones_campo_select()
	{
		$opciones = EmpleadoPlanilla::leftJoin('nom_pila_planillas_generadas', 'nom_pila_planillas_generadas.id', '=', 'nom_pila_datos_empresa.planilla_generada_id')
			->where('nom_pila_empleados_planilla.estado', 'Activo')
			->select('nom_pila_empleados_planilla.id', 'nom_pila_planillas_generadas.descripcion')
			->get();

		$vec[''] = '';
		foreach ($opciones as $opcion) {
			$vec[$opcion->id] = $opcion->descripcion;
		}

		return $vec;
	}
}
