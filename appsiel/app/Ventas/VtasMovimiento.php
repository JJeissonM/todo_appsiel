<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use App\Inventarios\InvProducto;

class VtasMovimiento extends Model
{
    // base_impuesto: es unitario y siempre positivo
    // valor_impuesto: es unitario
    // precio_total: tiene signo dependiendo de la operacion (ventas +, notas crédito -)
    protected $fillable = ['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'vtas_motivo_id', 'fecha', 'core_tercero_id', 'estado', 'creado_por', 'modificado_por', 'remision_doc_encabezado_id', 'cliente_id', 'vendedor_id', 'zona_id', 'clase_cliente_id', 'equipo_ventas_id', 'forma_pago', 'fecha_vencimiento', 'orden_compras', 'inv_motivo_id', 'inv_bodega_id', 'inv_producto_id', 'precio_unitario', 'cantidad', 'precio_total', 'codigo_referencia_tercero', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'detalle'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Producto', 'Cliente', 'Precio unit.', 'Cantidad', 'Precio total', 'IVA', 'Base IVA Total'];

    public $vistas = '{"index":"layouts.index3"}';


    public function producto()
    {
        return $this->belongsTo(InvProducto::class);
    }

    public function cliente()
    {
        return $this->belongsTo( Cliente::class);
    }

    public function tercero()
    {
        return $this->belongsTo( 'App\Core\Tercero', 'core_tercero_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo) AS campo2';

        $registros = VtasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw($select_raw),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'vtas_movimientos.precio_unitario AS campo5',
                'vtas_movimientos.cantidad AS campo6',
                'vtas_movimientos.precio_total AS campo7',
                'vtas_movimientos.tasa_impuesto AS campo8',
                'vtas_movimientos.base_impuesto_total AS campo9',
                'vtas_movimientos.id AS campo10'
            )
            ->where("vtas_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.base_impuesto_total", "LIKE", "%$search%")
            ->orderBy('vtas_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo) AS DOCUMENTO';

        $string = VtasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw($select_raw),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS PRODUCTO'),
                'core_terceros.descripcion AS CLIENTE',
                'vtas_movimientos.precio_unitario AS PRECIO_UNIT',
                'vtas_movimientos.cantidad AS CANTIDAD',
                'vtas_movimientos.precio_total AS PRECIO_TOTAL',
                'vtas_movimientos.tasa_impuesto AS IVA',
                'vtas_movimientos.base_impuesto_total AS BASE_IVA_TOTAL'
            )
            ->orWhere("vtas_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.base_impuesto_total", "LIKE", "%$search%")
            ->orderBy('vtas_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS DE VENTA";
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $select_raw = 'CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo) AS campo2';

        $registros = VtasMovimiento::leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'vtas_movimientos.core_tipo_doc_app_id')
            ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->select(
                DB::raw('DATE_FORMAT(vtas_doc_encabezados.fecha,"%d-%m-%Y") AS campo1'),
                DB::raw($select_raw),
                DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS campo3'),
                'core_terceros.descripcion AS campo4',
                'vtas_movimientos.precio_unitario AS campo5',
                'vtas_movimientos.cantidad AS campo6',
                'vtas_movimientos.precio_total AS campo7',
                'vtas_movimientos.tasa_impuesto AS campo8',
                'vtas_movimientos.base_impuesto_total AS campo9',
                'vtas_movimientos.id AS campo10'
            )
            ->orWhere("vtas_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT(core_tipos_docs_apps.prefijo," ",vtas_movimientos.consecutivo)'), "LIKE", "%$search%")
            ->orWhere(DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" )'), "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.tasa_impuesto", "LIKE", "%$search%")
            ->orWhere("vtas_movimientos.base_impuesto_total", "LIKE", "%$search%")
            ->orderBy('vtas_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function get_precios_ventas($fecha_desde, $fecha_hasta, $producto_id, $operador1, $cliente_id, $operador2)
    {
        return VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
            ->where('vtas_movimientos.inv_producto_id', $operador1, $producto_id)
            ->where('vtas_movimientos.cliente_id', $operador2, $cliente_id)
            ->select('vtas_movimientos.inv_producto_id', DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, ")" ) AS producto'), 'core_terceros.descripcion AS cliente', DB::raw('SUM(vtas_movimientos.cantidad) AS cantidad'), DB::raw('SUM(vtas_movimientos.base_impuesto_total) AS base_impuesto_total'))
            ->groupBy('vtas_movimientos.inv_producto_id')
            ->groupBy('vtas_movimientos.cliente_id')
            ->get();
    }


    public static function get_movimiento_ventas( $fecha_desde, $fecha_hasta, $agrupar_por )
    {
        switch ( $agrupar_por )
        {
            case 'cliente_id':
                $agrupar_por = 'cliente';
                break;
            case 'core_tercero_id':
                $agrupar_por = 'core_tercero_id';
                break;
            case 'inv_producto_id':
                $agrupar_por = 'producto';
                break;
            case 'tasa_impuesto':
                $agrupar_por = 'tasa_impuesto';
                break;
            case 'clase_cliente_id':
                $agrupar_por = 'clase_cliente';
                break;
            case 'core_tipo_transaccion_id':
                $agrupar_por = 'descripcion_tipo_transaccion';
                break;
            case 'forma_pago':
                $agrupar_por = 'forma_pago';
                break;
            
            default:
                break;
        }

        $movimiento = VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
                            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_movimientos.core_tercero_id')
                            ->leftJoin('vtas_clases_clientes', 'vtas_clases_clientes.id', '=', 'vtas_movimientos.clase_cliente_id')
                            ->leftJoin('sys_tipos_transacciones', 'sys_tipos_transacciones.id', '=', 'vtas_movimientos.core_tipo_transaccion_id')
                            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('fecha', [$fecha_desde, $fecha_hasta])
                            ->select(
                                        'vtas_movimientos.inv_producto_id',
                                        DB::raw('CONCAT( inv_productos.id, " - ", inv_productos.descripcion, " (", inv_productos.unidad_medida1, " ", inv_productos.unidad_medida2, ")" ) AS producto'),
                                        'core_terceros.descripcion AS cliente',
                                        'vtas_movimientos.cliente_id',
                                        'vtas_movimientos.core_tercero_id',
                                        'vtas_clases_clientes.descripcion AS clase_cliente',
                                        'vtas_movimientos.tasa_impuesto AS tasa_impuesto',
                                        'sys_tipos_transacciones.descripcion AS descripcion_tipo_transaccion',
                                        'vtas_movimientos.forma_pago',
                                        'vtas_movimientos.cantidad',
                                        'vtas_movimientos.precio_total',
                                        'vtas_movimientos.base_impuesto_total',// AS base_imp_tot
                                        'vtas_movimientos.tasa_descuento',
                                        'vtas_movimientos.valor_total_descuento')
                            ->get();

        foreach ($movimiento as $fila)
        {
            $fila->base_impuesto_total = (float) $fila->precio_total / (1 + (float)$fila->tasa_impuesto / 100 );


            $fila->tasa_impuesto = (string)$fila->tasa_impuesto; // para poder agrupar
        }

        return $movimiento->groupBy( $agrupar_por );
    }



    public static function get_ultimo_precio_producto($cliente_id, $producto_id)
    {
        $registro = VtasMovimiento::leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_movimientos.inv_producto_id')
            ->where('vtas_movimientos.core_empresa_id', Auth::user()->empresa_id)
            ->where('vtas_movimientos.inv_producto_id', '=', $producto_id)
            ->where('vtas_movimientos.cliente_id', '=', $cliente_id)
            ->select('vtas_movimientos.precio_unitario')
            ->get()
            ->last();

        if (is_null($registro)) {
            return InvProducto::find($producto_id)->precio_venta;
        } else {
            return $registro->precio_unitario;
        }
    }

    public static function mov_ventas_totales_por_fecha( $fecha_inicial, $fecha_final )
    {
        return VtasMovimiento::where('core_empresa_id', Auth::user()->empresa_id)
                                ->whereBetween('fecha',[$fecha_inicial, $fecha_final])
                                ->select(
                                            DB::raw('SUM(base_impuesto) as total_ventas'),
                                            DB::raw('SUM(precio_total) as total_ventas_netas'),
                                            'fecha')
                                ->groupBy('fecha')
                                ->orderBy('fecha')
                                ->get();

    }
}
