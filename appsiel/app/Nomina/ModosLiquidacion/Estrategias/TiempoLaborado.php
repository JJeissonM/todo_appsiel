<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

class TiempoLaborado implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		dd( $liquidacion );


		return [( $salario_x_hora * $liquidacion->documento_nomina->tiempo_a_liquidar ) * $liquidacion->concepto->porcentaje_sobre_basico / 100];
	}
}