<?php

namespace App\CxC;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class CxcDocCruceEncabezado extends CxcDocEncabezado
{
    protected $table = 'cxc_doc_encabezados';

    protected $fillable = ['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'fecha_vencimiento', 'core_empresa_id', 'core_tercero_id', 'tipo_movimiento', 'documento_soporte', 'descripcion', 'valor_total', 'estado', 'creado_por', 'modificado_por', 'codigo_referencia_tercero'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Valor Total', 'Tercero', 'Detalle'];


    // Se consultan los documentos para la empresa que tiene asignada el usuario
    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 16;

        if ( $search == '' )
        {
            return CxcDocCruceEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_doc_encabezados.core_tipo_doc_app_id')
                                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_doc_encabezados.core_tercero_id')
                                    ->where('cxc_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                    ->where('cxc_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                    ->select(
                                        'cxc_doc_encabezados.fecha AS campo1',
                                        DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS campo2'),
                                        'cxc_doc_encabezados.valor_total AS campo3',
                                        'core_terceros.descripcion as campo4',
                                        'cxc_doc_encabezados.descripcion AS campo5',
                                        'cxc_doc_encabezados.id AS campo6'
                                    )
                                    ->orderBy('cxc_doc_encabezados.created_at', 'DESC')
                                    ->paginate($nro_registros);
        }

        return CxcDocCruceEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_doc_encabezados.core_tercero_id')
            ->where('cxc_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('cxc_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'cxc_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS campo2'),
                'cxc_doc_encabezados.valor_total AS campo3',
                'core_terceros.descripcion as campo4',
                'cxc_doc_encabezados.descripcion AS campo5',
                'cxc_doc_encabezados.id AS campo6'
            )
            ->orWhere("cxc_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("cxc_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("cxc_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orderBy('cxc_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 16;

        $string = CxcDocCruceEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_doc_encabezados.core_tercero_id')
            ->where('cxc_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('cxc_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'cxc_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'cxc_doc_encabezados.valor_total AS VALOR_TOTAL',
                'core_terceros.descripcion as TERCERO',
                'cxc_doc_encabezados.descripcion AS DETALLE'
            )
            ->orWhere("cxc_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",cxc_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("cxc_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("cxc_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orderBy('cxc_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE DOCUMENTOS CRUCE";
    }
}
