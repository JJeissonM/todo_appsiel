<?php

namespace App\CxP;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxpAbono extends Model
{

    // Tabla auxiliar para llevar el registro de los abonos a los documentos de CxP, normalmente son documentos de Pagos de Tesorería
    protected $table = 'cxp_abonos';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'core_empresa_id', 'core_tercero_id', 'modelo_referencia_tercero_index', 'referencia_tercero_id', 'fecha', 'doc_cxp_transacc_id', 'doc_cxp_tipo_doc_id', 'doc_cxp_consecutivo', 'doc_cruce_transacc_id', 'doc_cruce_tipo_doc_id', 'doc_cruce_consecutivo', 'abono', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento pago', 'Proveedor', 'Documento de CxP', 'Documento Cruce', 'Valor abono'];

    public static function consultar_registros($nro_registros, $search)
    {
        return CxpAbono::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxp', 'tipo_docs_cxp.id', '=', 'cxp_abonos.doc_cxp_tipo_doc_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cruce', 'tipo_docs_cruce.id', '=', 'cxp_abonos.doc_cruce_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
            ->where('cxp_abonos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'cxp_abonos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('CONCAT(tipo_docs_cxp.prefijo," ",cxp_abonos.doc_cxp_consecutivo) AS campo4'),
                DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxp_abonos.doc_cruce_consecutivo) AS campo5'),
                'cxp_abonos.abono AS campo6',
                'cxp_abonos.id AS campo7'
            )
            ->where("cxp_abonos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(tipo_docs_cxp.prefijo," ",cxp_abonos.doc_cxp_consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxp_abonos.doc_cruce_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("cxp_abonos.abono", "LIKE", "%$search%")
            ->orderBy('cxp_abonos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = CxpAbono::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cxp', 'tipo_docs_cxp.id', '=', 'cxp_abonos.doc_cxp_tipo_doc_id')
            ->leftJoin('core_tipos_docs_apps AS tipo_docs_cruce', 'tipo_docs_cruce.id', '=', 'cxp_abonos.doc_cruce_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
            ->where('cxp_abonos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'cxp_abonos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('CONCAT(tipo_docs_cxp.prefijo," ",cxp_abonos.doc_cxp_consecutivo) AS campo4'),
                DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxp_abonos.doc_cruce_consecutivo) AS campo5'),
                'cxp_abonos.abono AS campo6',
                'cxp_abonos.id AS campo7'
            )
            ->where("cxp_abonos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(tipo_docs_cxp.prefijo," ",cxp_abonos.doc_cxp_consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(tipo_docs_cruce.prefijo," ",cxp_abonos.doc_cruce_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("cxp_abonos.abono", "LIKE", "%$search%")
            ->orderBy('cxp_abonos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTOS ABONADOS";
    }

    /*
        Obtener los registro de abonos hechos por $doc_encabezado
    */
    public static function get_documentos_abonados($doc_encabezado)
    {

        return CxpAbono::where('cxp_abonos.core_tipo_transaccion_id', $doc_encabezado->core_tipo_transaccion_id)
            ->where('cxp_abonos.core_tipo_doc_app_id', $doc_encabezado->core_tipo_doc_app_id)
            ->where('cxp_abonos.consecutivo', $doc_encabezado->consecutivo)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxp_abonos.doc_cxp_tipo_doc_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
            ->select(
                'cxp_abonos.id',
                'cxp_abonos.core_empresa_id',
                'cxp_abonos.core_tercero_id',
                'cxp_abonos.referencia_tercero_id',
                'cxp_abonos.doc_cxp_transacc_id',
                'cxp_abonos.doc_cxp_tipo_doc_id',
                'cxp_abonos.doc_cxp_consecutivo',
                'cxp_abonos.fecha',
                'cxp_abonos.abono',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxp_abonos.doc_cxp_consecutivo) AS documento_prefijo_consecutivo'),
                'core_terceros.descripcion AS tercero_nombre_completo',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get();
    }

    /*
     * Obtener datos de los PAGOS hechos a una FACTURA (o documento de CxP) específica
     */
    public static function get_abonos_documento($doc_cxp_encabezado)
    {

        return CxpAbono::where('cxp_abonos.doc_cxp_transacc_id', $doc_cxp_encabezado->core_tipo_transaccion_id)
            ->where('cxp_abonos.doc_cxp_tipo_doc_id', $doc_cxp_encabezado->core_tipo_doc_app_id)
            ->where('cxp_abonos.doc_cxp_consecutivo', $doc_cxp_encabezado->consecutivo)
            ->leftJoin('core_tipos_docs_apps as doc_pago', 'doc_pago.id', '=', 'cxp_abonos.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxp_abonos.core_tercero_id')
            ->select(
                'cxp_abonos.id',
                'cxp_abonos.core_empresa_id',
                'cxp_abonos.core_tercero_id',
                'cxp_abonos.core_tipo_transaccion_id',
                'cxp_abonos.core_tipo_doc_app_id',
                'cxp_abonos.consecutivo',
                'cxp_abonos.fecha',
                'cxp_abonos.abono',
                'doc_pago.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(doc_pago.prefijo," ",cxp_abonos.consecutivo) AS documento_prefijo_consecutivo'),
                'core_terceros.descripcion AS tercero_nombre_completo',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get();
    }
}
