<?php

namespace App\CxP;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class DocumentosPendientes extends Model
{

	protected $table = 'cxp_movimientos';

    protected $fillable = ['core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','core_empresa_id','core_tercero_id','modelo_referencia_tercero_index','referencia_tercero_id','doc_proveedor_prefijo','doc_proveedor_consecutivo','fecha','fecha_vencimiento','valor_documento','valor_pagado','saldo_pendiente','creado_por','modificado_por','estado'];

	public $encabezado_tabla = ['Proveedor', 'Documento', 'Fecha', 'Valor documento', 'Valor pagado', 'Saldo pendiente', 'Estado', 'Acción'];

    public $urls_acciones = '{
                                "create":"web/create",
                                "store":"compras_registro_cxp",
                                "update":"compras_registro_cxp/id_fila"
                            }';

    public static function consultar_registros()
    {
	    return DocumentosPendientes::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_movimientos.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_movimientos.core_tercero_id')
                    ->where('cxp_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->select( DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo1' ),
                    			DB::raw( 'CONCAT(cxp_movimientos.doc_proveedor_prefijo," ",cxp_movimientos.doc_proveedor_consecutivo) AS campo2' ),
                    			'cxp_movimientos.fecha AS campo3',
                                'cxp_movimientos.valor_documento AS campo4',
                                'cxp_movimientos.valor_pagado AS campo5',
                                'cxp_movimientos.saldo_pendiente AS campo6',
                                'cxp_movimientos.estado AS campo7',
                                'cxp_movimientos.id AS campo8')
                    ->get()
                    ->toArray();
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/compras/cxp_docuemntos_pendientes.js';

    public static function get_documentos_referencia_tercero( $operador, $cadena)
    {
    	$select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",cxp_movimientos.consecutivo) AS documento';

		return DocumentosPendientes::leftJoin('core_terceros','core_terceros.id','=','cxp_movimientos.core_tercero_id')
						        ->leftJoin('core_tipos_docs_apps','core_tipos_docs_apps.id','=','cxp_movimientos.core_tipo_doc_app_id')
						        ->where('cxp_movimientos.core_empresa_id',Auth::user()->empresa_id)
						        ->where('cxp_movimientos.core_tercero_id', $operador, $cadena)
                                ->where('cxp_movimientos.saldo_pendiente', '<>', 0)
						        ->select('cxp_movimientos.id','cxp_movimientos.core_tipo_transaccion_id','cxp_movimientos.core_tipo_doc_app_id','cxp_movimientos.consecutivo','core_terceros.descripcion AS tercero',DB::raw($select_raw),'cxp_movimientos.fecha','cxp_movimientos.fecha_vencimiento','cxp_movimientos.doc_proveedor_prefijo','cxp_movimientos.doc_proveedor_consecutivo','cxp_movimientos.valor_documento','cxp_movimientos.valor_pagado','cxp_movimientos.saldo_pendiente')
                                ->orderBy('cxp_movimientos.fecha')
						        ->get()->toArray(); 
    }

}
