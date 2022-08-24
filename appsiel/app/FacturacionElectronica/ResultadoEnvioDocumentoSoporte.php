<?php

namespace App\FacturacionElectronica;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

class ResultadoEnvioDocumentoSoporte extends Model
{
	protected $table = 'fe_resultados_envios_documentos_soporte';

	/*
		El campo "nombre" almacenará, en formato JSON, al Objecto "Documento Electrónico" Enviado 
	*/
	protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'codigo', 'consecutivoDocumento', 'cufe', 'esValidoDian', 'fechaAceptacionDIAN', 'fechaRespuesta', 'hash', 'mensaje', 'mensajesValidacion', 'nombre', 'qr', 'reglasNotificacionDIAN', 'reglasValidacionDIAN', 'resultado', 'tipoCufe', 'xml', 'tipoDocumento', 'trackID', 'poseeAdjuntos'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'ID envío','Documento', 'Codigo', 'CUFE', 'Mensaje', 'Resultado', 'Tipo Documento'];

	public $vistas = '{"index":"layouts.index3"}';

	public $urls_acciones = '{"show":"web/id_fila"}';

	public static function consultar_registros2($nro_registros, $search)
	{
		return ResultadoEnvioDocumentoSoporte::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'fe_resultados_envios_documentos_soporte.core_tipo_doc_app_id')
		->select(
			'fe_resultados_envios_documentos_soporte.id AS campo1',
			DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",fe_resultados_envios_documentos_soporte.consecutivo) AS campo2'),
			'fe_resultados_envios_documentos_soporte.codigo AS campo3',
			'fe_resultados_envios_documentos_soporte.cufe AS campo4',
			'fe_resultados_envios_documentos_soporte.mensaje AS campo5',
			'fe_resultados_envios_documentos_soporte.resultado AS campo6',
			'fe_resultados_envios_documentos_soporte.tipoDocumento AS campo7',
			'fe_resultados_envios_documentos_soporte.id AS campo8'
		)
			->where("fe_resultados_envios_documentos_soporte.consecutivoDocumento", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.codigo", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.cufe", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.mensaje", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.resultado", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.tipoDocumento", "LIKE", "%$search%")
			->orderBy('fe_resultados_envios_documentos_soporte.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function sqlString($search)
	{
		$string = ResultadoEnvioDocumentoSoporte::select(
			'fe_resultados_envios_documentos_soporte.consecutivoDocumento AS DOCUMENTO',
			'fe_resultados_envios_documentos_soporte.codigo AS CODIGO',
			'fe_resultados_envios_documentos_soporte.cufe AS CUFE',
			'fe_resultados_envios_documentos_soporte.mensaje AS MENSAJE',
			'fe_resultados_envios_documentos_soporte.resultado AS RESULTADO',
			'fe_resultados_envios_documentos_soporte.tipoDocumento AS TIPO_DOCUMENTO'
		)
			->where("fe_resultados_envios_documentos_soporte.consecutivoDocumento", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.codigo", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.cufe", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.mensaje", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.resultado", "LIKE", "%$search%")
			->orWhere("fe_resultados_envios_documentos_soporte.tipoDocumento", "LIKE", "%$search%")
			->orderBy('fe_resultados_envios_documentos_soporte.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE RESULTADO ENVIO DE DOCUMENTOS";
	}
}
