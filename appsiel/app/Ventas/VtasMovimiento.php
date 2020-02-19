<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use App\Inventarios\InvProducto;

class VtasMovimiento extends Model
{
    protected $fillable = ['core_empresa_id','core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','vtas_motivo_id','fecha','core_tercero_id','estado','creado_por','modificado_por','remision_doc_encabezado_id','cliente_id','vendedor_id','zona_id','clase_cliente_id','equipo_ventas_id','forma_pago','fecha_vencimiento','orden_compras','inv_motivo_id','inv_bodega_id','inv_producto_id','precio_unitario','cantidad','precio_total','codigo_referencia_tercero','base_impuesto','tasa_impuesto','valor_impuesto','base_impuesto_total'];

    public $encabezado_tabla = ['Fecha','Documento','Producto','Cliente','Precio unit.','Cantidad','Precio total','IVA','Base IVA Total','Acción'];

    public static function consultar_registros()
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo) AS campo2';

        $registros = VtasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_movimientos.core_tipo_doc_app_id')
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
                    ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->select('vtas_movimientos.fecha AS campo1',DB::raw($select_raw),DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'), 'core_terceros.descripcion AS campo4','vtas_movimientos.precio_unitario AS campo5','vtas_movimientos.cantidad AS campo6','vtas_movimientos.precio_total AS campo7','vtas_movimientos.tasa_impuesto AS campo8','vtas_movimientos.base_impuesto_total AS campo9','vtas_movimientos.id AS campo10')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_precios_ventas($fecha_desde, $fecha_hasta, $producto_id, $operador1, $cliente_id, $operador2)
    {
        return VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
                    ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
                    ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->whereBetween('fecha',[$fecha_desde, $fecha_hasta] )
                    ->where('vtas_movimientos.inv_producto_id', $operador1, $producto_id)
                    ->where('vtas_movimientos.cliente_id', $operador2, $cliente_id)
                    ->select('vtas_movimientos.inv_producto_id', DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS producto'), 'core_terceros.descripcion AS cliente', DB::raw('SUM(vtas_movimientos.cantidad) AS cantidad'), DB::raw('SUM(vtas_movimientos.base_impuesto_total) AS base_impuesto_total') )
                    ->groupBy('vtas_movimientos.inv_producto_id')
                    ->groupBy('vtas_movimientos.cliente_id')
                    ->get();
    }

    public static function get_ultimo_precio_producto( $cliente_id, $producto_id )
    {
        $registro = VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
                    ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
                    ->where('vtas_movimientos.inv_producto_id', '=', $producto_id)
                    ->where('vtas_movimientos.cliente_id', '=', $cliente_id)
                    ->select('vtas_movimientos.precio_unitario')
                    ->get()
                    ->last();

        if ( is_null($registro) )
        {
            return InvProducto::find($producto_id)->precio_venta;
        }else{
            return $registro->precio_unitario;
        }
    }
}