<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;

use Carbon\Carbon;

use App\Nomina\NovedadTnl;
use App\Nomina\NomDocRegistro;
use Illuminate\Support\Facades\Auth;

class TiempoNoLaborado implements Estrategia
{

	protected $valor_a_pagar_eps = 0;
	protected $valor_a_pagar_arl = 0;
	protected $valor_a_pagar_afp = 0;
	protected $valor_a_pagar_empresa = 0;
	
	/*
		tipo_novedad_tnl: { incapacidad | permiso_remunerado | permiso_no_remunerado | suspencion }
		origen_incapacidad: { comun | laboral }
		clase_incapacidad: { enfermedad_general | licencia_maternidad | licencia_paternidad | accidente_trabajo | enfermedad_profesional}
	*/

	const CANTIDAD_HORAS_DIA_LABORAL = 8;

	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$lapso_documento = $liquidacion['documento_nomina']->lapso();

		$novedades = NovedadTnl::where( [
											[ 'nom_concepto_id', '=', $liquidacion['concepto']->id ],
											[ 'nom_contrato_id', '=', $liquidacion['empleado']->id ],
											[ 'cantidad_dias_pendientes_amortizar', '>', 0 ],
											[ 'fecha_inicial_tnl', '<=', $lapso_documento->fecha_final ],
											[ 'estado', '=', 'Activo' ]
										] )
								->get();

		$valores_novedades = [];        

        foreach( $novedades as $novedad )
        {			
			// NO se puede liquidar más tiempo del que tiene el documento en el lapso
			$horas_liquidadas_empleado = $this->get_horas_ya_liquidadas_en_el_lapso_del_documento( $liquidacion['documento_nomina'], $liquidacion['empleado'] );

			if ( $horas_liquidadas_empleado >= $liquidacion['documento_nomina']->tiempo_a_liquidar )
			{
				continue;
			}

			
			if ( $novedad->tipo_novedad_tnl == 'vacaciones' )
			{
				// Si aún no se han amortizado días de vacaciones, se salta; pues la primera amortización se debe hacer desde el mismo documento de vacaciones
				if ( $novedad->cantidad_dias_amortizados == 0 )
				{
					continue;
				}

				// Si ya se liquidaron vacaciones en el documento, se salta. Pues, quiere decir, que se está en el mismo documento de vacaciones.
				// Solo se liquidan días pendientes por amortizar en los documentos que NO son de vacaciones
				$registro_vacacion_liquidada = NomDocRegistro::where([
																		['nom_doc_encabezado_id','=',$liquidacion['documento_nomina']->id],
																		['nom_contrato_id','=',$liquidacion['empleado']->id],
																		['nom_concepto_id','=',$liquidacion['concepto']->id]
																	])
															->get()->first();
				if ( !is_null($registro_vacacion_liquidada) )
				{
					continue;
				}
			}

			$cantidad_horas_a_liquidar = abs( $this->calcular_cantidad_horas_liquidar_novedad( $novedad, $lapso_documento ) );

			$salario_x_hora = $liquidacion['empleado']->salario_x_hora();

        	$valor_real_novedad = $this->calcular_valores_liquidar_novedad( $novedad, $liquidacion['empleado'], $liquidacion['documento_nomina'], $cantidad_horas_a_liquidar, $salario_x_hora );

        	if ( $novedad->tipo_novedad_tnl == 'incapacidad' )
        	{
        		$this->crear_registro_concepto_pagado_por_la_empresa( $novedad, $liquidacion['documento_nomina'], $liquidacion['empleado'], $cantidad_horas_a_liquidar );
        	}        		

			$novedad->cantidad_dias_amortizados += ($cantidad_horas_a_liquidar / self::CANTIDAD_HORAS_DIA_LABORAL);
			$novedad->cantidad_dias_pendientes_amortizar -= ($cantidad_horas_a_liquidar / self::CANTIDAD_HORAS_DIA_LABORAL);
        	$novedad->save();
            
            $novedad_id = $novedad->id;

            // Cuando todo lo paga la empresa ( 2 primeros días ), no se crea registro, pues ya se creó uno de INCAPACIDAD PAGADA POR LA EMPRESA
            $valor_registro = $this->valor_a_pagar_eps + $this->valor_a_pagar_arl + $this->valor_a_pagar_afp;
			if ( ($novedad->tipo_novedad_tnl == 'incapacidad') && ($this->valor_a_pagar_empresa > 0) && ($valor_registro == 0) )
			{
				$valor_real_novedad = 0;
				$cantidad_horas_a_liquidar = 0;
				$novedad_id = 0;
			}

			$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza, $valor_real_novedad );

            $valores_novedades[] = [
	                                    'cantidad_horas' => $cantidad_horas_a_liquidar,
										'valor_devengo' => $valores->devengo,
										'valor_deduccion' => $valores->deduccion,
										'novedad_tnl_id' => $novedad_id
                                	];
        }

        return $valores_novedades;
	}



	public function get_horas_ya_liquidadas_en_el_lapso_del_documento( $documento_nomina, $empleado )
	{
		// En el lapso del documento, pueden haber varios documentos con tiempos liquidados
		$lapso = $documento_nomina->lapso();
		$registros_documento = NomDocRegistro::whereBetween( 'nom_doc_registros.fecha', [$lapso->fecha_inicial,$lapso->fecha_final] )
													->where( 'nom_doc_registros.nom_contrato_id', $empleado->id )
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

	public function calcular_valores_liquidar_novedad( &$novedad, $empleado, $documento_nomina, $cantidad_horas_a_liquidar, $salario_x_hora )
	{
		switch ( $novedad->tipo_novedad_tnl )
		{
			case 'incapacidad':
				
				$this->calcular_valores_liquidar_incapacidad( $novedad, $empleado, $cantidad_horas_a_liquidar );

				$novedad->valor_a_pagar_eps += $this->valor_a_pagar_eps;
				$novedad->valor_a_pagar_arl += $this->valor_a_pagar_arl;
				$novedad->valor_a_pagar_afp += $this->valor_a_pagar_afp;
				$novedad->valor_a_pagar_empresa += $this->valor_a_pagar_empresa;

				$valor_registro = $this->valor_a_pagar_eps + $this->valor_a_pagar_arl + $this->valor_a_pagar_afp;

				// Cuando todo lo paga la empresa ( 2 primeros días )
				if ( ($this->valor_a_pagar_empresa > 0) && ($valor_registro == 0) )
				{
					$valor_registro = $this->valor_a_pagar_empresa;
				}

				$valor_novedad = $valor_registro;
				
				break;
			
			case 'permiso_remunerado':
				$valor_novedad = $salario_x_hora * $cantidad_horas_a_liquidar;
				break;
			
			case 'permiso_no_remunerado':
				$valor_novedad = 0.0001;
				break;
			
			case 'suspencion':
				$valor_novedad = 0.0001;
				break;
			
			case 'vacaciones':
				$valor_novedad = 0.0001;
				break;
			
			default:
				# code...
				break;
		}

		return $valor_novedad;
	}

	public function crear_registro_concepto_pagado_por_la_empresa( $novedad, $documento_nomina, $empleado, $cantidad_horas_a_liquidar)
	{

		// Crear registro adicional en el documento (GASTO EMPRESA)
		if ( $this->valor_a_pagar_empresa > 0 )
		{
			$cantidad_horas = 0;
			if ( ($this->valor_a_pagar_eps+$this->valor_a_pagar_afp) == 0 )
			{
				$cantidad_horas = $cantidad_horas_a_liquidar;
			}	

			$registro = NomDocRegistro::create(
                            [ 'nom_doc_encabezado_id' => $documento_nomina->id ] + 
                            [ 'fecha' => $documento_nomina->fecha] + 
                            [ 'core_empresa_id' => $documento_nomina->core_empresa_id] +  
                            [ 'nom_concepto_id' => (int)config('nomina.id_concepto_pagar_empresa_en_incapacidades') ] + 
                            [ 'core_tercero_id' => $empleado->core_tercero_id ] + 
                            [ 'nom_contrato_id' => $empleado->id ] +
                            [ 'estado' => 'Activo' ] + 
                            [ 'creado_por' => Auth::user()->email ] + 
                            [ 'modificado_por' => '' ] +
                            [ 'cantidad_horas' => $cantidad_horas ] +
							[ 'valor_devengo' => round( $this->valor_a_pagar_empresa, 0) ] +
							[ 'valor_deduccion' => 0 ] +
							[ 'novedad_tnl_id' => $novedad->id ]
                        );

		}
	}

	public function calcular_valores_liquidar_incapacidad( $novedad, $empleado, $cantidad_horas_a_liquidar )
	{
		$porcentaje_liquidacion_legal = 66.66;
		if ( $novedad->cantidad_dias_amortizados > 90 )
		{
			$porcentaje_liquidacion_legal = 50;
		}
		
		// Las incapacidades de origen "laboral" se pagan al 100%
		// Las incapacidades de origen "comun" se pagan al 66.66%
		// La empresa puede asumir o NO el pago del otro 33.33%
		$porcentaje_a_pagar = 100;
		if ( (int)config('nomina.pago_salario_completo_en_incapacidades') == 0 && $novedad->origen_incapacidad != 'laboral' )
		{
			$porcentaje_a_pagar = $porcentaje_liquidacion_legal;
		}

		$horas_laborales = (int)config('nomina.horas_laborales');
		if ( $empleado->horas_laborales != 0 )
		{
			$horas_laborales = $empleado->horas_laborales;
		}

		// El IBC almacenado es el del mes anterior; si no existe, se toma el sueldo
		$valor_hora = $empleado->valor_ibc() / $horas_laborales;
		// Se debe respetar al salario mínimo
		if( $valor_hora < (float)config('nomina.SMMLV') / (float)config('nomina.horas_laborales') )
		{
			$valor_hora = (float)config('nomina.SMMLV') / (float)config('nomina.horas_laborales');
		}

		$valor_total_liquidar = $valor_hora * $cantidad_horas_a_liquidar;
		
		$valor_a_pagar_eps = 0;
		$valor_a_pagar_arl = 0;
		$valor_a_pagar_afp = 0;
		$valor_a_pagar_empresa = 0;

		if ( $novedad->origen_incapacidad == 'laboral' )
		{
			$valor_a_pagar_arl = $valor_total_liquidar;
		}else{

			// Los dos primeros días a cargo de la empresa (Artículo 3.2.1.10 decreto 780)
			$valor_a_pagar_empresa = $valor_total_liquidar * ($porcentaje_a_pagar / 100);

			$valor_porcentual = $valor_total_liquidar * ($porcentaje_a_pagar / 100);

			// Periodo de incapacidad de 3 a 180 día a cargo de la EPS (en el porcentaje legal) 
			// (Artículo 3.2.1.10 decreto 780)
			if ( $novedad->cantidad_dias_tnl > 2 )
			{
				$valor_a_pagar_eps = $valor_total_liquidar * ($porcentaje_liquidacion_legal / 100);
				$valor_a_pagar_empresa = $valor_porcentual - $valor_a_pagar_eps;
			}

			// Periodo de incapacidad mayor a 180 día a cargo del Fondo de pensiones (AFP) (en el porcentaje legal) 
			// (Artículo 41 ley 100 de 1993)
			if ( $novedad->cantidad_dias_amortizados > 180 )
			{
				$valor_a_pagar_afp = $valor_total_liquidar * ($porcentaje_liquidacion_legal / 100);
				$valor_a_pagar_empresa = $valor_porcentual - $valor_a_pagar_eps;
			}
		}

		$this->valor_a_pagar_eps = $valor_a_pagar_eps;
		$this->valor_a_pagar_arl = $valor_a_pagar_arl;
		$this->valor_a_pagar_afp = $valor_a_pagar_afp;
		$this->valor_a_pagar_empresa = $valor_a_pagar_empresa;
	}

	public function calcular_cantidad_horas_liquidar_novedad( $novedad, $lapso_documento )
	{
		$fecha_ini_novedad = strtotime( $novedad->fecha_inicial_tnl );
		$fecha_fin_novedad = strtotime( $novedad->fecha_final_tnl );

		$fecha_ini_documento = strtotime( $lapso_documento->fecha_inicial );
		$fecha_fin_documento = strtotime( $lapso_documento->fecha_final );
		
		// Se suma 1, pues se debe incluir el mismo día inicial.
		$dias_incluir = 1;
		// Además si es el mes de febrero se le suman esos adicionales
		if ( explode( '-', $lapso_documento->fecha_final)[1] == '02' )
		{
			$dias_incluir = 1 + ( 30 - (int)explode('-', $lapso_documento->fecha_final)[2] ); 
		}
			

		// Caso 1: Liquidar todo el tiempo de la novedad
		if ( $fecha_ini_novedad >= $fecha_ini_documento && $fecha_ini_novedad <= $fecha_fin_documento && $fecha_fin_novedad <= $fecha_fin_documento )
		{
			return $novedad->cantidad_horas_tnl;
		}

		// Caso 2: Liquidar una parte del tiempo de la novedad, el tiempo restante queda para siguiente(s) documento(s)
		if ( $fecha_ini_novedad >= $fecha_ini_documento && $fecha_ini_novedad < $fecha_fin_documento && $fecha_fin_novedad > $fecha_fin_documento )
		{
			$diferencia_en_dias = $this->diferencia_en_dias_entre_fechas( $novedad->fecha_inicial_tnl, $lapso_documento->fecha_final );
			return ( ( $diferencia_en_dias + $dias_incluir ) * self::CANTIDAD_HORAS_DIA_LABORAL ); 
		}

		// Caso 3: La novedad es vieja; ya tiene tiempos amortizados. Se continua amortizando desde la fecha inicial del lapso
		if ( $fecha_ini_novedad < $fecha_ini_documento )
		{
			if ( $fecha_fin_novedad > $fecha_fin_documento )
			{
				// Caso 3.1: liquidar todo el tiempo del lapso
				$diferencia_en_dias = $this->diferencia_en_dias_entre_fechas( $lapso_documento->fecha_inicial, $lapso_documento->fecha_final );
			}else{
				// Si la fecha final de la novedad es menor a la fecha del documento
				if( $novedad->fecha_final_tnl < $lapso_documento->fecha_inicial )
				{
					// Caso 3.2: liquidar desde la Fecha inicial del LAPSO ANTERIOR del documento hasta el tiempo final de la novedad
					$fecha_inicial_lapso_anterior = $this->get_fecha_inicial_lapso_anterior( $lapso_documento );
					$diferencia_en_dias = $this->diferencia_en_dias_entre_fechas( $fecha_inicial_lapso_anterior, $novedad->fecha_final_tnl ); // Sumar el mismo día de la fecha final de la TNL
				}else{
					// Caso 3.3: liquidar desde la Fecha inicial del Documento hasta el tiempo final de la novedad
					$diferencia_en_dias = $this->diferencia_en_dias_entre_fechas( $lapso_documento->fecha_inicial, $novedad->fecha_final_tnl );
				}		
			}

			return ( ( $diferencia_en_dias + $dias_incluir ) * self::CANTIDAD_HORAS_DIA_LABORAL ); // Se suma 1, pues se debe incluir el mismo día inicial.
		}
	}

	public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
	{
		$fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
		$fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

		return abs( $fecha_ini->diffInDays($fecha_fin) );
	}

	public function get_fecha_inicial_lapso_anterior( $lapso_documento )
	{
		$array_fecha = explode( '-', $lapso_documento->fecha_inicial );

		$anio_anterior = (int)$array_fecha[0];
        $mes_anterior = (int)$array_fecha[1];

		$anio_lapso = (int)$array_fecha[0];
        $mes_lapso = (int)$array_fecha[1];
        $dia_lapso = (int)$array_fecha[2];

        switch ( $dia_lapso)
        {
        	case '01':
        		$dia_anterior = '16';
        		$mes_anterior = $mes_lapso - 1;
        		
        		if ( $mes_lapso == 1 ) // Enero
        		{
        			$mes_anterior = '12';
        			$anio_anterior = $anio_lapso - 1;
        		}
        		
        		break;
        	
        	
        	case '16': // queda en el mismo mes y año
        		$dia_anterior = '01';
        		break;
        	
        	default:
        		# code...
        		break;
        }          

        return ( $anio_anterior . '-' . $this->formatear_numero_a_texto_dos_digitos( $mes_anterior ) . '-' . $dia_anterior );
	}



    public function formatear_numero_a_texto_dos_digitos( $numero )
    {
        if ( strlen($numero) == 1 )
        {
            return "0" . $numero;
        }

        return $numero;
    }

	public function retirar(NomDocRegistro $registro)
	{
		$novedad = $registro->novedad_tnl;
		
		if( is_null( $novedad ) )
		{
			if ( $registro->encabezado_documento->tipo_liquidacion == 'terminacion_contrato' )
	        {
	            return 0;
	        }

			dd( [ 'Class TiempoNoLaborado@retirar(), $registro->novedad_tnl = NULL', $registro] );
		}

		// Se elimina cuando solo hay Amortización de tiempo (no dinero) de vacaciones 
		if( $novedad->tipo_novedad_tnl == 'vacaciones')
		{
			if ( $registro->valor_devengo > 1 ) // Vacaciones en dinero
			{
				return 0;
			}
		}

		if( is_null( $registro->contrato ) )
		{
			dd( [ 'TiempoNoLaborado@retirar(), $registro->contrato = NULL', $registro] );
		}

		$lapso_documento = $registro->encabezado_documento->lapso();
		$cantidad_horas_a_liquidar = abs( $this->calcular_cantidad_horas_liquidar_novedad( $novedad, $lapso_documento ) );

		// Para todas las novedades
		if ( $registro->nom_concepto_id != (int)config('nomina.id_concepto_pagar_empresa_en_incapacidades')  )
		{
			$novedad->cantidad_dias_amortizados -= $cantidad_horas_a_liquidar / self::CANTIDAD_HORAS_DIA_LABORAL;
			$novedad->cantidad_dias_pendientes_amortizar += $cantidad_horas_a_liquidar / self::CANTIDAD_HORAS_DIA_LABORAL;
		}

		if ( $novedad->tipo_novedad_tnl == 'incapacidad' )
		{
			$this->calcular_valores_liquidar_incapacidad( $novedad, $registro->contrato, $cantidad_horas_a_liquidar );

			/*
				Refactorizar este procedimiento del valor pagado por la empresa 
			*/
			// Cuando todo lo paga la empresa ( 2 primeros días )
			$valor_registro = $this->valor_a_pagar_eps + $this->valor_a_pagar_arl + $this->valor_a_pagar_afp;
			if ( ($this->valor_a_pagar_empresa > 0) && ($valor_registro == 0) )
			{
				$novedad->cantidad_dias_amortizados -= $cantidad_horas_a_liquidar / self::CANTIDAD_HORAS_DIA_LABORAL;
				$novedad->cantidad_dias_pendientes_amortizar += $cantidad_horas_a_liquidar / self::CANTIDAD_HORAS_DIA_LABORAL;
			}
		
			$novedad->valor_a_pagar_eps -= $this->valor_a_pagar_eps;
			$novedad->valor_a_pagar_arl -= $this->valor_a_pagar_arl;
			$novedad->valor_a_pagar_afp -= $this->valor_a_pagar_afp;
			$novedad->valor_a_pagar_empresa -= $this->valor_a_pagar_empresa;

		}else{
			$novedad->valor_a_pagar_eps = 0;
			$novedad->valor_a_pagar_arl = 0;
			$novedad->valor_a_pagar_afp = 0;
			$novedad->valor_a_pagar_empresa = 0;
		}

		$novedad->save();

        $registro->delete();
	}
}