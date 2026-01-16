<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;

class VtasDocRegistro extends Model
{
    
    // WARNING: se está usando inv_motivo_id para vtas_motivo_id

    // valor_impuesto es del precio_unitario
    // base_impuesto es del precio_unitario
    protected $fillable = ['vtas_doc_encabezado_id','vtas_motivo_id','inv_producto_id', 'impuesto_id', 'precio_unitario','cantidad', 'cantidad_pendiente', 'cantidad_devuelta','precio_total','base_impuesto','tasa_impuesto','valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'creado_por', 'modificado_por'];

    public $campos_invisibles_linea_registro = ['inv_motivo_id','inv_bodega_id','inv_producto_id','costo_unitario','precio_unitario','base_impuesto','tasa_impuesto','valor_impuesto','base_impuesto_total','cantidad','costo_total','precio_total', 'tasa_descuento', 'valor_total_descuento']; // 13 campos

    public $campos_visibles_linea_registro = [ 
                                            ['&nbsp;','10px'],
                                            ['ITEM','280px'],
    											['MOTIVO','200px'],
    											['STOCK','35px'],
                                                ['CANT.',''],
                                                ['PRECIO UNIT. (IVA INCLUIDO)',''],
                                                ['DCTO. (%)',''],
                                                ['DCTO. TOT. ($)',''],
                                                ['IVA',''],
    											['TOTAL',''],
    											['&nbsp;','10px']
    										]; // 9 campos


    public function producto()
    {
        return $this->belongsTo('App\Inventarios\InvProducto','inv_producto_id');
    }

    public function item()
    {
        return $this->belongsTo('App\Inventarios\InvProducto','inv_producto_id');
    }

    public function impuesto()
    {
        return $this->belongsTo('App\Contabilidad\Impuesto', 'impuesto_id');
    }

    public function motivo()
    {
        return $this->belongsTo('App\Inventarios\InvMotivo','vtas_motivo_id');
    }

    public function valor_impuesto_total()
    {
        return $this->valor_impuesto * $this->cantidad;
    }

    public static function get_registros_impresion( $doc_encabezado_id )
    {
        // WARNING vtas_motivo_id en realidad es inv_motivo_id
        return VtasDocRegistro::where('vtas_doc_registros.vtas_doc_encabezado_id', $doc_encabezado_id)
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_doc_registros.inv_producto_id')
                    ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'vtas_doc_registros.vtas_motivo_id')
                    ->select(
                                'vtas_doc_registros.id',
                                'vtas_doc_registros.estado',
                                'vtas_doc_registros.creado_por',
                                'vtas_doc_registros.modificado_por',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.unidad_medida2',
                                'inv_productos.referencia',
                                'inv_productos.codigo_barras',
                                'vtas_doc_registros.inv_producto_id',
                                'vtas_doc_registros.cantidad_pendiente',
                                'vtas_doc_registros.precio_unitario',
                                'vtas_doc_registros.cantidad',
                                'vtas_doc_registros.precio_total',
                                'vtas_doc_registros.impuesto_id',
                                'vtas_doc_registros.base_impuesto',
                                'vtas_doc_registros.tasa_impuesto',
                                'vtas_doc_registros.valor_impuesto',
                                'vtas_doc_registros.base_impuesto_total',
                                'vtas_doc_registros.cantidad_devuelta',
                                'vtas_doc_registros.tasa_descuento',
                                'vtas_doc_registros.valor_total_descuento',
                                'inv_motivos.descripcion as inv_motivo_descripcion',
                                'vtas_doc_registros.vtas_motivo_id'
                            )
                    ->get();
    }

    public static function get_un_registro( $registro_id )
    {
        // WARNING vtas_motivo_id en realidad es inv_motivo_id
        return VtasDocRegistro::where('vtas_doc_registros.id', $registro_id)
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'vtas_doc_registros.inv_producto_id')
                    ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'vtas_doc_registros.vtas_motivo_id')
                    ->select(
                                'vtas_doc_registros.id',
                                'vtas_doc_registros.vtas_doc_encabezado_id',
                                'vtas_doc_registros.estado',
                                'vtas_doc_registros.creado_por',
                                'vtas_doc_registros.modificado_por',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.referencia',
                                'inv_productos.codigo_barras',
                                'vtas_doc_registros.inv_producto_id',
                                'vtas_doc_registros.impuesto_id',
                                'vtas_doc_registros.precio_unitario',
                                'vtas_doc_registros.cantidad',
                                'vtas_doc_registros.cantidad_pendiente',
                                'vtas_doc_registros.precio_total',
                                'vtas_doc_registros.base_impuesto',
                                'vtas_doc_registros.tasa_impuesto',
                                'vtas_doc_registros.valor_impuesto',
                                'vtas_doc_registros.base_impuesto_total',
                                'vtas_doc_registros.cantidad_devuelta',
                                'vtas_doc_registros.tasa_descuento',
                                'vtas_doc_registros.valor_total_descuento',
                                'inv_motivos.descripcion as inv_motivo_descripcion'
                            )
                    ->get()
                    ->first();
    }

}
