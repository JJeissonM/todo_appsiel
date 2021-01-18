<?php

namespace App\FacturacionElectronica;

use Illuminate\Database\Eloquent\Model;

class ResultadoEnvioDocumento extends Model
{
	protected $table = 'fe_resultados_envios_documentos';

	/*
		El campo "nombre" almacenará, en formato JSON, al Objecto "Documento Electrónico" Enviado 
	*/
    protected $fillable = [ 'vtas_doc_encabezado_id', 'codigo', 'consecutivoDocumento', 'cufe', 'esValidoDian', 'fechaAceptacionDIAN', 'fechaRespuesta', 'hash', 'mensaje', 'mensajesValidacion', 'nombre', 'qr', 'reglasNotificacionDIAN', 'reglasValidacionDIAN', 'resultado', 'tipoCufe', 'xml', 'tipoDocumento', 'trackID', 'poseeAdjuntos' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Codigo', 'CUFE', 'Mensaje', 'Resultado', 'Tipo Documento'];

    public $vistas = '{"index":"layouts.index3"}';

    public $urls_acciones = '{"show":"web/id_fila"}';

    public static function consultar_registros2($nro_registros)
	{
		return ResultadoEnvioDocumento::select(
			'fe_resultados_envios_documentos.consecutivoDocumento AS campo1',
			'fe_resultados_envios_documentos.codigo AS campo2',
			'fe_resultados_envios_documentos.cufe AS campo3',
			'fe_resultados_envios_documentos.mensaje AS campo4',
			'fe_resultados_envios_documentos.resultado AS campo5',
			'fe_resultados_envios_documentos.tipoDocumento AS campo6',
			'fe_resultados_envios_documentos.id AS campo7'
		)
			->orderBy('fe_resultados_envios_documentos.created_at', 'DESC')
			->paginate($nro_registros);
	}
}
