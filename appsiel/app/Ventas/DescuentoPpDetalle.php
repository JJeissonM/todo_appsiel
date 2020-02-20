<?php

namespace App\Ventas;

use Illuminate\Database\Eloquent\Model;

class DescuentoPpDetalle extends Model
{
    protected $table = 'vtas_descuentos_pp_detalles';
	protected $fillable = ['encabezado_id', 'dias_pp', 'descuento_pp'];
	public $encabezado_tabla = ['Encabezado descuento', 'Días pronto pago', 'Porcentaje descuento', 'Acción'];
	public static function consultar_registros()
	{
	    $registros = DescuentoPpDetalle::select('vtas_descuentos_pp_detalles.encabezado_id AS campo1', 'vtas_descuentos_pp_detalles.dias_pp AS campo2', 'vtas_descuentos_pp_detalles.descuento_pp AS campo3', 'vtas_descuentos_pp_detalles.id AS campo4')
	    ->get()
	    ->toArray();
	    return $registros;
	}
}
