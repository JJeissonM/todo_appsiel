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
        if ( $liquidacion['empleado']->clase_contrato == 'labor_contratada' || $liquidacion['empleado']->es_pasante_sena )
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
			$valor_liquidacion = 777;

		$es_primera_quincena = 1;
		if ( explode("-", $lapso_documento->fecha_final)[2] == '30' )
		{
			$es_primera_quincena = 0;
		}
        
        if ( (int)config('nomina.calcular_valor_proyectado_fondo_solidaridad') == 1 )
		{
			// Calcula una parte en la primera quincena, si el valor base proyectado cumple los montos


			
		}else{
			// Si no se proyectó el calculo del concepto, todo se liquida en la segunda quincena (cero en la primera quincena)
			if ( !$es_primera_quincena )
			{
				// Si es la segunda quincena
				$valor_liquidacion = $this->calcular_liquidacion_completa_fin_de_mes( $liquidacion['empleado'], $lapso_documento, $conceptos_de_la_agrupacion, $smmlv );
			}
		}


		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza,  $valor_liquidacion );
		if( $liquidacion['empleado']->id == 43)
		{
			dd( $valores );
		}
			
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

	public function calcular_liquidacion_completa_fin_de_mes( $empleado, $lapso_documento, $conceptos_de_la_agrupacion, $smmlv )
	{
		$fecha_final = $lapso_documento->fecha_final;
		$fecha_inicial = str_replace('30', '01', $fecha_final);
		$registros_liquidacion = $empleado->get_registros_documentos_nomina_entre_fechas( $fecha_inicial, $fecha_final);

		// Todo lo liquidado en el mes
		$total_devengos = $registros_liquidacion->whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
	                                            ->sum( 'valor_devengo' );

		$total_deducciones = $registros_liquidacion->whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
	                                            ->sum( 'valor_deduccion' );
	    
	    $neto = $total_devengos - $total_deducciones;
		
		return $this->determinar_valor_liquidacion_tabla( $neto, $neto, $smmlv );
	}

	public function determinar_valor_liquidacion_tabla( $valor_comparacion, $valor_base_liquidacion, $smmlv )
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

	public function get_valor_comparacion( $valor_base_liquidacion, $empleado )
	{
		if ( (int)config('nomina.calcular_valor_proyectado_fondo_solidaridad') == 0 )
		{
			// Este sería el valor base del documento de nómina, si es la primera quincena, tal vez no de la base.
			return $valor_base_liquidacion;
		}

		// Se proyecta la otra parte del sueldo
		return $valor_base_liquidacion + ( $empleado->sueldo / 2);
	}
}