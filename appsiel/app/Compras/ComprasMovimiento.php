<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use App\Inventarios\InvProducto;

class ComprasMovimiento extends Model
{

    // valor_impuesto es UNITARIO
    // base_impuesto = valor_impuesto * cantidad => Equivale al total de la compra SIN IVA
    // precio_total = precio_unitario * cantidad => Equivale al total de la compra IVA incluido
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_tercero_id', 'estado', 'creado_por', 'modificado_por', 'cotizacion_id', 'compras_doc_encabezado_id', 'entrada_almacen_id', 'proveedor_id', 'comprador_id', 'fecha_recepcion', 'clase_proveedor_id', 'forma_pago', 'fecha_vencimiento', 'inv_motivo_id', 'inv_bodega_id', 'inv_producto_id', 'precio_unitario', 'cantidad', 'precio_total', 'codigo_referencia_tercero', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto'];

    public $encabezado_tabla = ['Fecha', 'Documento', 'Producto', 'Proveedor', 'Precio unit.', 'Cantidad', 'Precio total', 'IVA', 'Base IVA Total', 'Acción'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function consultar_registros()
    {
        return ComprasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_movimientos.core_tercero_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select('compras_movimientos.fecha AS campo1', DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo) AS campo2'), DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'), 'core_terceros.descripcion AS campo4', 'compras_movimientos.precio_unitario AS campo5', 'compras_movimientos.cantidad AS campo6', 'compras_movimientos.precio_total AS campo7', 'compras_movimientos.tasa_impuesto AS campo8', 'compras_movimientos.base_impuesto AS campo9', 'compras_movimientos.id AS campo10')
            ->get()
            ->toArray();
    }

    public static function consultar_registros2()
    {
        return ComprasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'compras_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_movimientos.core_tercero_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select('compras_movimientos.fecha AS campo1', DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",compras_movimientos.consecutivo) AS campo2'), DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'), 'core_terceros.descripcion AS campo4', 'compras_movimientos.precio_unitario AS campo5', 'compras_movimientos.cantidad AS campo6', 'compras_movimientos.precio_total AS campo7', 'compras_movimientos.tasa_impuesto AS campo8', 'compras_movimientos.base_impuesto AS campo9', 'compras_movimientos.id AS campo10')
            ->orderBy('compras_movimientos.created_at', 'DESC')
            ->paginate(100);
    }

    public static function get_precios_compras($fecha_desde, $fecha_hasta, $producto_id, $operador1, $proveedor_id, $operador2)
    {

        /**/
        return ComprasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'compras_movimientos.core_tercero_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->where('compras_movimientos.inv_producto_id', $operador1, $producto_id)
            ->where('compras_movimientos.proveedor_id', $operador2, $proveedor_id)
            ->select('compras_movimientos.inv_producto_id', DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS producto'), 'core_terceros.descripcion AS proveedor', DB::raw('SUM(compras_movimientos.cantidad) AS cantidad'), DB::raw('SUM(compras_movimientos.precio_total) AS precio_total'))
            ->groupBy('compras_movimientos.inv_producto_id')
            ->groupBy('compras_movimientos.proveedor_id')
            ->get();
    }

    public static function get_ultimo_precio_producto($proveedor_id, $producto_id)
    {
        $registro = ComprasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_movimientos.inv_producto_id')
            ->where('compras_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->where('compras_movimientos.inv_producto_id', '=', $producto_id)
            ->where('compras_movimientos.proveedor_id', '=', $proveedor_id)
            ->select('compras_movimientos.precio_unitario')
            ->get()
            ->last();

        if (is_null($registro)) {
            return InvProducto::find($producto_id)->precio_compra;
        } else {
            return $registro->precio_unitario;
        }
    }
}
