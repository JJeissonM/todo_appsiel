<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\ParametrosRetefuenteEmpleado;
use App\Nomina\NomConcepto;

class Retefuente implements Estrategia
{
	public $tabla_resumen = [
								'salario_basico' => 0,
								'otros_devengos' => 0,
								'prestaciones_sociales' => 0,
								'total_pagos' => 0,
								'aportes_salud_obligatoria' => 0,
								'aportes_pension_obligatoria' => 0,
								'pagos_cesantias_e_intereses' => 0,
								'total_ingresos_no_constitutivos_renta' => 0,
								'aportes_pension_voluntaria' => 0,
								'ahorros_cuentas_afc' => 0,
								'rentas_trabajo_exentas' => 0,
								'total_rentas_exentas' => 0,
								'intereses_vivienda' => 0,
								'salud_prepagada' => 0,
								'deduccion_por_dependientes' => 0,
								'total_deducciones_particulares' => 0,
								'total_deducciones' => 0,
								'subtotal' => 0,
								'renta_trabajo_exenta' => 0,
								'base_retencion' => 0,
								'porcentaje_aplicado' => 0,
								'valor_liquidacion' => 0,
							];

	public function calcular(LiquidacionConcepto $liquidacion)
	{
		$lapso_documento = $liquidacion['documento_nomina']->lapso();

		$es_primera_quincena = true;
		if ( (int)explode("-", $lapso_documento->fecha_final)[2] >= 28 )
		{
			$es_primera_quincena = false;
		}

		if ( $es_primera_quincena )
		{
			return [
						[
		                    'cantidad_horas' => 0,
		                    'valor_devengo' => 0,
		                    'valor_deduccion' => 0,
		                    'tabla_resumen' => $this->tabla_resumen 
		                ]
			        ];
		}

		$parametros_retefuente_empleado = ParametrosRetefuenteEmpleado::where('nom_contrato_id', $liquidacion['empleado']->id)->orderBy('fecha_final_promedios')->get()->last();

        if ( is_null( $parametros_retefuente_empleado ) )  // falta validar a qué empleados se aplicará
        {
            $parametros_retefuente_empleado = (object)[
														'procedimiento' => 0,
														'porcentaje_fijo' => 0
													];
        }
			

		switch ( $parametros_retefuente_empleado->procedimiento )
		{
			case '1': // Calculo mensual del porcentaje que corresponda según el monto del salario devengado

				/*
						PROCESO PENDIENTE
				*/
				$valor_liquidacion = 0;
				break;
			
			
			case '2': // Porcentaje fijo mensual, ya calculado semestralmente 

				$vec_fecha = explode( "-", $liquidacion['documento_nomina']->fecha );
				if ( $liquidacion['fecha_final_promedios'] != '' )
				{
					$vec_fecha = explode( "-", $liquidacion['fecha_final_promedios'] );
				}
				$mes = $vec_fecha[1];
				$anio = $vec_fecha[0];
				$fecha_inicial = $anio.'-'.$mes.'-01';
				$fecha_final = $anio.'-'.$mes.'-30';

				$valor_base_depurada = $this->get_valor_base_depurada( $fecha_inicial, $fecha_final, $liquidacion['empleado'] ); // subtotal

				// X	Renta de trabajo exenta del 25% numeral 10 del artículo 206 ET (W X 25%)
				$renta_trabajo_exenta = $valor_base_depurada * 25 / 100;
				$this->tabla_resumen['renta_trabajo_exenta'] = $renta_trabajo_exenta;

				// Y	Base de retención que se multiplica por el porcentaje fijo de retención
				$base_retencion = $valor_base_depurada - $renta_trabajo_exenta;
				$this->tabla_resumen['base_retencion'] = $base_retencion;

				$porcentaje_aplicado = $parametros_retefuente_empleado->porcentaje_fijo;
				$this->tabla_resumen['porcentaje_aplicado'] = $porcentaje_aplicado;

				$valor_liquidacion = $this->redondear_a_unidad_seguida_ceros( $base_retencion * $porcentaje_aplicado / 100, 100, 'superior' );
				$this->tabla_resumen['valor_liquidacion'] = $valor_liquidacion;

				break;
			
			default:
				$valor_liquidacion = 0;
				break;
		}

		$valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza,  $valor_liquidacion );
			
		return [
					[
	                    'cantidad_horas' => 0,
	                    'valor_devengo' => $valores->devengo,
	                    'valor_deduccion' => $valores->deduccion,
	                    'tabla_resumen' => $this->tabla_resumen 
	                ]
		        ];

	}

	public function get_valor_base_depurada( $fecha_inicial, $fecha_final, $empleado )
	{
		$registros_liquidados_empleado = $empleado->get_registros_documentos_nomina_entre_fechas( $fecha_inicial, $fecha_final);

		$total_pagos = $this->get_total_pagos_empleado( $registros_liquidados_empleado );

		$total_deducciones = $this->get_total_deducciones_empleado( $registros_liquidados_empleado, $empleado );

		// W	Subtotal 1 (F – N – R - V)
		$subtotal = $total_pagos - $total_deducciones;
		$this->tabla_resumen['subtotal'] = $subtotal;

		return $subtotal;
	}

	public function get_total_pagos_empleado( $pagos_empleado )
	{
        // A Sumatoria de los salarios básicos pagados durante los 12 meses anteriores (dinero o especie). Entran las incapacidades  y TNL
        $conceptos_salario = NomConcepto::where( 'forma_parte_basico', 1)->get()->pluck('id')->toArray();
		$salario_basico = $pagos_empleado->whereIn( 'nom_concepto_id', $conceptos_salario )->sum( 'valor_devengo' );
		$this->tabla_resumen['salario_basico'] = $salario_basico;
		
		// B Horas extras, bonificaciones y comisiones pagadas durante los 12 meses anteriores (sean o no factor salarial)
		/*
				modo_liquidacion_id: [ 2: Manual, 3: Cuota, 4: Préstamo, 6: Auxilio de transporte ]
		*/
		$conceptos_otros_devengos = NomConcepto::whereIn( 'modo_liquidacion_id', [2,3,4,6] )
												->where( [['forma_parte_basico', '<>', 1]] )
												->get()->pluck('id')->toArray();
		$otros_devengos = $pagos_empleado->whereIn( 'nom_concepto_id', $conceptos_otros_devengos )->sum( 'valor_devengo' );
		$this->tabla_resumen['otros_devengos'] = $otros_devengos;

		//C	Auxilios y subsidios pagados durante los 12 meses anteriores (directo o indirecto)
		
		//D	Cesantía, intereses sobre cesantía, prima mínima legal de servicios (sector privado) o de navidad (sector público) y vacaciones pagados durante los 12 meses anteriores
		/*
				modo_liquidacion_id: [ 14: Prima Legal, 16: Intereses de cesantías, 17: Cesantías pagadas ]
		*/
		$conceptos_prestaciones_sociales = NomConcepto::whereIn( 'modo_liquidacion_id', [ 14, 16, 17 ] )
														->where( [['forma_parte_basico', '<>', 1]] )
														->get()->pluck('id')->toArray();
		$prestaciones_sociales = $pagos_empleado->whereIn( 'nom_concepto_id', $conceptos_prestaciones_sociales )->sum( 'valor_devengo' );
		$this->tabla_resumen['prestaciones_sociales'] = $prestaciones_sociales;

		// E	Demás pagos ordinario o extraordinario realizados durante los 12 meses anteriores

		//F	Total pagos efectuados durante los 12 meses anteriores (A+B+C+D+E)
		$total_pagos = $salario_basico + $otros_devengos + $prestaciones_sociales;
		$this->tabla_resumen['total_pagos'] = $total_pagos;

		return $total_pagos;
	}

	public function get_total_deducciones_empleado( $pagos_empleado, $empleado )
	{
		$parametros_retencion = $empleado->parametros_retefuente();
		$aportes_pension_voluntaria = 0;
		$ahorros_cuentas_afc = 0;
		$rentas_trabajo_exentas = 0;
		$intereses_vivienda = 0;
		$salud_prepagada = 0;

        if ( !is_null( $parametros_retencion ) )  // falta validar a qué empleados se aplicará
        {
            $aportes_pension_voluntaria = $parametros_retencion->deduccion_aportes_pension_voluntaria;
			$ahorros_cuentas_afc = $parametros_retencion->deduccion_ahorros_cuentas_afc;
			$rentas_trabajo_exentas = $parametros_retencion->deduccion_rentas_trabajo_exentas;
			$intereses_vivienda = $parametros_retencion->deduccion_intereses_vivienda;
			$salud_prepagada = $parametros_retencion->deduccion_salud_prepagada;
        }

		/**  Ingresos no constitutivos de renta ni ganancia ocasional  **/
		// G	Pagos a terceros por concepto de alimentación (limitado según artículo 387-1 ET)
		//$pago_terceros_alimentacion = 0;

		// H	Viáticos ocasionales que constituyen reembolso de gastos soportados
		//$viaticos_ocacionales = 0;

		// I	Medios de transporte distintos del subsidio de transporte
		//$medios_transporte = 0;

		// J	Aportes obligatorios a salud efectuados por el trabajador
		/*
				modo_liquidacion_id: [ 12: Salud obligatoria ]
		*/
		$conceptos_salud_obligatoria = NomConcepto::whereIn( 'modo_liquidacion_id', [ 12 ] )
														->where( [ ['forma_parte_basico', '<>', 1] ] )
														->get()->pluck('id')->toArray();
		$aportes_salud_obligatoria = $pagos_empleado->whereIn( 'nom_concepto_id', $conceptos_salud_obligatoria )->sum( 'valor_deduccion' );
		$this->tabla_resumen['aportes_salud_obligatoria'] = $aportes_salud_obligatoria;
		
		// K	Aportes obligatorios a fondos de pensiones
		/*
				modo_liquidacion_id: [ 13: Pensión obligatoria, 10: FondoSolidaridadPensional ]
		*/
		$conceptos_pension_obligatoria = NomConcepto::whereIn( 'modo_liquidacion_id', [ 10, 13 ] )
														->where( [ ['forma_parte_basico', '<>', 1] ] )
														->get()->pluck('id')->toArray();
		$aportes_pension_obligatoria = $pagos_empleado->whereIn( 'nom_concepto_id', $conceptos_pension_obligatoria )->sum( 'valor_deduccion' );
		$this->tabla_resumen['aportes_pension_obligatoria'] = $aportes_pension_obligatoria;

		// L	Cesantía e intereses sobre cesantía. Pagos expresamente excluidos por el artículo 386 estatuto tributario.
		/*
				modo_liquidacion_id: [ 16: Intereses de cesantías, 17: Cesantías pagadas ]
		*/
		$conceptos_cesantias_e_intereses = NomConcepto::whereIn( 'modo_liquidacion_id', [ 16, 17 ] )
														->where( [ ['forma_parte_basico', '<>', 1] ] )
														->get()->pluck('id')->toArray();
		$pagos_cesantias_e_intereses = $pagos_empleado->whereIn( 'nom_concepto_id', $conceptos_cesantias_e_intereses )->sum( 'valor_devengo' );
		$this->tabla_resumen['pagos_cesantias_e_intereses'] = $pagos_cesantias_e_intereses;
		
		// M	Demás pagos que no incrementan el patrimonio del trabajador
		
		// N	Total ingresos no constitutivos de renta 12 meses anteriores (G +H + I + J + K + L + M)
		$total_ingresos_no_constitutivos_renta = $aportes_salud_obligatoria + $aportes_pension_obligatoria + $pagos_cesantias_e_intereses;
		$this->tabla_resumen['total_ingresos_no_constitutivos_renta'] = $total_ingresos_no_constitutivos_renta;



		/**  Rentas exentas  **/
		// O	Aportes voluntarios a fondos de pensiones
		$this->tabla_resumen['aportes_pension_voluntaria'] = $aportes_pension_voluntaria;

		// P	Ahorros cuentas AFC
		$this->tabla_resumen['ahorros_cuentas_afc'] = $ahorros_cuentas_afc;
		
		// Q	Rentas de trabajo exentas numerales del 1 al 9 artículo 206 ET
		$this->tabla_resumen['rentas_trabajo_exentas'] = $rentas_trabajo_exentas;
		
		// R	Total rentas exentas de los 12 meses anteriores (O + P +Q)
		$total_rentas_exentas = $aportes_pension_voluntaria + $ahorros_cuentas_afc + $rentas_trabajo_exentas;
		$this->tabla_resumen['total_rentas_exentas'] = $total_rentas_exentas;
		
		/**  Deducciones particulares  **/

		// S	Intereses o corrección monetaria en préstamos para adquisición de vivienda
		$this->tabla_resumen['intereses_vivienda'] = $intereses_vivienda;
		// T	Pagos a salud (medicina prepagada y pólizas de seguros)
		/*
			esto debe ser traido del parametro del empleado.
		*/
		$this->tabla_resumen['salud_prepagada'] = $salud_prepagada;

		// U	Deducción por dependientes (artículo 387-1 ET) (máximo 10%)
		$base_dependientes = $pagos_empleado->sum( 'valor_devengo' ) - $this->tabla_resumen['pagos_cesantias_e_intereses'];
		$deduccion_por_dependientes = $base_dependientes * 10 / 100;
		$this->tabla_resumen['deduccion_por_dependientes'] = $deduccion_por_dependientes;

		// V	Total deducciones de los 12 meses anteriores (N + R + T)
		$total_deducciones_particulares = $intereses_vivienda + $salud_prepagada + $deduccion_por_dependientes;
		$this->tabla_resumen['total_deducciones_particulares'] = $total_deducciones_particulares;


		$total_deducciones = $total_ingresos_no_constitutivos_renta + $total_rentas_exentas + $total_deducciones_particulares;
		$this->tabla_resumen['total_deducciones'] = $total_deducciones;

		return $total_deducciones;
	}

	public function retirar(NomDocRegistro $registro)
	{
        $registro->delete();
	}

	public function get_rango_tabla_uvts( $valor_uvt )
	{
		/*
			ARTíCULO 383.  Del Estatuto Tributario ( Modificado por LEY-2010-DEL-27-DE-DICIEMBRE-DE-2019 )
		   Rangos en UVT	   Porcentaje a liquidar
			0 a 95						0%
			>95  a 150					19%
			>150 a 360					28%
			>360 a 640					33%
			>640 a 945					35%
			>945 a 2300					37%
			>2300 en adelante			39%
		*/

		if ( $valor_uvt <= 95 ) // 1%
    	{
    		return (object)[ 
        						'fila_rango' => 1,
        						'uvts_iniciales' => 0,
        						'uvts_finales' => 95,
        						'uvts_finales_rango_anterior' => 0,
        						'tarifa_marginal' => 0,
        						'uvts_marginales' => 0
        					];
    	}

		if ( $valor_uvt > 95 && $valor_uvt <= 150  )
    	{
    		return (object)[ 
        						'fila_rango' => 2,
        						'uvts_iniciales' => 95,
        						'uvts_finales' => 150,
        						'uvts_finales_rango_anterior' => 95,
        						'tarifa_marginal' => 19 / 100,
        						'uvts_marginales' => 0
        					];
    	}

		if ( $valor_uvt > 150 && $valor_uvt <= 360  )
    	{
    		return (object)[ 
        						'fila_rango' => 3,
        						'uvts_iniciales' => 150,
        						'uvts_finales' => 360,
        						'uvts_finales_rango_anterior' => 150,
        						'tarifa_marginal' => 28 / 100,
        						'uvts_marginales' => 10
        					];
    	}

		if ( $valor_uvt > 360 && $valor_uvt <= 640  )
    	{
    		return (object)[ 
        						'fila_rango' => 4,
        						'uvts_iniciales' => 360,
        						'uvts_finales' => 640,
        						'uvts_finales_rango_anterior' => 360,
        						'tarifa_marginal' => 33 / 100,
        						'uvts_marginales' => 69
        					];
    	}

		if ( $valor_uvt > 640 && $valor_uvt <= 945  )
    	{
    		return (object)[ 
        						'fila_rango' => 5,
        						'uvts_iniciales' => 640,
        						'uvts_finales' => 945,
        						'uvts_finales_rango_anterior' => 640,
        						'tarifa_marginal' => 35 / 100,
        						'uvts_marginales' => 162
        					];
    	}

		if ( $valor_uvt > 945 && $valor_uvt <= 2300  )
    	{
    		return (object)[ 
        						'fila_rango' => 6,
        						'uvts_iniciales' => 945,
        						'uvts_finales' => 2300,
        						'uvts_finales_rango_anterior' => 945,
        						'tarifa_marginal' => 37 / 100,
        						'uvts_marginales' => 268
        					];
    	}

    	// > 2300 UVTs
    	return (object)[ 
    						'fila_rango' => 7,
    						'uvts_iniciales' => 2300,
    						'uvts_finales' => 999999,
    						'uvts_finales_rango_anterior' => 2300,
    						'tarifa_marginal' => 39 / 100,
    						'uvts_marginales' => 770
    					];
	}

	public function redondear_a_unidad_seguida_ceros( $numero, $valor_unidad_seguida_ceros, $tipo_redondeo)
    {
        if ( $numero == 0 )
        {
            return 0;
        }
        
        $valor_redondeado = $numero;

        if ( $valor_unidad_seguida_ceros != 0 )
        {
            $decimal = $numero / $valor_unidad_seguida_ceros;
            $aux = (string) $decimal;
            // Si, no existe el punto en el string $aux, $numero no necesita ser redondeado
            if ( (int)strpos( $aux, "." ) == 0 )
            {
                return $numero;
            }

            // Extraer la parte decimal
            $residuo = substr( $aux, strpos( $aux, "." ) );

            $valor_residuo_tipo_unidad = $residuo * $valor_unidad_seguida_ceros;

            switch ( $tipo_redondeo )
            {
                case 'superior':
                    $diferecia = $valor_unidad_seguida_ceros - $valor_residuo_tipo_unidad;
                    $valor_redondeado = $numero + $diferecia;
                    break;
                
                case 'inferior':
                    $valor_redondeado = $numero - $valor_residuo_tipo_unidad;
                    break;
                
                default:
                    $valor_redondeado = $numero;
                    break;
            }
                    
        }

        return $valor_redondeado;
    }

}