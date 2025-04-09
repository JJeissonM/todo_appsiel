<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;
use App\Nomina\AgrupacionConcepto;

class FondoSolidaridadPensional implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		// Para empleados con tipo contrato labor_contratada o pasantes SENA
        if ( $liquidacion['empleado']->clase_contrato == 'labor_contratada' || $liquidacion['empleado']->es_pasante_sena || $liquidacion['empleado']->tipo_cotizante == 32)
		{
			return [ 
						[
							'cantidad_horas' => 0,
							'valor_devengo' => 0,
							'valor_deduccion' => 0 
						]
					];
		}
        {
            return [ 
                        [
                            'cantidad_horas' => 0,
                            'valor_devengo' => 0,
                            'valor_deduccion' => 0 
                        ]
                    ];
        }

        $smmlv = (float)config('nomina.SMMLV');

        $lapso_documento = $liquidacion['documento_nomina']->lapso();

		$conceptos_de_la_agrupacion = AgrupacionConcepto::find( $liquidacion['concepto']->nom_agrupacion_id )->conceptos->pluck('id')->toArray();
		
		$valor_liquidacion = 0;

		$es_primera_quincena = true;
		if ( (int)explode("-", $lapso_documento->fecha_final)[2] >= 28 )
		{
			$es_primera_quincena = false;
		}
        if ( (int)config('nomina.calcular_valor_proyectado_fondo_solidaridad') == 1 )
		{
			$valor_base_neto_documento = $liquidacion['documento_nomina']->get_valor_neto_empleado_segun_grupo_conceptos( $conceptos_de_la_agrupacion, $liquidacion['empleado']->core_tercero_id );

			$valor_liquidado_primera_quincena = 0;
			$valor_base_neto_mes = 0;

			// Calcula una parte en la primera quincena, si el valor base proyectado cumple los montos
			if ( $es_primera_quincena )
			{
				// $valor_base_neto_documento ya tiene la sumatoria de sueldo y otros conceptos base para la liquidación, se le suma la proyección del sueldo en la segunda quincena
				$valor_comparacion = $valor_base_neto_documento + ( $liquidacion['empleado']->sueldo / 2);

				$valor_liquidacion = $this->calcular_valor_liquidacion_segun_tabla( $valor_comparacion, $valor_base_neto_documento, $smmlv );
			}else{
				// Ya se calculó una parte en la primera quincena
				// Se debe reliquidar el mes completo y descontar la diferencia

				// la sumatoria de los conceptos de la agrupacion
				$valor_base_neto_mes = $this->get_valor_neto_mes_completo( $liquidacion['empleado'], $lapso_documento, $conceptos_de_la_agrupacion );

				$valor_liquidacion_real_mes = $this->calcular_valor_liquidacion_segun_tabla( $valor_base_neto_mes, $valor_base_neto_mes, $smmlv );
			    
			    $valor_liquidado_primera_quincena = $this->get_valor_liquidado_primera_quincena( $lapso_documento, $liquidacion['empleado'], $liquidacion['concepto'] );

			    $valor_liquidacion = $valor_liquidacion_real_mes - $valor_liquidado_primera_quincena;
			}		
		}else{
			// Si no se proyectó el calculo del concepto, todo se liquida en la segunda quincena (cero en la primera quincena)
			if ( !$es_primera_quincena )
			{
				// Si es la segunda quincena
				$valor_liquidacion = $this->calcular_liquidacion_completa_fin_de_mes( $liquidacion['empleado'], $lapso_documento, $conceptos_de_la_agrupacion, $smmlv );
			}
		}

		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza,  $valor_liquidacion );
			
		return [ 
                [
                    'cantidad_horas' => 0,
                    'valor_devengo' => $valores->devengo,
                    'valor_deduccion' => $valores->deduccion 
                ]
            ];

	}

	public function retirar(NomDocRegistro $registro)
	{
        $registro->delete();
	}

	public function get_valor_neto_mes_completo( $empleado, $lapso_documento, $conceptos_de_la_agrupacion )
	{
		$fecha_final = $lapso_documento->fecha_final;
		$dia_final = explode('-', $fecha_final)[2];
		$fecha_inicial = str_replace($dia_final, '01', $fecha_final);

		$registros_liquidacion = $empleado->get_registros_documentos_nomina_entre_fechas( $fecha_inicial, $fecha_final);

		// Todo lo liquidado en el mes
		$total_devengos = $registros_liquidacion->whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
	                                            ->sum( 'valor_devengo' );

		$total_deducciones = $registros_liquidacion->whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
	                                            ->sum( 'valor_deduccion' );
	    
	    return ($total_devengos - $total_deducciones);
	}

	// Este método se llama en la liquidación de la segunda quincena
	public function get_valor_liquidado_primera_quincena( $lapso_documento, $empleado, $concepto )
	{
		$fecha_inicial = str_replace('16', '01', $lapso_documento->fecha_inicial);
		$fecha_final = str_replace('30', '15', $lapso_documento->fecha_final);
		$registro_concepto_primera_quincena = $empleado->get_registros_documentos_nomina_entre_fechas( $fecha_inicial, $fecha_final);

		$valor_devengo = $registro_concepto_primera_quincena->where( 'nom_concepto_id', $concepto->id)
                                        					->sum( 'valor_devengo' );

		$valor_deduccion = $registro_concepto_primera_quincena->where( 'nom_concepto_id', $concepto->id)
	                                            				->sum( 'valor_deduccion' );
	    
	    return ($valor_devengo + $valor_deduccion);
	}

	public function calcular_liquidacion_completa_fin_de_mes( $empleado, $lapso_documento, $conceptos_de_la_agrupacion, $smmlv )
	{	    
	    $neto = $this->get_valor_neto_mes_completo( $empleado, $lapso_documento, $conceptos_de_la_agrupacion );
		
		return $this->calcular_valor_liquidacion_segun_tabla( $neto, $neto, $smmlv );
	}


	public function calcular_valor_liquidacion_segun_tabla( $valor_comparacion, $valor_base_liquidacion, $smmlv )
	{
		/*
		   Rango salario	   Porcentaje a liquidar
			>=4 a <16					1%
			>=16  a 17					1,2%
			De 17 a 18					1,4%
			 De 18 a 19					1,6%
			De 19 a 20					1,8%
			Superiores a 20				2%
		*/

		if ( $valor_comparacion >= ($smmlv * 4) )
        {
        	if ( $valor_comparacion < ($smmlv * 16) ) // 1%
        	{
				return ( $valor_base_liquidacion * 1 / 100 );
        	}

        	if ( $valor_comparacion < ($smmlv * 17) ) // 1.2%
        	{
				return ( $valor_base_liquidacion * 1.2 / 100 );
        	}

        	if ( $valor_comparacion < ($smmlv * 18) ) // 1.4%
        	{
				return ( $valor_base_liquidacion * 1.4 / 100 );
        	}

        	if ( $valor_comparacion < ($smmlv * 19) ) // 1.6%
        	{
				return ( $valor_base_liquidacion * 1.6 / 100 );
        	}

        	if ( $valor_comparacion < ($smmlv * 20) ) // 1.8%
        	{
				return ( $valor_base_liquidacion * 1.8 / 100 );
        	}

        	// >= 20SMMLV 2%
        	return ( $valor_base_liquidacion * 2 / 100 );
        }

        return 0;
	}
}