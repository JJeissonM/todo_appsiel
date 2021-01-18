<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class DescuentoPpDetalle extends Model
{
	protected $table = 'vtas_descuentos_pp_detalles';
	protected $fillable = ['encabezado_id', 'dias_pp', 'descuento_pp'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Encabezado descuento', 'Días pronto pago', 'Porcentaje descuento'];
	public static function consultar_registros($nro_registros, $search)
	{
		$registros = DescuentoPpDetalle::select(
			'vtas_descuentos_pp_detalles.encabezado_id AS campo1',
			'vtas_descuentos_pp_detalles.dias_pp AS campo2',
			'vtas_descuentos_pp_detalles.descuento_pp AS campo3',
			'vtas_descuentos_pp_detalles.id AS campo4'
		)
			->where("vtas_descuentos_pp_detalles.encabezado_id", "LIKE", "%$search%")
			->orWhere("vtas_descuentos_pp_detalles.dias_pp", "LIKE", "%$search%")
			->orWhere("vtas_descuentos_pp_detalles.descuento_pp", "LIKE", "%$search%")
			->orderBy('vtas_descuentos_pp_detalles.created_at', 'DESC')
			->paginate($nro_registros);
		return $registros;
	}

	public static function sqlString($search)
	{
		$string = DescuentoPpDetalle::select(
			'vtas_descuentos_pp_detalles.encabezado_id AS ENCABEZADO_DESCUENTO',
			'vtas_descuentos_pp_detalles.dias_pp AS DÍAS_PRONTO_PAGO',
			'vtas_descuentos_pp_detalles.descuento_pp AS PORCENTAJE_DESCUENTO'
		)
			->where("vtas_descuentos_pp_detalles.encabezado_id", "LIKE", "%$search%")
			->orWhere("vtas_descuentos_pp_detalles.dias_pp", "LIKE", "%$search%")
			->orWhere("vtas_descuentos_pp_detalles.descuento_pp", "LIKE", "%$search%")
			->orderBy('vtas_descuentos_pp_detalles.created_at', 'DESC')
			->toSql();
		return str_replace('?', '"%' . $search . '%"', $string);
	}

	//Titulo para la exportación en PDF y EXCEL
	public static function tituloExport()
	{
		return "LISTADO DE DETALLES DE DESCUESTO PP";
	}
}
