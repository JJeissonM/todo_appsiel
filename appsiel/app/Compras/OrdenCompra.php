<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class OrdenCompra extends ComprasDocEncabezado
{
    // El campo estado puede ser: Pendiente, Parcial, Cumplida, Anulada
    protected $table = 'compras_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Órden de compra', 'Fecha', 'Proveedor', 'Factura proveedor', 'Detalle', 'Valor total', 'Estado'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 6; // Órden de compra   
        return OrdenCompra::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'compras_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo) AS campo4'),
                'compras_doc_encabezados.descripcion AS campo5',
                'compras_doc_encabezados.valor_total AS campo6',
                'compras_doc_encabezados.estado AS campo7',
                'compras_doc_encabezados.id AS campo8'
            )
            ->orWhere("compras_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," ",compras_doc_encabezados.doc_proveedor_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 6; // Órden de compra   
        $string = OrdenCompra::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'compras_doc_encabezados.fecha AS ÓRDEN_DE_COMPRA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS FECHA'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS PROVEEDOR'),
                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo) AS FACTURA_PROVEEDOR'),
                'compras_doc_encabezados.descripcion AS DETALLE',
                'compras_doc_encabezados.valor_total AS VALOR_TOTAL',
                'compras_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("compras_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," ",compras_doc_encabezados.doc_proveedor_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE ";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 6; // Órden de compra   
        return OrdenCompra::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'compras_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo) AS campo4'),
                'compras_doc_encabezados.descripcion AS campo5',
                'compras_doc_encabezados.valor_total AS campo6',
                'compras_doc_encabezados.estado AS campo7',
                'compras_doc_encabezados.id AS campo8'
            )
            ->orWhere("compras_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," ",compras_doc_encabezados.doc_proveedor_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }
}
