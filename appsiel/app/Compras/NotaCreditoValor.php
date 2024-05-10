<?php

namespace App\Compras;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotaCreditoValor extends ComprasDocEncabezado
{
    protected $table = 'compras_doc_encabezados';

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento compra', 'Proveedor', 'Factura', 'Detalle', 'Valor total', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 61; // Nota crédito por valor

        if ( $search == '' )
        {
            return NotaCreditoValor::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
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
                            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
                            ->paginate($nro_registros);
        }

        return NotaCreditoValor::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
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
            ->orWhere(DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo)'), "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("compras_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('compras_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 61; // Nota crédito por valor
        $string = NotaCreditoValor::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
            ->where('compras_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'compras_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS DOCUMENTO_COMPRA'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS PROVEEDOR'),
                DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo) AS FACTURA'),
                'compras_doc_encabezados.descripcion AS DETALLE',
                'compras_doc_encabezados.valor_total AS VALOR_TOTAL',
                'compras_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("compras_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(compras_doc_encabezados.doc_proveedor_prefijo," - ",compras_doc_encabezados.doc_proveedor_consecutivo)'), "LIKE", "%$search%")
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
        return "LISTADO DE NOTAS CREDITO";
    }

    /*
        Obtener todas las notas crédito aplicadas a la factura
    */
    public static function get_notas_aplicadas_factura( $doc_encabezado_factura_id )
    {

        return NotaCreditoValor::where('compras_doc_encabezados.compras_doc_relacionado_id',$doc_encabezado_factura_id)
                    ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_doc_encabezados.core_tipo_doc_app_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_doc_encabezados.core_tercero_id')
                    ->select(
                                'compras_doc_encabezados.id',
                                'compras_doc_encabezados.core_empresa_id',
                                'compras_doc_encabezados.entrada_almacen_id',
                                'compras_doc_encabezados.core_tercero_id',
                                'compras_doc_encabezados.proveedor_id',
                                'compras_doc_encabezados.core_tipo_transaccion_id',
                                'compras_doc_encabezados.core_tipo_doc_app_id',
                                'compras_doc_encabezados.consecutivo',
                                'compras_doc_encabezados.fecha',
                                'compras_doc_encabezados.fecha_vencimiento',
                                'compras_doc_encabezados.fecha_recepcion',
                                'compras_doc_encabezados.cotizacion_id',
                                'compras_doc_encabezados.descripcion',
                                'compras_doc_encabezados.compras_doc_relacionado_id',
                                'compras_doc_encabezados.estado',
                                'compras_doc_encabezados.creado_por',
                                'compras_doc_encabezados.modificado_por',
                                'compras_doc_encabezados.created_at',
                                'compras_doc_encabezados.valor_total',
                                'compras_doc_encabezados.doc_proveedor_prefijo',
                                'compras_doc_encabezados.doc_proveedor_consecutivo',
                                'compras_doc_encabezados.forma_pago AS condicion_pago',
                                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                                DB::raw( 'CONCAT(core_tipos_docs_apps.prefijo," ",compras_doc_encabezados.consecutivo) AS documento_prefijo_consecutivo' ),
                                'core_terceros.descripcion AS tercero_nombre_completo',
                                'core_terceros.numero_identificacion',
                                'core_terceros.direccion1',
                                'core_terceros.telefono1'
                            )
                    ->get();
    }
}
