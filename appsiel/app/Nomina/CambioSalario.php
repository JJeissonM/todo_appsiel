<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\Nomina\NomContrato;

/*
	Desde aquí se deberían tomar todas las operaciones consalarios del sistema.
	Se debe refactorizar. Cambio en el diseño de la Aplicación Nomina.

	Por ahora solo se usa comom soporte para la liquidación de prestaciones sociales.
*/

class CambioSalario extends Model
{
	protected $table = 'nom_cambios_salarios';

	protected $fillable = ['nom_contrato_id', 'salario_anterior', 'nuevo_salario', 'fecha_modificacion', 'tipo_modificacion', 'observacion', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empleado', 'Salario anterior', 'Nuevo salario', 'Fecha modificación', 'Tipo modificación', 'Observación', 'Creado por', 'Modificado por'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

	public static function consultar_registros($nro_registros, $search)
	{
		return CambioSalario::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_cambios_salarios.nom_contrato_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
			->select(
				'core_terceros.descripcion AS campo1',
				'nom_cambios_salarios.salario_anterior AS campo2',
				'nom_cambios_salarios.nuevo_salario AS campo3',
				'nom_cambios_salarios.fecha_modificacion AS campo4',
				'nom_cambios_salarios.tipo_modificacion AS campo5',
				'nom_cambios_salarios.observacion AS campo6',
				DB::raw('CONCAT(nom_cambios_salarios.creado_por,", ",nom_cambios_salarios.created_at) AS campo7'),
				DB::raw('CONCAT(nom_cambios_salarios.modificado_por,", ",nom_cambios_salarios.updated_at) AS campo8'),
				'nom_cambios_salarios.id AS campo9'
			)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.salario_anterior", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.nuevo_salario", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.fecha_modificacion", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.tipo_modificacion", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.observacion", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(nom_cambios_salarios.creado_por,", ",nom_cambios_salarios.created_at)'), "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(nom_cambios_salarios.modificado_por,", ",nom_cambios_salarios.updated_at)'), "LIKE", "%$search%")
			->orderBy('nom_cambios_salarios.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = CambioSalario::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_cambios_salarios.nom_contrato_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
			->select(
				'core_terceros.descripcion AS EMPLEADO',
				'nom_cambios_salarios.salario_anterior AS SALARIO_ANTERIOR',
				'nom_cambios_salarios.nuevo_salario AS NUEVO_SALARIO',
				'nom_cambios_salarios.fecha_modificacion AS FECHA_MODIFICACIÓN',
				'nom_cambios_salarios.tipo_modificacion AS TIPO_MODIFICACIÓN',
				'nom_cambios_salarios.observacion AS OBSERVACIÓN',
				DB::raw('CONCAT(nom_cambios_salarios.creado_por,", ",nom_cambios_salarios.created_at) AS CREADO_POR'),
				DB::raw('CONCAT(nom_cambios_salarios.modificado_por,", ",nom_cambios_salarios.updated_at) AS MODIFICADO_POR')
			)
			->where("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.salario_anterior", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.nuevo_salario", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.fecha_modificacion", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.tipo_modificacion", "LIKE", "%$search%")
			->orWhere("nom_cambios_salarios.observacion", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(nom_cambios_salarios.creado_por,", ",nom_cambios_salarios.created_at)'), "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(nom_cambios_salarios.modificado_por,", ",nom_cambios_salarios.updated_at)'), "LIKE", "%$search%")
			->orderBy('nom_cambios_salarios.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE CAMBIOS DE SALARIO";
	}

	public static function opciones_campo_select()
	{
		$opciones = CambioSalario::where('nom_cambios_salarios.estado', 'Activo')
			->select('nom_cambios_salarios.id', 'nom_cambios_salarios.descripcion')
			->get();

		$vec[''] = '';
		foreach ($opciones as $opcion) {
			$vec[$opcion->id] = $opcion->descripcion;
		}

		return $vec;
	}

	public function store_adicional($datos, $registro)
	{
		$empleado = NomContrato::find((int)$datos['nom_contrato_id']);

		$registro->salario_anterior = $empleado->sueldo;
		$registro->tipo_modificacion = 'directa';
		$registro->save();

		$empleado->sueldo = $registro->nuevo_salario;
		$empleado->save();
	}
}
