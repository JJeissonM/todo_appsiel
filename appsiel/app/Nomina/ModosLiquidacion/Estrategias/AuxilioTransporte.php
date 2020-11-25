<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

class AuxilioTransporte implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		return [$liquidacion->concepto->valor_fijo / ( config('nomina.horas_laborales') / $liquidacion->documento_nomina->tiempo_a_liquidar )];
	}
}