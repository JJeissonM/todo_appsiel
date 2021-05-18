<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

class VtasPedido extends VtasDocEncabezado
{
    protected $table = 'vtas_doc_encabezados';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente',  'Fecha entrega', 'Detalle', 'Estado'];

    // ventas_doc_relacionado_id: la factura que se facturó con base en el pedido.
    protected $fillable = ['id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'remision_doc_encabezado_id', 'ventas_doc_relacionado_id', 'cliente_id', 'vendedor_id', 'forma_pago', 'fecha_entrega', 'fecha_vencimiento', 'orden_compras', 'descripcion', 'valor_total', 'estado', 'creado_por', 'modificado_por', 'created_at', 'updated_at'];

    //public $vistas = '{"index":"layouts.index3"}';

    public $archivo_js = 'assets/js/ventas/pedidos.js';

    public function tipo_documento_app()
    {
        return $this->belongsTo('App\Core\TipoDocApp', 'core_tipo_doc_app_id');
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function lineas_registros()
    {
        return $this->hasMany(VtasDocRegistro::class, 'vtas_doc_encabezado_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 42; //pedido de venta

        if ($search == '') {
            return VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
                ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                ->select(
                    DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                    DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                    DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS campo4'),
                    'vtas_doc_encabezados.descripcion AS campo5',
                    'vtas_doc_encabezados.estado AS campo6',
                    'vtas_doc_encabezados.id AS campo7'
                )
                ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
                ->paginate($nro_registros);
        }

        return VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS campo4'),
                'vtas_doc_encabezados.descripcion AS campo5',
                'vtas_doc_encabezados.estado AS campo6',
                'vtas_doc_encabezados.id AS campo7'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.fecha_entrega", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $core_tipo_transaccion_id = 42;
        $string = VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                'vtas_doc_encabezados.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS DOCUMENTO'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS CLIENTE'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS FECHA_ENTREGA'),
                'vtas_doc_encabezados.descripcion AS DETALLE',
                'vtas_doc_encabezados.estado AS ESTADO'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.fecha_entrega", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PEDIDO DE VENTAS";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $core_tipo_transaccion_id = 42;

        if ($search == '') {
            return VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
                ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
                ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
                ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
                ->select(
                    DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                    DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                    DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                    DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS campo4'),
                    'vtas_doc_encabezados.descripcion AS campo5',
                    'vtas_doc_encabezados.estado AS campo6',
                    'vtas_doc_encabezados.id AS campo7'
                )->orderBy('vtas_doc_encabezados.created_at', 'DESC')
                ->paginate($nro_registros);
        }

        return VtasPedido::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_doc_encabezados.core_tipo_doc_app_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_doc_encabezados.core_tercero_id')
            ->where('vtas_doc_encabezados.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_doc_encabezados.core_tipo_transaccion_id', $core_tipo_transaccion_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo) AS campo2'),
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo3'),
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha_entrega,"%d-%m-%Y") AS campo4'),
                'vtas_doc_encabezados.descripcion AS campo5',
                'vtas_doc_encabezados.estado AS campo6',
                'vtas_doc_encabezados.id AS campo7'
            )
            ->orWhere("vtas_doc_encabezados.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_doc_encabezados.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social)'), "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.fecha_entrega", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_doc_encabezados.estado", "LIKE", "%$search%")
            ->orderBy('vtas_doc_encabezados.created_at', 'DESC')
            ->paginate($nro_registros);
    }
}
