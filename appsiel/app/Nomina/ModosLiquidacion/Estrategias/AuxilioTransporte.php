<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

class AuxilioTransporte implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, $liquidacion['concepto']->valor_fijo / ( config('nomina.horas_laborales') / $liquidacion['documento_nomina']->tiempo_a_liquidar ) );

		return [ 
					[
						'cantidad_horas' => $liquidacion['documento_nomina']->tiempo_a_liquidar,
						'valor_devengo' => $valores->devengo,
						'valor_deduccion' => $valores->deduccion 
					]
				];
	}
}