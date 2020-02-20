<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class ListaDctoDetalle extends Model
{
    protected $table = 'vtas_listas_dctos_detalles';
	protected $fillable = ['lista_descuentos_id', 'inv_producto_id', 'fecha_activacion', 'descuento1', 'descuento2'];
	public $encabezado_tabla = ['Lista de descuentos', 'Producto', 'Fecha activación', 'Dcto. 1', 'Dcto. 2', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = ListaDctoDetalle::select('vtas_listas_dctos_detalles.lista_descuentos_id AS campo1', 'vtas_listas_dctos_detalles.inv_producto_id AS campo2', 'vtas_listas_dctos_detalles.fecha_activacion AS campo3', 'vtas_listas_dctos_detalles.descuento1 AS campo4', 'vtas_listas_dctos_detalles.descuento2 AS campo5', 'vtas_listas_dctos_detalles.id AS campo6')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
