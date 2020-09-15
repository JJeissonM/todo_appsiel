<?php

namespace App\Inventarios;

use Illuminate\Database\Eloquent\Model;

use DB;

class InvDocRegistro extends Model
{
    //protected $table = 'teso_doc_registros_recaudos';

    protected $fillable = ['core_empresa_id','inv_doc_encabezado_id','inv_motivo_id','inv_bodega_id','inv_producto_id','costo_unitario','cantidad','costo_total','creado_por','modificado_por','estado','codigo_referencia_tercero','core_tercero_id'];

    public $campos_invisibles_linea_registro = ['inv_motivo_id','inv_bodega_id','inv_producto_id','costo_unitario','cantidad','costo_total'];

    public $campos_visibles_linea_registro = [ 
    											['','10px'],
    											['Producto','280px'],
    											['Motivo','200px'],
    											['Existencia',''],
    											['Costo Unit.',''],
    											['Cantidad',''],
    											['Costo Total',''],
    											['','10px']
    										];

    
    public function item()
    {
        return $this->belongsTo( 'App\Inventarios\InvProducto','inv_producto_id');
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_registros_impresion($doc_encabezado_id)
    {
        return InvDocRegistro::where('inv_doc_registros.inv_doc_encabezado_id',$doc_encabezado_id)
                    ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'inv_doc_registros.inv_motivo_id')
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_doc_registros.inv_producto_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_registros.inv_bodega_id')
                    ->select(
                                'inv_doc_registros.id',
                                'inv_doc_registros.estado',
                                'inv_doc_registros.inv_producto_id',
                                'inv_doc_registros.creado_por',
                                'inv_doc_registros.modificado_por',
                                'inv_doc_registros.costo_unitario',
                                'inv_doc_registros.inv_bodega_id',
                                'inv_doc_registros.cantidad',
                                'inv_doc_registros.costo_total',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.referencia',
                                'inv_productos.codigo_barras',
                                'inv_motivos.id AS inv_motivo_id',
                                'inv_motivos.descripcion AS motivo_descripcion',
                                'inv_bodegas.descripcion AS bodega_descripcion',
                                'inv_motivos.movimiento AS motivo_movimiento'
                            )
                    ->get();
    }

    /*
        Obtener un registro de encabezado de documento con sus datos relacionados
    */
    public static function get_un_registro( $registro_id )
    {
        return InvDocRegistro::where('inv_doc_registros.id', $registro_id)
                    ->leftJoin('inv_motivos', 'inv_motivos.id', '=', 'inv_doc_registros.inv_motivo_id')
                    ->leftJoin('inv_productos', 'inv_productos.id', '=', 'inv_doc_registros.inv_producto_id')
                    ->leftJoin('inv_bodegas', 'inv_bodegas.id', '=', 'inv_doc_registros.inv_bodega_id')
                    ->select(
                                'inv_doc_registros.id',
                                'inv_doc_registros.inv_doc_encabezado_id',
                                'inv_doc_registros.inv_producto_id',
                                'inv_doc_registros.estado',
                                'inv_doc_registros.creado_por',
                                'inv_doc_registros.modificado_por',
                                'inv_doc_registros.costo_unitario',
                                'inv_doc_registros.inv_bodega_id',
                                'inv_doc_registros.cantidad',
                                'inv_doc_registros.costo_total',
                                'inv_productos.id AS producto_id',
                                'inv_productos.descripcion AS producto_descripcion',
                                'inv_productos.unidad_medida1',
                                'inv_productos.referencia',
                                'inv_productos.codigo_barras',
                                'inv_motivos.id AS inv_motivo_id',
                                'inv_motivos.descripcion AS motivo_descripcion',
                                'inv_bodegas.descripcion AS bodega_descripcion',
                                'inv_motivos.movimiento AS motivo_movimiento'
                            )
                    ->get()
                    ->first();
    }
}
