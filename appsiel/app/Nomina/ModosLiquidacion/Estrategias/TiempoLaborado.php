<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;

use Carbon\Carbon;
use Auth;

use App\Nomina\ProgramacionVacacion;

class TiempoLaborado implements Estrategia
{
	protected $vacaciones_programadas;

	const CANTIDAD_HORAS_DIA_LABORAL = 8;

	public function calcular(LiquidacionConcepto $liquidacion)
	{
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

		// El concepto de Aprencies no se liquida automáticamente
		if ( (int)config('nomina.concepto_a_pagar_pasante_sena') == $liquidacion['concepto']->id )
		{
			return [ 
						[
							'cantidad_horas' => 0,
							'valor_devengo' => 0,
							'valor_deduccion' => 0 
						]
					];
		}

		if ( $liquidacion['empleado']->es_pasante_sena )
		{
			$this->liquidacion_pasante_sena( $liquidacion['documento_nomina'], $liquidacion['empleado'] );

			return [ 
						[
							'cantidad_horas' => 0,
							'valor_devengo' => 0,
							'valor_deduccion' => 0 
						]
					];
		}

		$horas_liquidadas_empleado = $this->get_horas_ya_liquidadas_en_el_lapso_del_documento( $liquidacion['documento_nomina'], $liquidacion['empleado'] );
		
		// Para que no se liquide el tiempo después de vacaciones, si estas termina dentro del mismo lapso del documento
		$horas_liquidadas_empleado += $this->get_horas_descontar_por_vacaciones( $horas_liquidadas_empleado, $liquidacion['documento_nomina'], $liquidacion['empleado'] );
		
		$salario_x_hora = $liquidacion['empleado']->salario_x_hora();
		
		$tiempo_a_liquidar = $this->get_tiempo_a_liquidar( $liquidacion['empleado'], $liquidacion['documento_nomina'], $horas_liquidadas_empleado );

		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, ( $salario_x_hora * $tiempo_a_liquidar ) * $liquidacion['concepto']->porcentaje_sobre_basico / 100 );

		return [ 
					[
						'cantidad_horas' => $tiempo_a_liquidar,
						'valor_devengo' => $valores->devengo,
						'valor_deduccion' => $valores->deduccion 
					]
				];
	}

	public function get_horas_ya_liquidadas_en_el_lapso_del_documento( $documento_nomina, $empleado )
	{
		// En el lapso del documento, pueden haber varios documentos con tiempos liquidados
		$lapso = $documento_nomina->lapso();
		$registros_documento = NomDocRegistro::whereBetween( 'nom_doc_registros.fecha', [$lapso->fecha_inicial,$lapso->fecha_final] )
													->where( 'nom_doc_registros.core_tercero_id', $empleado->core_tercero_id )
													->get();
		$horas_liquidadas_empleado = 0; 
        
        foreach ($registros_documento as $registro )
        {   
            if ( !is_null($registro->concepto) )
            {
            	// 1:Tiempo laborado, 7:Tiempo NO laborado
                if ( in_array($registro->concepto->modo_liquidacion_id, [1,7] ) )
                {
                    $horas_liquidadas_empleado += $registro->cantidad_horas;
                }
            }
        }

        return $horas_liquidadas_empleado;
	}

	// Cuando las vacaciones terminan en el lapso, NO se liquida el tiempo después del tiempo final de las mismas
	public function get_horas_descontar_por_vacaciones( $horas_liquidadas_empleado, $documento_nomina, $empleado )
	{
		if ( $horas_liquidadas_empleado >= $documento_nomina->tiempo_a_liquidar )
		{
			return 0;
		}
		
		$dias_a_descontar = 0;
		$lapso = $documento_nomina->lapso();

		// Caso 1. Las vacacaciones comienzan y terminan en el lapso, ya están en horas_liquidadas_empleado
		$vacaciones_programadas = ProgramacionVacacion::where([
																['tipo_novedad_tnl','=','vacaciones'],
																['nom_contrato_id','=',$empleado->id],
																['fecha_inicial_tnl','>=',$lapso->fecha_inicial],
																['fecha_final_tnl','<=',$lapso->fecha_final]
															])
														->get()->first();
		if ( !is_null($vacaciones_programadas) )
		{
			// Si NO se liquidaron vacaciones en el documento (No es documento de vacaciones)
			$registro_vacacion_liquidada = NomDocRegistro::where([
																	['nom_doc_encabezado_id','=',$documento_nomina->id],
																	['nom_contrato_id','=',$empleado->id],
																	['nom_concepto_id','=',$vacaciones_programadas->nom_concepto_id ]
																])
														->get()->first();
			if ( is_null($registro_vacacion_liquidada) )
			{
				return 0;
			}

			return ( $this->diferencia_en_dias_entre_fechas( $vacaciones_programadas->fecha_final_tnl, $lapso->fecha_final ) + 1 ) * self::CANTIDAD_HORAS_DIA_LABORAL;
		}

		// Caso 2. Hay programación de vacaciones que empiezan en el lapso pero aún no se ha liquidado la prestación social.
		$vacaciones_programadas = ProgramacionVacacion::where([
																['tipo_novedad_tnl','=','vacaciones'],
																['nom_contrato_id','=',$empleado->id],
																['fecha_inicial_tnl','<=',$lapso->fecha_final]
															])
														->get()->first();
														
		if ( !is_null($vacaciones_programadas) )
		{
			// Si NO se liquidaron vacaciones en el documento (no se han ejecutado las prestaciones sociales)
			$registro_vacacion_liquidada = NomDocRegistro::where([
																	['nom_doc_encabezado_id','=',$documento_nomina->id],
																	['nom_contrato_id','=',$empleado->id],
																	['nom_concepto_id','=',$vacaciones_programadas->nom_concepto_id ]
																])
														->get()->first();
			if ( is_null($registro_vacacion_liquidada) )
			{
				$fecha_final_dias = $lapso->fecha_final;
				$dias_adicionales = 1; // El mismo dia
				if ( $vacaciones_programadas->fecha_final_tnl < $lapso->fecha_final )
				{
					$fecha_final_dias = $vacaciones_programadas->fecha_final_tnl;
				}

				if ( explode('-', $lapso->fecha_final )[1] == '02' )
				{
					$dias_adicionales = 2; // Se completan los treinta días
				}

				$dias_a_descontar = $this->diferencia_en_dias_entre_fechas( $vacaciones_programadas->fecha_inicial_tnl, $fecha_final_dias ) + $dias_adicionales;
				return $dias_a_descontar * self::CANTIDAD_HORAS_DIA_LABORAL;
			}
		}

		return 0;
	}


	public function get_tiempo_a_liquidar( $empleado, $documento_nomina, $horas_liquidadas_empleado )
	{
		$fecha_inicial = $documento_nomina->lapso()->fecha_inicial;
		$fecha_final = $documento_nomina->lapso()->fecha_final;

		if ( $empleado->contrato_hasta < $fecha_inicial )
		{
			return 0;
		}

		// Caso 1: el contrato empieza dentro del lapso del documento, se restan días desde el inicio del documento hasta que empieza el contrato
		$tiempo_a_descontar_1 = 0;
		if ( $empleado->fecha_ingreso >= $fecha_inicial && $empleado->fecha_ingreso <= $fecha_final )
		{
			$tiempo_a_descontar_1 = $this->diferencia_en_dias_entre_fechas( $fecha_inicial, $empleado->fecha_ingreso ) * self::CANTIDAD_HORAS_DIA_LABORAL;
		}

		// Caso 2: el contrato termina dentro del lapso del documento, se restan los días después de la fecha terminación del contrato
		$tiempo_a_descontar_2 = 0;
		if ( $empleado->contrato_hasta >= $fecha_inicial && $empleado->contrato_hasta <= $fecha_final )
		{
			$tiempo_a_descontar_2 = $this->diferencia_en_dias_entre_fechas( $empleado->contrato_hasta, $fecha_final ) * self::CANTIDAD_HORAS_DIA_LABORAL;

			$aux_fecha = explode('-', $fecha_final);

			if ( (int)$aux_fecha[1] == 2 ) // Mes de febrero, completar 30 días
			{
				$tiempo_a_descontar_2 += 2 * self::CANTIDAD_HORAS_DIA_LABORAL;

				if ( (int)$aux_fecha[2] == 29 ) // Año Bisiesto, solo falta un día para 30
				{
					$tiempo_a_descontar_2 += self::CANTIDAD_HORAS_DIA_LABORAL;
				}
				
			}
		}
		
        $tiempo_a_liquidar = $documento_nomina->tiempo_a_liquidar - $tiempo_a_descontar_1 - $tiempo_a_descontar_2 - $horas_liquidadas_empleado;
        
        return $tiempo_a_liquidar;
	}

	public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
	{
		$fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
		$fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

		return abs( $fecha_ini->diffInDays($fecha_fin) );
	}

	public function liquidacion_pasante_sena( $documento_nomina, $empleado)
	{
		$concepto_id = (int)config('nomina.concepto_a_pagar_pasante_sena');

		$cant = NomDocRegistro::where( 'nom_doc_encabezado_id', $documento_nomina->id)
                                        ->where('core_tercero_id', $empleado->core_tercero_id)
                                        ->where('nom_concepto_id', $concepto_id)
                                        ->count();
        if ( $cant != 0 ) 
        {
            return 0;
        }

		// Liquidar concepto de sostenimiento y apoyo
		$valor_devengo_mes = (float)config('nomina.SMMLV') * (float)config('nomina.porcentaje_liquidacon_pasante_sena') / 100;
		$salario_x_hora = $valor_devengo_mes / 240;
		$horas_liquidadas_empleado = $this->get_horas_ya_liquidadas_en_el_lapso_del_documento( $documento_nomina, $empleado );
		$tiempo_a_liquidar = $this->get_tiempo_a_liquidar( $empleado, $documento_nomina, $horas_liquidadas_empleado );
		$valor_devengo = $salario_x_hora * $tiempo_a_liquidar;
		NomDocRegistro::create(
                                    ['nom_doc_encabezado_id' => $documento_nomina->id ] + 
                                    ['fecha' => $documento_nomina->fecha] + 
                                    ['core_empresa_id' => $documento_nomina->core_empresa_id] +  
                                    ['nom_concepto_id' => $concepto_id ] + 
                                    ['core_tercero_id' => $empleado->core_tercero_id ] + 
                                    ['nom_contrato_id' => $empleado->id ] + 
                                    ['estado' => 'Activo'] + 
                                    ['creado_por' => Auth::user()->email] + 
                                    ['valor_devengo' => round( $valor_devengo, 0) ]+  
                                    ['cantidad_horas' => $tiempo_a_liquidar ]+ 
                                    ['modificado_por' => '']
                                );
	}

	public function retirar( NomDocRegistro $registro )
	{
        $registro->delete();

        return 0;
	}
}