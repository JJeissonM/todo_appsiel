<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use App\Inventarios\InvProducto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ComprasMovimiento extends Model
{

    // valor_impuesto es UNITARIO
    // base_impuesto = valor_impuesto * cantidad => Equivale al total de la compra SIN IVA
    // precio_total = precio_unitario * cantidad => Equivale al total de la compra IVA incluido
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'estado', 'creado_por', 'modificado_por', 'cotizacion_id', 'compras_doc_encabezado_id', 'entrada_almacen_id', 'proveedor_id', 'comprador_id', 'fecha_recepcion', 'clase_proveedor_id', 'forma_pago', 'fecha_vencimiento', 'inv_motivo_id', 'inv_bodega_id', 'inv_producto_id', 'precio_unitario', 'cantidad', 'precio_total', 'codigo_referencia_tercero', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'tasa_descuento', 'valor_total_descuento'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Producto', 'Proveedor', 'Precio unit.', 'Cantidad', 'Precio total', 'IVA', 'Base IVA Total'];

    public $vistas = '{"index":"layouts.index3"}';
    
    public function tipo_transaccion()
    {
        return $this->belongsTo( 'App\Sistema\TipoTransaccion', 'core_tipo_transaccion_id' );
    }
    
    public function tipo_documento_app()
    {
        return $this->belongsTo( 'App\Core\TipoDocApp', 'core_tipo_doc_app_id' );
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero','core_tercero_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function get_label_documento()
    {
        return $this->tipo_documento_app->prefijo . ' ' . $this->consecutivo;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return ComprasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_movimientos.core_tercero_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'compras_movimientos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo) AS campo2'),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'compras_movimientos.precio_unitario AS campo5',
                'compras_movimientos.cantidad AS campo6',
                'compras_movimientos.precio_total AS campo7',
                'compras_movimientos.tasa_impuesto AS campo8',
                'compras_movimientos.base_impuesto AS campo9',
                'compras_movimientos.id AS campo10'
            )
            ->orWhere("compras_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.base_impuesto", "LIKE", "%$search%")
            ->orderBy('compras_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = ComprasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_movimientos.core_tercero_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'compras_movimientos.fecha AS FECHA',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo) AS DOCUMENTO'),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS PRODUCTO'),
                'core_terceros.descripcion AS PROVEEDOR',
                'compras_movimientos.precio_unitario AS PRECIO_UNIT',
                'compras_movimientos.cantidad AS CANTIDAD',
                'compras_movimientos.precio_total AS PRECIO_TOTAL',
                'compras_movimientos.tasa_impuesto AS IVA',
                'compras_movimientos.base_impuesto AS BASE_IVA_TOTAL'
            )
            ->orWhere("compras_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.base_impuesto", "LIKE", "%$search%")
            ->orderBy('compras_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTO DE COMPRAS";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        return ComprasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_movimientos.core_tercero_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                'compras_movimientos.fecha AS campo1',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo) AS campo2'),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'compras_movimientos.precio_unitario AS campo5',
                'compras_movimientos.cantidad AS campo6',
                'compras_movimientos.precio_total AS campo7',
                'compras_movimientos.tasa_impuesto AS campo8',
                'compras_movimientos.base_impuesto AS campo9',
                'compras_movimientos.id AS campo10'
            )
            ->orWhere("compras_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("compras_movimientos.base_impuesto", "LIKE", "%$search%")
            ->orderBy('compras_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function get_precios_compras($fecha_desde, $fecha_hasta, $producto_id, $operador1, $proveedor_id, $operador2, $grupo_inventario_id, $operador3)
    {
        return ComprasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_movimientos.core_tercero_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->where('compras_movimientos.inv_producto_id', $operador1, $producto_id)
            ->where('compras_movimientos.proveedor_id', $operador2, $proveedor_id)
            ->where('inv_productos.inv_grupo_id', $operador3, $grupo_inventario_id)
            ->select(
                        'compras_movimientos.inv_producto_id',
                        DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS producto'),
                        DB::raw('CONCAT( core_terceros.numero_identificacion, " - ", core_terceros.descripcion ) AS proveedor'),
                        DB::raw('SUM(compras_movimientos.cantidad) AS cantidad'),
                        DB::raw('SUM(compras_movimientos.precio_total) AS precio_total'),
                        DB::raw('SUM(compras_movimientos.base_impuesto) AS base_impuesto'))
            ->groupBy('compras_movimientos.inv_producto_id')
            ->groupBy('compras_movimientos.proveedor_id')
            ->get();
    }

    public static function get_ultimo_precio_producto($proveedor_id, $producto_id, $grupo_inventario_id = null)
    {
        $array_wheres = [
            ['compras_movimientos.core_empresa_id','=', Auth::user()->empresa_id]
        ];

        if ($grupo_inventario_id != null) {
            $array_wheres = array_merge( $array_wheres, [[ 'inv_productos.inv_grupo_id', '=', $grupo_inventario_id]]);
        }

        if ($producto_id != null) {
            $array_wheres = array_merge( $array_wheres, [[ 'compras_movimientos.inv_producto_id', '=', $producto_id]]);
        }

        if ($proveedor_id != null) {
            $array_wheres = array_merge( $array_wheres, [[ 'compras_movimientos.proveedor_id', '=', $proveedor_id]]);
        }

        $registro = ComprasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->where( $array_wheres)
            ->select(
                'compras_movimientos.*'
                )
            ->get()
            ->last();

        if ($registro == null) {
            return (object)[
                'precio_unitario' => InvProducto::find($producto_id)->precio_compra,
                'core_tipo_transaccion_id' => null
            ];
        }        
        
        return $registro;
    }

    // 
    public static function mov_compras_totales_por_fecha( $fecha_inicial, $fecha_final )
    {
        return ComprasMovimiento::where('core_empresa_id', Auth::user()->empresa_id)
                                ->whereBetween('fecha',[$fecha_inicial, $fecha_final])
                                ->select(
                                            DB::raw('SUM(base_impuesto) as total_compras'),
                                            DB::raw('SUM(precio_total) as total_compras_netas'),
                                            'fecha')
                                ->groupBy('fecha')
                                ->orderBy('fecha')
                                ->get();

    }
}
