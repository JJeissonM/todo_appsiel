<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;

class ResolucionFacturacion extends Model
{
	protected $table = 'vtas_resoluciones_facturacion';
	protected $fillable = ['core_empresa_id', 'sucursal_id', 'tipo_doc_app_id', 'numero_resolucion', 'numero_fact_inicial', 'numero_fact_final', 'fecha_expedicion', 'fecha_expiracion', 'modalidad', 'prefijo', 'tipo_solicitud', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Empresa', 'Tipo de documento', 'Prefijo', 'Número Resolución', 'Número desde', 'Número hasta', 'Fecha expedición', 'Fecha expiración', 'Modalidad', 'Tipo solicitud', 'Estado'];

	public static function consultar_registros($nro_registros, $search)
	{
		$registros = ResolucionFacturacion::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_resoluciones_facturacion.tipo_doc_app_id')
			->select(
				'vtas_resoluciones_facturacion.core_empresa_id AS campo1',
				DB::raw('CONCAT(core_tipos_docs_apps.prefijo," (",core_tipos_docs_apps.descripcion, ")") AS campo2'),
				'vtas_resoluciones_facturacion.prefijo AS campo3',
				'vtas_resoluciones_facturacion.numero_resolucion AS campo4',
				'vtas_resoluciones_facturacion.numero_fact_inicial AS campo5',
				'vtas_resoluciones_facturacion.numero_fact_final AS campo6',
				'vtas_resoluciones_facturacion.fecha_expedicion AS campo7',
				'vtas_resoluciones_facturacion.fecha_expiracion AS campo8',
				'vtas_resoluciones_facturacion.modalidad AS campo9',
				'vtas_resoluciones_facturacion.tipo_solicitud AS campo10',
				'vtas_resoluciones_facturacion.estado AS campo11',
				'vtas_resoluciones_facturacion.id AS campo12'
			)
			->where("vtas_resoluciones_facturacion.core_empresa_id", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," (",core_tipos_docs_apps.descripcion, ")")'), "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.prefijo", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.numero_resolucion", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.numero_fact_inicial", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.numero_fact_final", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.fecha_expedicion", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.fecha_expiracion", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.modalidad", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.tipo_solicitud", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.estado", "LIKE", "%$search%")
			->orderBy('vtas_resoluciones_facturacion.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = ResolucionFacturacion::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_resoluciones_facturacion.tipo_doc_app_id')
			->select(
				'vtas_resoluciones_facturacion.core_empresa_id AS EMPRESA',
				DB::raw('CONCAT(core_tipos_docs_apps.prefijo," (",core_tipos_docs_apps.descripcion, ")") AS TIPO_DE_DOCUMENTO'),
				'vtas_resoluciones_facturacion.prefijo AS PREFIJO',
				'vtas_resoluciones_facturacion.numero_resolucion AS NÚMERO_RESOLUCIÓN',
				'vtas_resoluciones_facturacion.numero_fact_inicial AS NÚMERO_DESDE',
				'vtas_resoluciones_facturacion.numero_fact_final AS NÚMERO_HASTA',
				'vtas_resoluciones_facturacion.fecha_expedicion AS FECHA_EXPEDICIÓN',
				'vtas_resoluciones_facturacion.fecha_expiracion AS FECHA_EXPIRACIÓN',
				'vtas_resoluciones_facturacion.modalidad AS MODALIDAD',
				'vtas_resoluciones_facturacion.tipo_solicitud AS TIPO_SOLICITUD',
				'vtas_resoluciones_facturacion.estado AS ESTADO'
			)
			->where("vtas_resoluciones_facturacion.core_empresa_id", "LIKE", "%$search%")
			->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," (",core_tipos_docs_apps.descripcion, ")")'), "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.prefijo", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.numero_resolucion", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.numero_fact_inicial", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.numero_fact_final", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.fecha_expedicion", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.fecha_expiracion", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.modalidad", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.tipo_solicitud", "LIKE", "%$search%")
			->orWhere("vtas_resoluciones_facturacion.estado", "LIKE", "%$search%")
			->orderBy('vtas_resoluciones_facturacion.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE RESOLUCIONES DE FACTURACIÓN";
	}
}
