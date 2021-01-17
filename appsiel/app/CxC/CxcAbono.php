<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxcAbono extends Model
{
    // Tabla auxiliar para llevar el registro de los abonos a los documentos de cxc.
    // Normalmente son documentos de Recaudos de Tesorería
    protected $table = 'cxc_abonos';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'core_empresa_id', 'core_tercero_id', 'modelo_referencia_tercero_index', 'referencia_tercero_id', 'fecha', 'doc_cxc_transacc_id', 'doc_cxc_tipo_doc_id', 'doc_cxc_consecutivo', 'doc_cruce_transacc_id', 'doc_cruce_tipo_doc_id', 'doc_cruce_consecutivo', 'abono', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento recaudo', 'Proveedor', 'Documento CxC Abonado', 'Valor abono'];

    public static function consultar_registros($nro_registros)
    {
        return CxcAbono::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxc', 'tipo_docs_cxc.id', '=', 'cxc_abonos.doc_cxc_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_abonos.core_tercero_id')
            ->where('cxc_abonos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'cxc_abonos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_abonos.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('CONCAT(tipo_docs_cxc.prefijo," ",cxc_abonos.doc_cxc_consecutivo) AS campo4'),
                'cxc_abonos.abono AS campo5',
                'cxc_abonos.id AS campo6'
            )
            ->orderBy('cxc_abonos.created_at', 'DESC')
            ->paginate($nro_registros);
    }


    /*
        Obtener datos de las FACTURA ( o documentos de CxC) afectadas por un recaudo específico 
    */
    public static function get_documentos_abonados($doc_recaudo_encabezado)
    {

        return CxcAbono::where('cxc_abonos.core_tipo_transaccion_id', $doc_recaudo_encabezado->core_tipo_transaccion_id)
            ->where('cxc_abonos.core_tipo_doc_app_id', $doc_recaudo_encabezado->core_tipo_doc_app_id)
            ->where('cxc_abonos.consecutivo', $doc_recaudo_encabezado->consecutivo)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_abonos.doc_cxc_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_abonos.core_tercero_id')
            ->select(
                'cxc_abonos.id',
                'cxc_abonos.core_empresa_id',
                'cxc_abonos.core_tercero_id',
                'cxc_abonos.referencia_tercero_id',
                'cxc_abonos.doc_cxc_transacc_id',
                'cxc_abonos.doc_cxc_tipo_doc_id',
                'cxc_abonos.doc_cxc_consecutivo',
                'cxc_abonos.fecha',
                'cxc_abonos.abono',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_abonos.doc_cxc_consecutivo) AS documento_prefijo_consecutivo'),
                'core_terceros.descripcion AS tercero_nombre_completo',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get();
    }


    /*
     * Obtener datos de los RECAUDOS hecho a una FACTURA (o documento de CxC) específica
     */
    public static function get_abonos_documento($doc_cxc_encabezado)
    {

        return CxcAbono::where('cxc_abonos.doc_cxc_transacc_id', $doc_cxc_encabezado->core_tipo_transaccion_id)
            ->where('cxc_abonos.doc_cxc_tipo_doc_id', $doc_cxc_encabezado->core_tipo_doc_app_id)
            ->where('cxc_abonos.doc_cxc_consecutivo', $doc_cxc_encabezado->consecutivo)
            ->leftJoin('core_tipos_docs_apps as doc_recaudo', 'doc_recaudo.id', '=', 'cxc_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_abonos.core_tercero_id')
            ->select(
                'cxc_abonos.id',
                'cxc_abonos.core_empresa_id',
                'cxc_abonos.core_tercero_id',
                'cxc_abonos.core_tipo_transaccion_id',
                'cxc_abonos.core_tipo_doc_app_id',
                'cxc_abonos.consecutivo',
                'cxc_abonos.fecha',
                'cxc_abonos.abono',
                'doc_recaudo.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(doc_recaudo.prefijo," ",cxc_abonos.consecutivo) AS documento_prefijo_consecutivo'),
                'core_terceros.descripcion AS tercero_nombre_completo',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get();
    }
}
