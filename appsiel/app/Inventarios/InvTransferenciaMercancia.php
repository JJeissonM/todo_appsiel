<?php

namespace App\Inventarios;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvTransferenciaMercancia extends InvDocEncabezado
{
    protected $table = 'inv_doc_encabezados'; 

    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','fecha','core_tercero_id','inv_bodega_id', 'bodega_destino_id','documento_soporte','descripcion','estado','creado_por','modificado_por','hora_inicio','hora_finalizacion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'B. Origen', 'B. Destino', 'Tercero', 'Detalle', 'Doc. Soporte', 'Estado'];

    public $urls_acciones = '{"create":"inventarios/create","store":"inventarios","update":"inventarios/id_fila","show":"inventarios/id_fila","imprimir":"transaccion_print/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 2;

        if ( $search == '' )
        {
            return InvTransferenciaMercancia::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                                        ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                                        ->leftJoin('inv_bodegas AS bodega_origen', 'bodega_origen.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                                        ->leftJoin('inv_bodegas as bodega_destino', 'bodega_destino.id', '=', 'inv_doc_encabezados.bodega_destino_id')
                                        ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                        ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                        ->select(
                                            'inv_doc_encabezados.fecha AS campo1',
                                            DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2'),
                                            'bodega_origen.descripcion AS campo3',
                                            'bodega_destino.descripcion AS campo4',
                                            DB::raw('core_terceros.descripcion AS campo5'),
                                            'inv_doc_encabezados.descripcion AS campo6',
                                            'inv_doc_encabezados.documento_soporte AS campo7',
                                            'inv_doc_encabezados.estado AS campo8',
                                            'inv_doc_encabezados.id AS campo9'
                                        )
                                        ->orderBy('inv_doc_encabezados.created_at', 'DESC')
                                        ->paginate($nro_registros);
        }

        return InvTransferenciaMercancia::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas AS bodega_origen', 'bodega_origen.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->leftJoin('inv_bodegas as bodega_destino', 'bodega_destino.id', '=', 'inv_doc_encabezados.bodega_destino_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'inv_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2'),
                'bodega_origen.descripcion AS campo3',
                'bodega_destino.descripcion AS campo4',
                DB::raw('core_terceros.descripcion AS campo5'),
                'inv_doc_encabezados.descripcion AS campo6',
                'inv_doc_encabezados.documento_soporte AS campo7',
                'inv_doc_encabezados.estado AS campo8',
                'inv_doc_encabezados.id AS campo9'
            )
            ->where("inv_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("bodega_origen.descripcion", "LIKE", "%$search%")
            ->orWhere("bodega_destino.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.documento_soporte", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 2;

        $string = InvTransferenciaMercancia::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'inv_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS DOCUMENTO'),
                'inv_bodegas.descripcion AS BODEGA',
                DB::raw('core_terceros.descripcion AS TERCERO'),
                'inv_doc_encabezados.descripcion AS DETALLE',
                'inv_doc_encabezados.estado AS ESTADO'
            )
            ->where("inv_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TRANSFERENCIA DE MERCANCIA";
    }
}
