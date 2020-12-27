<?php

namespace App\Compras;

use Illuminate\Database\Eloquent\Model;

class CondicionPagoProv extends Model
{
	protected $table = 'compras_condiciones_pago';
	protected $fillable = ['descripcion', 'dias_plazo', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Condición de pago', 'Días de plazo', 'Estado'];
	public static function consultar_registros($nro_registros)
	{
		return CondicionPagoProv::select('compras_condiciones_pago.descripcion AS campo1', 'compras_condiciones_pago.dias_plazo AS campo2', 'compras_condiciones_pago.estado AS campo3', 'compras_condiciones_pago.id AS campo4')
			->orderBy('compras_condiciones_pago.created_at', 'DESC')
			->paginate($nro_registros);
	}

	public static function opciones_campo_select()
	{
		$opciones = CondicionPagoProv::where('compras_condiciones_pago.estado', 'Activo')
			->select('compras_condiciones_pago.id', 'compras_condiciones_pago.descripcion')
			->get();

		$vec[''] = '';
		foreach ($opciones as $opcion) {
			$vec[$opcion->id] = $opcion->descripcion;
		}

		return $vec;
	}
}
