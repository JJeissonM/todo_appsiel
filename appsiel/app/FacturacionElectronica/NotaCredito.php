<?php

namespace App\FacturacionElectronica;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

use App\Ventas\VtasDocEncabezado;

class NotaCredito extends VtasDocEncabezado
{
    protected $table = 'vtas_doc_encabezados';

    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'descripcion', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'plazo_entrega_id', 'fecha_vencimiento', 'orden_compras', 'valor_total'];

    public $urls_acciones = '{"store":"fe_nota_credito","show":"fe_nota_credito/id_fila"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Detalle', 'Valor total', 'Estado'];

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 53;
        return VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('core_terceros.descripcion AS campo3'),
                'vtas_doc_encabezados.descripcion AS campo4',
                'vtas_doc_encabezados.valor_total AS campo5',
                'vtas_doc_encabezados.estado AS campo6',
                'vtas_doc_encabezados.id AS campo7'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 53;
        $string = VtasDocEncabezado::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('core_terceros.descripcion AS CLIENTE'),
                'vtas_doc_encabezados.descripcion AS DETALLE',
                'vtas_doc_encabezados.valor_total AS VALOR_TOTAL',
                'vtas_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.valor_total", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE NOTAS DE CRÉDITO";
    }
    /*
        Obtener todas las notas crédito aplicadas a la factura
    */
    public static function get_notas_aplicadas_factura($doc_encabezado_factura_id)
    {
        return NotaCredito::where('vtas_doc_encabezados.ventas_doc_relacionado_id', $doc_encabezado_factura_id)
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->select(
                'vtas_doc_encabezados.id',
                'vtas_doc_encabezados.core_empresa_id',
                'vtas_doc_encabezados.remision_doc_encabezado_id',
                'vtas_doc_encabezados.core_tercero_id',
                'vtas_doc_encabezados.cliente_id',
                'vtas_doc_encabezados.core_tipo_transaccion_id',
                'vtas_doc_encabezados.core_tipo_doc_app_id',
                'vtas_doc_encabezados.consecutivo',
                'vtas_doc_encabezados.fecha',
                'vtas_doc_encabezados.fecha_vencimiento',
                'vtas_doc_encabezados.descripcion',
                'vtas_doc_encabezados.ventas_doc_relacionado_id',
                'vtas_doc_encabezados.estado',
                'vtas_doc_encabezados.creado_por',
                'vtas_doc_encabezados.modificado_por',
                'vtas_doc_encabezados.created_at',
                'vtas_doc_encabezados.valor_total',
                'vtas_doc_encabezados.forma_pago AS condicion_pago',
                'core_tipos_docs_apps.descripcion AS documento_transaccion_descripcion',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS documento_prefijo_consecutivo'),
                'core_terceros.descripcion AS tercero_nombre_completo',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.telefono1'
            )
            ->get();
    }
}
