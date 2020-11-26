<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

class TiempoLaborado implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, ( $liquidacion['empleado']->salario_x_hora() * $liquidacion['documento_nomina']->tiempo_a_liquidar ) * $liquidacion['concepto']->porcentaje_sobre_basico / 100 );

		return [ 
					[
						'cantidad_horas' => $liquidacion['documento_nomina']->tiempo_a_liquidar,
						'valor_devengo' => $valores->devengo,
						'valor_deduccion' => $valores->deduccion 
					]
				];
	}
}