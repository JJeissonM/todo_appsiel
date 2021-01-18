<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use Auth;

class NomMovimiento extends Model
{
	//protected $table = 'nom_movimientos';
	protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'descripcion', 'total_devengos', 'total_deducciones', 'codigo_referencia_tercero', 'porcentaje', 'nom_concepto_id', 'nom_cuota_id', 'nom_prestamo_id', 'cantidad_horas', 'valor_devengo', 'valor_deduccion', 'estado', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Empleado', 'Fecha', 'Detalle', 'Concepto', 'Devengo', 'Deducción', 'Estado'];


	public static function consultar_registros($nro_registros, $search)
	{
		return NomMovimiento::leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_movimientos.nom_doc_encabezado_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_movimientos.core_tercero_id')
			->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_movimientos.nom_concepto_id')
			->select(
				'nom_doc_encabezados.descripcion AS campo1',
				'core_terceros.descripcion AS campo2',
				'nom_movimientos.fecha AS campo3',
				'nom_movimientos.detalle AS campo4',
				'nom_conceptos.descripcion AS campo5',
				'nom_movimientos.valor_devengo AS campo6',
				'nom_movimientos.valor_deduccion AS campo7',
				'nom_movimientos.estado AS campo8',
				'nom_movimientos.id AS campo9'
			)
			->where("nom_doc_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_movimientos.fecha", "LIKE", "%$search%")
			->orWhere("nom_movimientos.detalle", "LIKE", "%$search%")
			->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
			->orWhere("nom_movimientos.valor_devengo", "LIKE", "%$search%")
			->orWhere("nom_movimientos.valor_deduccion", "LIKE", "%$search%")
			->orWhere("nom_movimientos.estado", "LIKE", "%$search%")
			->orderBy('nom_movimientos.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = NomMovimiento::leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_movimientos.nom_doc_encabezado_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_movimientos.core_tercero_id')
			->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_movimientos.nom_concepto_id')
			->select(
				'nom_doc_encabezados.descripcion AS DOCUMENTO',
				'core_terceros.descripcion AS EMPLEADO',
				'nom_movimientos.fecha AS FECHA',
				'nom_movimientos.detalle AS DETALLE',
				'nom_conceptos.descripcion AS CONCEPTO',
				'nom_movimientos.valor_devengo AS DEVENGO',
				'nom_movimientos.valor_deduccion AS DEDUCCIÓN',
				'nom_movimientos.estado AS ESTADO'
			)
			->where("nom_doc_encabezados.descripcion", "LIKE", "%$search%")
			->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
			->orWhere("nom_movimientos.fecha", "LIKE", "%$search%")
			->orWhere("nom_movimientos.detalle", "LIKE", "%$search%")
			->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
			->orWhere("nom_movimientos.valor_devengo", "LIKE", "%$search%")
			->orWhere("nom_movimientos.valor_deduccion", "LIKE", "%$search%")
			->orWhere("nom_movimientos.estado", "LIKE", "%$search%")
			->orderBy('nom_movimientos.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE MOVIMIENTOS NOMINA";
	}

	public static function listado_acumulados($fecha_desde, $fecha_hasta, $operador1, $nom_agrupacion_id)
	{
		return NomMovimiento::leftJoin('nom_agrupacion_tiene_conceptos', 'nom_agrupacion_tiene_conceptos.nom_concepto_id', '=', 'nom_movimientos.nom_concepto_id')
			->where('nom_movimientos.core_empresa_id', Auth::user()->empresa_id)
			->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
			->where('nom_agrupacion_tiene_conceptos.nom_agrupacion_id', $operador1, $nom_agrupacion_id)
			->get();
	}
}
