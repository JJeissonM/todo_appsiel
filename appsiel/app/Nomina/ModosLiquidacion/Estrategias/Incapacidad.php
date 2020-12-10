<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

class Incapacidad implements Estrategia
{
	/* 
		Tipo novedad
			* incapacidad
			* permiso_remunerado
			* permiso_no_remunerado
			* suspencion
			* vacaciones

		Origen de la incapacidad
			* comun
			* laboral
	*/

	protected $clases_incapacidades = [ 'enfermedad_general', 'licencia_maternidad', 'licencia_paternidad', 'accidente_trabajo', 'enfermedad_profesional' ];

	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$cantidad_horas = 0;
		$valor_auxilio = 0;

		switch ( $liquidacion['empleado']->liquida_subsidio_transporte )
		{
			case '1': // Si liquida, si Salario < 2 SMMLV
				if ( $liquidacion['empleado']->sueldo < config('nomina.SMMLV') )
				{
					$cantidad_horas = $liquidacion['documento_nomina']->tiempo_a_liquidar;
					$valor_auxilio = $liquidacion['concepto']->valor_fijo / ( config('nomina.horas_laborales') / $liquidacion['documento_nomina']->tiempo_a_liquidar );
				}
				break;

			case '2': // Siempre
				$cantidad_horas = $liquidacion['documento_nomina']->tiempo_a_liquidar;
				$valor_auxilio = $liquidacion['concepto']->valor_fijo / ( config('nomina.horas_laborales') / $liquidacion['documento_nomina']->tiempo_a_liquidar );
				break;
			
			case '3': // No liquida
				# code...
				break;

			default:
				# code...
				break;
		}
		
		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, $valor_auxilio );

		return [ 
					[
						'cantidad_horas' => $cantidad_horas,
						'valor_devengo' => $valores->devengo,
						'valor_deduccion' => $valores->deduccion 
					]
				];
	}
}