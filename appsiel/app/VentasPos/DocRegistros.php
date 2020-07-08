<?php

namespace App\VentasPos;

use Illuminate\Database\Eloquent\Model;

class DocRegistro extends Model
{
    protected $table = 'vtas_pos_doc_registros';
	protected $fillable = ['vtas_pos_doc_encabezado_id ', 'vtas_motivo_id ', 'inv_producto_id', 'precio_unitario', 'cantidad', 'precio_total', 'base_impuesto', 'tasa_impuesto', 'valor_impuesto', 'base_impuesto_total', 'tasa_descuento', 'valor_total_descuento', 'cantidad_devuelta', 'creado_por', 'modificado_por', 'estado'];
	public $encabezado_tabla = ['Fecha', 'Documento', 'Cliente', 'Producto', 'Precio unit.', 'Cantidad', 'Precio total', 'Acción'];
	public static function consultar_registros()
	{
	    return DocRegistro::select('vtas_pos_doc_registros.inv_producto_id AS campo1', 'vtas_pos_doc_registros.precio_unitario AS campo2', 'vtas_pos_doc_registros.cantidad AS campo3', 'vtas_pos_doc_registros.precio_total AS campo4', 'vtas_pos_doc_registros.base_impuesto AS campo5', 'vtas_pos_doc_registros.tasa_impuesto AS campo6', 'vtas_pos_doc_registros.valor_impuesto AS campo7', 'vtas_pos_doc_registros.id AS campo8')
	    ->get()
	    ->toArray();
	}
	public static function opciones_campo_select()
    {
        $opciones = DocRegistro::where('vtas_pos_doc_registros.estado','Activo')
                    ->select('vtas_pos_doc_registros.id','vtas_pos_doc_registros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
