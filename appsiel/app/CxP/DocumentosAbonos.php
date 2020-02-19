<?php

namespace App\CxP;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class DocumentosAbonos extends Model
{

    // Tabla auxiliar para llevar el registro de los abonos a los documentos de CxP, normalmente son documentos de Pagos de Tesorería
	protected $table = 'cxp_documentos_abonos';

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','core_empresa_id','core_tercero_id','modelo_referencia_tercero_index','referencia_tercero_id','fecha','doc_cxp_transacc_id','doc_cxp_tipo_doc_id','doc_cxp_consecutivo','valor_documento','creado_por','modificado_por'];

	public $encabezado_tabla = ['Proveedor', 'Documento Abono', 'Fecha', 'Documento de CxP', 'Valor abono', 'Acción'];

    public static function consultar_registros()
    {
	    return DocumentosAbonos::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_documentos_abonos.core_tipo_doc_app_id')
                    ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxp', 'tipo_docs_cxp.id', '=', 'cxp_documentos_abonos.doc_cxp_tipo_doc_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_documentos_abonos.core_tercero_id')
                    ->where('cxp_documentos_abonos.core_empresa_id', Auth::user()->empresa_id)
                    ->select( DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo1' ),
                    			DB::raw( 'CONCAT(cxp_documentos_abonos.doc_proveedor_prefijo," ",cxp_documentos_abonos.doc_proveedor_consecutivo) AS campo2' ),
                    			'cxp_documentos_abonos.fecha AS campo3',
                                DB::raw( 'CONCAT(tipo_docs_cxp.prefijo," ",cxp_documentos_abonos.doc_cxp_consecutivo) AS campo4' ),
                                'cxp_documentos_abonos.valor_documento AS campo5',
                                'cxp_documentos_abonos.id AS campo6')
                    ->get()
                    ->toArray();
    }

}
