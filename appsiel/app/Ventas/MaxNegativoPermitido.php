<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class MaxNegativoPermitido extends Model
{
    protected $table = 'vtas_max_negativos_permitidos';
	protected $fillable = ['inv_bodega_id', 'inv_producto_id', 'maximo_negativo'];
	public $encabezado_tabla = ['Bodega', 'Producto', 'Máximo negativo', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = MaxNegativoPermitido::leftJoin('inv_bodegas','inv_bodegas.id','=','vtas_max_negativos_permitidos.inv_bodega_id')->leftJoin('inv_productos','inv_productos.id','=','vtas_max_negativos_permitidos.inv_producto_id')->select('inv_bodegas.descripcion AS campo1', 'inv_productos.descripcion AS campo2', 'vtas_max_negativos_permitidos.maximo_negativo AS campo3', 'vtas_max_negativos_permitidos.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
