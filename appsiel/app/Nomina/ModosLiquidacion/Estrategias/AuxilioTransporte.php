<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;

class AuxilioTransporte implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$cantidad_horas = 0;
		$valor_auxilio_empleado = 0;

		$valor_auxilio_x_hora = $liquidacion['concepto']->valor_fijo / (float)config('nomina.horas_laborales');

		switch ( $liquidacion['empleado']->liquida_subsidio_transporte )
		{
			case '1': // Si liquida, si Salario <= 2 SMMLV
				if ( $liquidacion['empleado']->sueldo <= ( (float)config('nomina.SMMLV') * 2 ) )
				{
					$cantidad_horas = $liquidacion['documento_nomina']->horas_liquidadas_tiempo_laborado_empleado( $liquidacion['empleado']->core_tercero_id );
					$valor_auxilio_empleado =  $valor_auxilio_x_hora * $cantidad_horas;
				}
				break;

			case '2': // Siempre
				$cantidad_horas = $liquidacion['documento_nomina']->horas_liquidadas_tiempo_laborado_empleado( $liquidacion['empleado']->core_tercero_id );
				$valor_auxilio_empleado =  $valor_auxilio_x_hora * $cantidad_horas;
				break;
			
			case '3': // No liquida
				# code...
				break;

			default:
				# code...
				break;
		}
		
		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, $valor_auxilio_empleado );

		return [ 
					[
						'cantidad_horas' => $cantidad_horas,
						'valor_devengo' => $valores->devengo,
						'valor_deduccion' => $valores->deduccion 
					]
				];
	}

	public function retirar(NomDocRegistro $registro)
	{
        $registro->delete();
	}
}