<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class ComprasDocRegistro extends Model
{

    //NUEVO CAMPO: inv_doc_registro_id PARA determinar qué entrada/devolucion generó el registro y poder editar el registro cuando se edite el registro de la factura de compras 

    // cantidad_recibida: Se actualiza en las órdenes de compras al hacer una factura con base en orden de compra.
    // cantidad_devuelta: Se actualiza en las facturas de compras al hacer una devolución con base en factura.

    // base_impuesto: precio_unitario * cantidad (se calcula sobre el costo total, NO es unitario)
    // valor_impuesto: precio_total - base_impuesto (tambien es total, NO unitario)

	protected $fillable = ['compras_doc_encabezado_id', 'inv_doc_registro_id', 'inv_motivo_id', 'inv_producto_id', 'precio_unitario', 'cantidad', 'precio_total', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'cantidad_recibida', 'cantidad_devuelta', 'creado_por', 'modificado_por', 'estado'];

	// En compras los impuestos se calculan con base en el costo_unitario (precio_compra)
	public $campos_invisibles_linea_registro = ['inv_motivo_id','inv_bodega_id','inv_producto_id','costo_unitario','precio_unitario','base_impuesto','tasa_impuesto','valor_impuesto','cantidad','costo_total','precio_total']; // 11 campos

    public $campos_visibles_linea_registro = [ 
    											['&nbsp;','10px'],
    											['Producto','280px'],
    											['Motivo','200px'],
    											['Stock','35px'],
                                                ['Precio Compra (IVA incluido)',''],
                                                ['IVA',''],
    											['Cantidad',''],
    											['Total',''],
    											['&nbsp;','10px']
    										]; // 9 campos

	public $encabezado_tabla = ['Encabezado documento', 'Producto', 'Precio', 'Cantidad', 'Total', 'Base IVA', 'Tasa IVA', 'Total IVA', 'Cantidad recibida', 'Acción'];

	public static function consultar_registros()
	{
	    $registros = ComprasDocRegistro::select('compras_doc_registros.compras_doc_encabezado_id AS campo1', 'compras_doc_registros.inv_producto_id AS campo2', 'compras_doc_registros.precio_unitario AS campo3', 'compras_doc_registros.cantidad AS campo4', 'compras_doc_registros.precio_total AS campo5', 'compras_doc_registros.base_impuesto AS campo6', 'compras_doc_registros.tasa_impuesto AS campo7', 'compras_doc_registros.valor_impuesto AS campo8', 'compras_doc_registros.cantidad_recibida AS campo9', 'compras_doc_registros.id AS campo10')
	    ->get()
	    ->toArray();
	    return $registros;
	}


    public static function get_registros_impresion( $doc_encabezado_id )
    {

        return ComprasDocRegistro::where('compras_doc_registros.compras_doc_encabezado_id', $doc_encabezado_id)
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_doc_registros.inv_producto_id')
                    ->select(
                                'compras_doc_registros.id',
                                'compras_doc_registros.estado',
                                'compras_doc_registros.creado_por',
                                'compras_doc_registros.modificado_por',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.referencia',
                                'compras_doc_registros.inv_motivo_id',
                                'compras_doc_registros.inv_producto_id',
                                'inv_productos.codigo_barras',
                                'compras_doc_registros.precio_unitario',
                                'compras_doc_registros.cantidad',
                                'compras_doc_registros.cantidad_recibida',
                                'compras_doc_registros.cantidad_devuelta',
                                'compras_doc_registros.precio_total',
                                'compras_doc_registros.base_impuesto',
                                'compras_doc_registros.tasa_impuesto',
                                'compras_doc_registros.valor_impuesto'
                            )
                    ->get();
    }


    public static function get_un_registro( $registro_id )
    {
        return ComprasDocRegistro::where('compras_doc_registros.id', $registro_id)
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'compras_doc_registros.inv_producto_id')
                    ->select(
                                'compras_doc_registros.id',
                                'compras_doc_registros.estado',
                                'compras_doc_registros.compras_doc_encabezado_id',
                                'compras_doc_registros.creado_por',
                                'compras_doc_registros.modificado_por',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.referencia',
                                'inv_productos.codigo_barras',
                                'compras_doc_registros.precio_unitario',
                                'compras_doc_registros.cantidad',
                                'compras_doc_registros.precio_total',
                                'compras_doc_registros.base_impuesto',
                                'compras_doc_registros.tasa_impuesto',
                                'compras_doc_registros.valor_impuesto'
                            )
                    ->get()
                    ->first();
    }
}
