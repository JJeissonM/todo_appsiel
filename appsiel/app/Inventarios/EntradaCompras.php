<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class EntradaCompras extends InvDocEncabezado
{
    protected $table = 'inv_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Bodega', 'Tercero', 'Detalle', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 35; // Entrada de almacén (por compras)

        if ( $search == '' )
        {
            return EntradaCompras::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
                                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
                                ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
                                ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                                ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                                ->select(
                                    'inv_doc_encabezados.fecha AS campo1',
                                    DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2' ),
                                    'inv_bodegas.descripcion AS campo3',
                                    DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo4' ),
                                    'inv_doc_encabezados.descripcion AS campo5',
                                    'inv_doc_encabezados.estado AS campo6',
                                    'inv_doc_encabezados.id AS campo7'
                                )
                                ->orderBy('inv_doc_encabezados.created_at', 'DESC')
                                ->paginate($nro_registros);
        }

        return EntradaCompras::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'inv_doc_encabezados.fecha AS campo1',
                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS campo2' ),
                'inv_bodegas.descripcion AS campo3',
                DB::raw( 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo4' ),
                'inv_doc_encabezados.descripcion AS campo5',
                'inv_doc_encabezados.estado AS campo6',
                'inv_doc_encabezados.id AS campo7'
            )
            ->orWhere("inv_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }
    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo) AS DOCUMENTO';

        $select_raw2 = 'CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS TERCERO';

        $core_tipo_transaccion_id = 35; // Entrada de almacén (por compras)
        $string = EntradaCompras::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'inv_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'inv_doc_encabezados.core_tercero_id')
            ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_encabezados.inv_bodega_id')
            ->where('inv_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('inv_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'inv_doc_encabezados.fecha AS FECHA',
                DB::raw($select_raw),
                'inv_bodegas.descripcion AS BODEGA',
                DB::raw($select_raw2),
                'inv_doc_encabezados.descripcion AS DETALLE',
                'inv_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("inv_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",inv_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere("inv_bodegas.descripcion", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("inv_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('inv_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ENTRADAS DE COMPRAS";
    }
}
