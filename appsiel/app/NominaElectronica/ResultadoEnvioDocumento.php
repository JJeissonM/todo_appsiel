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

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Fecha', 'CUNE', 'Mensaje', 'Resultado', 'Tipo Documento'];

	public $vistas = '{"index":"layouts.index3"}';

	public $urls_acciones = '{"show":"web/id_fila"}';

	public static function consultar_registros2($nro_registros, $search)
	{
		return ResultadoEnvioDocumento::select(
			'fe_resultados_envios_documentos.number AS campo1',
			'fe_resultados_envios_documentos.codigo AS campo2',
			'fe_resultados_envios_documentos.cune AS campo3',
			'fe_resultados_envios_documentos.email_status AS campo4',
			'fe_resultados_envios_documentos.resultado AS campo5',
			'fe_resultados_envios_documentos.tipoDocumento AS campo6',
			'fe_resultados_envios_documentos.id AS campo7'
		)
			->where("fe_resultados_envios_documentos.number", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.codigo", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.cune", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.email_status", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.resultado", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.tipoDocumento", "LIKE", "%$search%")
			->orderBy('fe_resultados_envios_documentos.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = ResultadoEnvioDocumento::select(
			'fe_resultados_envios_documentos.number AS DOCUMENTO',
			'fe_resultados_envios_documentos.codigo AS CODIGO',
			'fe_resultados_envios_documentos.cune AS cune',
			'fe_resultados_envios_documentos.email_status AS MENSAJE',
			'fe_resultados_envios_documentos.resultado AS RESULTADO',
			'fe_resultados_envios_documentos.tipoDocumento AS TIPO_DOCUMENTO'
		)
			->where("fe_resultados_envios_documentos.number", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.codigo", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.cune", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.email_status", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.resultado", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos.tipoDocumento", "LIKE", "%$search%")
			->orderBy('fe_resultados_envios_documentos.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE RESULTADO ENVIO DE DOCUMENTOS";
	}
}
