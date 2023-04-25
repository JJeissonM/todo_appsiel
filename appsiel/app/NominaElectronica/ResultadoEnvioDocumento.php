<?php

namespace App\NominaElectronica;

use Illuminate\Database\Eloquent\Model;

class ResultadoEnvioDocumento extends Model
{
	protected $table = 'nom_elect_resultados_envios_documentos';

	/*
		El campo "nombre" almacenará, en formato JSON, al Objecto "Documento Electrónico" Enviado 
	*/
	protected $fillable = [ 'core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'number', 'cune', 'request_xml', 'response_xml', 'qrcode', 'dian_status', 'email_status', 'dian_messages', 'codigo', 'objeto_json_enviado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Fecha', 'Cod. Respuesta', 'Dian Status', 'Email Status', 'CUNE'];

	public $vistas = '{"index":"layouts.index3"}';

	public $urls_acciones = '{"show":"web/id_fila"}';

	public static function consultar_registros2($nro_registros, $search)
	{
		return ResultadoEnvioDocumento::select(
			'nom_elect_resultados_envios_documentos.number AS campo1',
			'nom_elect_resultados_envios_documentos.fecha AS campo2',
			'nom_elect_resultados_envios_documentos.codigo AS campo3',
			'nom_elect_resultados_envios_documentos.dian_status AS campo4',
			'nom_elect_resultados_envios_documentos.email_status AS campo5',
			'nom_elect_resultados_envios_documentos.cune AS campo6',
			'nom_elect_resultados_envios_documentos.id AS campo7'
		)
			->where("nom_elect_resultados_envios_documentos.number", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.fecha", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.codigo", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.email_status", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.dian_status", "LIKE", "%$search%")
			->orderBy('nom_elect_resultados_envios_documentos.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = ResultadoEnvioDocumento::select(
			'nom_elect_resultados_envios_documentos.number AS DOCUMENTO',
			'nom_elect_resultados_envios_documentos.fecha AS FECHA',
			'nom_elect_resultados_envios_documentos.codigo AS CODIGO',
			'nom_elect_resultados_envios_documentos.cune AS CUNE',
			'nom_elect_resultados_envios_documentos.email_status AS EMAIL_STATUS',
			'nom_elect_resultados_envios_documentos.dian_status AS DIAN_STATUS'
		)->where("nom_elect_resultados_envios_documentos.number", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.codigo", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.cune", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.email_status", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.dian_status", "LIKE", "%$search%")
			->orWhere("nom_elect_resultados_envios_documentos.fecha", "LIKE", "%$search%")
			->orderBy('nom_elect_resultados_envios_documentos.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE RESULTADO ENVIO DE DOCUMENTOS";
	}
}
