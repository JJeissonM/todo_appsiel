<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;

class TiempoLaborado implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$horas_liquidadas_empleado = $liquidacion['documento_nomina']->horas_liquidadas_empleado( $liquidacion['empleado']->core_tercero_id );

		// NO se puede liquidar más tiempo del que tiene el documento
		if ( $horas_liquidadas_empleado >= $liquidacion['documento_nomina']->tiempo_a_liquidar )
		{
			return [ 
						[
							'cantidad_horas' => 0,
							'valor_devengo' => 0,
							'valor_deduccion' => 0 
						]
					];
		}

		// Para salario integral
		if ( ( (int)config('nomina.concepto_salario_integral') == $liquidacion['concepto']->id ) && !$liquidacion['empleado']->salario_integral )
		{
			return [ 
						[
							'cantidad_horas' => 0,
							'valor_devengo' => 0,
							'valor_deduccion' => 0 
						]
					];
		}

		// Para salario integral
		if ( ( (int)config('nomina.concepto_salario_integral') != $liquidacion['concepto']->id ) && $liquidacion['empleado']->salario_integral )
		{
			return [ 
						[
							'cantidad_horas' => 0,
							'valor_devengo' => 0,
							'valor_deduccion' => 0 
						]
					];
		}

		//$horas_liquidadas_empleado = $liquidacion['documento_nomina']->horas_liquidadas_empleado( $liquidacion['empleado'] );

		$horas_liquidadas_empleado = NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
											->whereIn( 'nom_conceptos.modo_liquidacion_id', [1,7] )
											->where('nom_doc_registros.nom_doc_encabezado_id', $liquidacion['documento_nomina']->id )
											->where( 'nom_doc_registros.core_tercero_id', $liquidacion['empleado']->core_tercero_id )
											->sum('nom_doc_registros.cantidad_horas');

		$salario_x_hora = $liquidacion['empleado']->salario_x_hora();
		
		$tiempo_a_liquidar = $liquidacion['documento_nomina']->tiempo_a_liquidar - $horas_liquidadas_empleado;
		
		//dd( [$liquidacion['documento_nomina']->tiempo_a_liquidar, $horas_liquidadas_empleado] );

		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, ( $salario_x_hora * $tiempo_a_liquidar ) * $liquidacion['concepto']->porcentaje_sobre_basico / 100 );

		return [ 
					[
						'cantidad_horas' => $tiempo_a_liquidar,
						'valor_devengo' => $valores->devengo,
						'valor_deduccion' => $valores->deduccion 
					]
				];
	}

	public function retirar( NomDocRegistro $registro )
	{
        $registro->delete();

        return 0;
	}
}