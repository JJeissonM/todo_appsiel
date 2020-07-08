<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

use DB;

class DocRegistro extends Model
{
    protected $table = 'vtas_pos_doc_registros';

    
    // WARNING: se está usando inv_motivo_id para vtas_motivo_id

    // valor_impuesto es del precio_unitario
    // base_impuesto es del precio_unitario
    protected $fillable = ['vtas_pos_doc_encabezado_id ', 'vtas_motivo_id ', 'inv_producto_id', 'precio_unitario', 'cantidad', 'precio_total', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'cantidad_devuelta', 'creado_por', 'modificado_por', 'estado'];

    public $campos_invisibles_linea_registro = ['inv_motivo_id','inv_bodega_id','inv_producto_id','costo_unitario','precio_unitario','base_impuesto','tasa_impuesto','valor_impuesto','base_impuesto_total','cantidad','costo_total','precio_total', 'tasa_descuento', 'valor_total_descuento']; // 13 campos

    public $campos_visibles_linea_registro = [ 
    											['&nbsp;','10px'],
    											['Item','280px'],
                                                ['Cantidad','80px'],
                                                ['Precio Unit.','150px'],
                                                ['Dcto.','150px'],
                                                ['IVA','50px'],
    											['Total','200px'],
    											['&nbsp;','10px']
    										]; // 9 campos


    public static function get_registros_impresion( $doc_encabezado_id )
    {
        // WARNING vtas_motivo_id en realidad es inv_motivo_id
        return DocRegistro::where('vtas_pos_doc_registros.vtas_pos_doc_encabezado_id', $doc_encabezado_id)
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_pos_doc_registros.inv_producto_id')
                    ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'vtas_pos_doc_registros.vtas_motivo_id')
                    ->select(
                                'vtas_pos_doc_registros.id',
                                'vtas_pos_doc_registros.estado',
                                'vtas_pos_doc_registros.creado_por',
                                'vtas_pos_doc_registros.modificado_por',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.referencia',
                                'inv_productos.codigo_barras',
                                'vtas_pos_doc_registros.precio_unitario',
                                'vtas_pos_doc_registros.cantidad',
                                'vtas_pos_doc_registros.precio_total',
                                'vtas_pos_doc_registros.base_impuesto',
                                'vtas_pos_doc_registros.tasa_impuesto',
                                'vtas_pos_doc_registros.valor_impuesto',
                                'vtas_pos_doc_registros.base_impuesto_total',
                                'vtas_pos_doc_registros.cantidad_devuelta',
                                'vtas_pos_doc_registros.tasa_descuento',
                                'vtas_pos_doc_registros.valor_total_descuento',
                                'inv_motivos.descripcion as inv_motivo_descripcion',
                                'vtas_pos_doc_registros.vtas_motivo_id'
                            )
                    ->get();
    }

    public static function get_un_registro( $registro_id )
    {
        // WARNING vtas_motivo_id en realidad es inv_motivo_id
        return DocRegistro::where('vtas_pos_doc_registros.id', $registro_id)
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_pos_doc_registros.inv_producto_id')
                    ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'vtas_pos_doc_registros.vtas_motivo_id')
                    ->select(
                                'vtas_pos_doc_registros.id',
                                'vtas_pos_doc_registros.vtas_pos_doc_encabezado_id',
                                'vtas_pos_doc_registros.estado',
                                'vtas_pos_doc_registros.creado_por',
                                'vtas_pos_doc_registros.modificado_por',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.referencia',
                                'inv_productos.codigo_barras',
                                'vtas_pos_doc_registros.precio_unitario',
                                'vtas_pos_doc_registros.cantidad',
                                'vtas_pos_doc_registros.precio_total',
                                'vtas_pos_doc_registros.base_impuesto',
                                'vtas_pos_doc_registros.tasa_impuesto',
                                'vtas_pos_doc_registros.valor_impuesto',
                                'vtas_pos_doc_registros.base_impuesto_total',
                                'vtas_pos_doc_registros.cantidad_devuelta',
                                'vtas_pos_doc_registros.tasa_descuento',
                                'vtas_pos_doc_registros.valor_total_descuento',
                                'inv_motivos.descripcion as inv_motivo_descripcion'
                            )
                    ->get()
                    ->first();
    }

}
