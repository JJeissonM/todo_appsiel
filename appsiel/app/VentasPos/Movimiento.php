<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    protected $table = 'vtas_pos_movimientos';
	protected $fillable = ['pdv_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'fecha', 'core_empresa_id', 'core_tercero_id', 'codigo_referencia_tercero', 'remision_doc_encabezado_id', 'cliente_id', 'vendedor_id', 'cajero_id', 'zona_id', 'clase_cliente_id', 'equipo_ventas_id', 'forma_pago', 'fecha_vencimiento', 'orden_compras', 'inv_producto_id', 'inv_bodega_id', 'vtas_motivo_id', 'inv_motivo_id', 'precio_unitario', 'cantidad', 'precio_total', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'estado', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Fecha', 'Documento', 'Cliente', 'Producto', 'Precio Unit.', 'Cantidad', 'Precio total', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        return Movimiento::select(
            'vtas_pos_movimientos.fecha AS campo1',
            'vtas_pos_movimientos.core_empresa_id AS campo2',
            'vtas_pos_movimientos.cliente_id AS campo3',
            'vtas_pos_movimientos.inv_producto_id AS campo4',
            'vtas_pos_movimientos.precio_unitario AS campo5',
            'vtas_pos_movimientos.cantidad AS campo6',
            'vtas_pos_movimientos.precio_total AS campo7',
            'vtas_pos_movimientos.estado AS campo8',
            'vtas_pos_movimientos.id AS campo9'
        )
            ->where("vtas_pos_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cliente_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.inv_producto_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_movimientos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Movimiento::select(
            'vtas_pos_movimientos.fecha AS FECHA',
            'vtas_pos_movimientos.core_empresa_id AS DOCUMENTO',
            'vtas_pos_movimientos.cliente_id AS CLIENTE',
            'vtas_pos_movimientos.inv_producto_id AS PRODUCTO',
            'vtas_pos_movimientos.precio_unitario AS PRECIO_UNIT.',
            'vtas_pos_movimientos.cantidad AS CANTIDAD',
            'vtas_pos_movimientos.precio_total AS PRECIO_TOTAL',
            'vtas_pos_movimientos.estado AS ESTADO'
        )
            ->where("vtas_pos_movimientos.fecha", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.core_empresa_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cliente_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.inv_producto_id", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_unitario", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.cantidad", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.precio_total", "LIKE", "%$search%")
            ->orWhere("vtas_pos_movimientos.estado", "LIKE", "%$search%")
            ->orderBy('vtas_pos_movimientos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE MOVIMIENTOS POS";
    }

	public static function opciones_campo_select()
    {
        $opciones = Movimiento::where('vtas_pos_movimientos.estado','Activo')
                    ->select('vtas_pos_movimientos.id','vtas_pos_movimientos.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
