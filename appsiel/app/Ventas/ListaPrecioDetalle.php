<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\Inventarios\InvProducto;

class ListaPrecioDetalle extends Model
{
	// LOS PRECIOS SE MANEJAN CON IVA INCLUIDO
    protected $table = 'vtas_listas_precios_detalles';
	protected $fillable = ['lista_precios_id', 'inv_producto_id', 'fecha_activacion', 'precio'];
	public $encabezado_tabla = ['Lista de precios', 'Producto', 'Fecha activación', 'Precio', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ListaPrecioDetalle::leftJoin('vtas_listas_precios_encabezados','vtas_listas_precios_encabezados.id','=','vtas_listas_precios_detalles.lista_precios_id')
	    								->leftJoin('inv_productos','inv_productos.id','=','vtas_listas_precios_detalles.inv_producto_id')
	    								->select('vtas_listas_precios_encabezados.descripcion AS campo1', DB::raw('CONCAT(inv_productos.id," - ",inv_productos.descripcion) AS campo2'), 'vtas_listas_precios_detalles.fecha_activacion AS campo3', 'vtas_listas_precios_detalles.precio AS campo4', 'vtas_listas_precios_detalles.id AS campo5')
	    ->get()
	    ->toArray();
	    return $registros;
	}


	public static function get_precio_producto( $lista_precios_id, $fecha_activacion, $inv_producto_id )
	{
		$registro = ListaPrecioDetalle::where('lista_precios_id', $lista_precios_id)
									->where('fecha_activacion', '<=', $fecha_activacion)
									->where('inv_producto_id', $inv_producto_id)
									->get()
									->last();

		if ( is_null($registro) )
		{
			return InvProducto::find($inv_producto_id)->precio_venta;
		}else{
			return $registro->precio;
		}
	}
}
