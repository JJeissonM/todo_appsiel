<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\NomDocRegistro;
use App\Nomina\AgrupacionConcepto;

class Retefuente implements Estrategia
{
	protected $tabla_resumen = [
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
		$parametros_retefuente_empleado = (object)[
													'procedimiento' => 2,
													'porcentaje_fijo' => 3.23
												];

		switch ( $parametros_retefuente_empleado->procedimiento )
		{
			case '1': // Calculo mensual del porcentaje que corresponda según el monto del salario devengado
				//$ = $this->get_porcentaje();
				$valor_base_retencion = $this->get_valor_base_retencion( $total_devengos_gravados, );
				$valor_liquidacion = $this->determinar_valor_liquidacion_tabla( $valor_base_retencion, $valor_base_retencion, $valor_uvt );
				break;
			
			
			case '2': // Porcentaje fijo mensual, ya calculado semestralmente 
				$valor_base_depurada = $this->get_valor_base_depurada( $liquidacion['documento_nomina'], $liquidacion['empleado'] ); // subtotal

				// X	Renta de trabajo exenta del 25% numeral 10 del artículo 206 ET (W X 25%)
				$renta_trabajo_exenta = $valor_base_depurada * 25 / 100;
				$this->tabla_resumen['renta_trabajo_exenta'] = $renta_trabajo_exenta;

				// Y	Base de retención que se multiplica por el porcentaje fijo de retención
				$base_retencion = $valor_base_depurada - $renta_trabajo_exenta;
				$this->tabla_resumen['base_retencion'] = $base_retencion;

				$porcentaje_aplicado = $parametros_retefuente_empleado->porcentaje_fijo;
				$this->tabla_resumen['porcentaje_aplicado'] = $porcentaje_aplicado;

				$valor_liquidacion = $base_retencion * $porcentaje_aplicado / 100;
				$this->tabla_resumen['valor_liquidacion'] = $valor_liquidacion;

				break;
			
			default:
				
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

	public function get_valor_base_depurada( $documento_nomina, $empleado )
	{
		$vec_fecha = explode("-", $documento_nomina->fecha);
		$mes = $vec_fecha[1];
		$anio = $vec_fecha[0];

		//$conceptos_de_la_agrupacion = AgrupacionConcepto::find( $liquidacion['concepto']->nom_agrupacion_id )->conceptos->pluck('id')->toArray();

		$registros_liquidados_empleado = $empleado->get_registros_documentos_nomina_entre_fechas( $anio.'-'.$mes.'-01', $anio.'-'.$mes.'-30');

		$total_pagos = $this->get_total_pagos_empleado( $registros_liquidados_empleado );

		$total_deducciones = $this->get_total_deducciones_empleado( $registros_liquidados_empleado );

		// W	Subtotal 1 (F – N – R - V)
		$subtotal = $total_pagos - $total_deducciones;
		$this->tabla_resumen['subtotal'] = $subtotal;

		return $subtotal;
	}

	public function get_total_pagos_empleado( $pagos_empleado )
	{
        // A Sumatoria de los salarios básicos pagados durante los 12 meses anteriores (dinero o especie). Entran las incapacidades  y TNL
		$salario_basico = $pagos_empleado->whereIn( 'nom_concepto_id', [1,2,19,20,21,22,25,28,32,33,41,58,59,60,71,77,79] )->sum( 'valor_devengo' );
		$this->tabla_resumen['salario_basico'] = $salario_basico;
		
		// B Horas extras, bonificaciones y comisiones pagadas durante los 12 meses anteriores (sean o no factor salarial)
		$otros_devengos = $pagos_empleado->whereIn( 'nom_concepto_id', [3,4,5,6,7,8,9,11,12,13,14,26,30,42,43,44,57,80,81,82,83] )->sum( 'valor_devengo' );
		$this->tabla_resumen['otros_devengos'] = $otros_devengos;

		//C	Auxilios y subsidios pagados durante los 12 meses anteriores (directo o indirecto)
		
		//D	Cesantía, intereses sobre cesantía, prima mínima legal de servicios (sector privado) o de navidad (sector público) y vacaciones pagados durante los 12 meses anteriores
		$prestaciones_sociales = $pagos_empleado->whereIn( 'nom_concepto_id', [15,16,17,27,45,46,66,67] )->sum( 'valor_devengo' );
		$this->tabla_resumen['prestaciones_sociales'] = $prestaciones_sociales;

		// E	Demás pagos ordinario o extraordinario realizados durante los 12 meses anteriores

		//F	Total pagos efectuados durante los 12 meses anteriores (A+B+C+D+E)
		$total_pagos = $salario_basico + $otros_devengos + $prestaciones_sociales;
		$this->tabla_resumen['total_pagos'] = $total_pagos;

		return $total_pagos;
	}

	public function get_total_deducciones_empleado( $pagos_empleado )
	{

		/**  Ingresos no constitutivos de renta ni ganancia ocasional  **/
		// G	Pagos a terceros por concepto de alimentación (limitado según artículo 387-1 ET)
		//$pago_terceros_alimentacion = 0;

		// H	Viáticos ocasionales que constituyen reembolso de gastos soportados
		//$viaticos_ocacionales = 0;

		// I	Medios de transporte distintos del subsidio de transporte
		//$medios_transporte = 0;

		// J	Aportes obligatorios a salud efectuados por el trabajador
		$aportes_salud_obligatoria = $pagos_empleado->whereIn( 'nom_concepto_id', [64] )->sum( 'valor_deduccion' );
		$this->tabla_resumen['aportes_salud_obligatoria'] = $aportes_salud_obligatoria;
		
		// K	Aportes obligatorios a fondos de pensiones
		$aportes_pension_obligatoria = $pagos_empleado->whereIn( 'nom_concepto_id', [65,75] )->sum( 'valor_deduccion' );
		$this->tabla_resumen['aportes_pension_obligatoria'] = $aportes_pension_obligatoria;

		// L	Cesantía e intereses sobre cesantía. Pagos expresamente excluidos por el artículo 386 estatuto tributario.
		$pagos_cesantias_e_intereses = $pagos_empleado->whereIn( 'nom_concepto_id', [18,69,78] )->sum( 'valor_devengo' );
		$this->tabla_resumen['pagos_cesantias_e_intereses'] = $pagos_cesantias_e_intereses;
		
		// M	Demás pagos que no incrementan el patrimonio del trabajador
		
		// N	Total ingresos no constitutivos de renta 12 meses anteriores (G +H + I + J + K + L + M)
		$total_ingresos_no_constitutivos_renta = $aportes_salud_obligatoria + $aportes_pension_obligatoria + $pagos_cesantias_e_intereses;
		$this->tabla_resumen['total_ingresos_no_constitutivos_renta'] = $total_ingresos_no_constitutivos_renta;



		/**  Rentas exentas  **/
		// O	Aportes voluntarios a fondos de pensiones
		$aportes_pension_voluntaria = 0;
		$this->tabla_resumen['aportes_pension_voluntaria'] = $aportes_pension_voluntaria;

		// P	Ahorros cuentas AFC
		$ahorros_cuentas_afc = 0;
		$this->tabla_resumen['ahorros_cuentas_afc'] = $ahorros_cuentas_afc;
		
		// Q	Rentas de trabajo exentas numerales del 1 al 9 artículo 206 ET
		$rentas_trabajo_exentas = 0;
		$this->tabla_resumen['rentas_trabajo_exentas'] = $rentas_trabajo_exentas;
		
		// R	Total rentas exentas de los 12 meses anteriores (O + P +Q)
		$total_rentas_exentas = $aportes_pension_voluntaria + $ahorros_cuentas_afc + $rentas_trabajo_exentas;
		$this->tabla_resumen['total_rentas_exentas'] = $total_rentas_exentas;
		
		/**  Deducciones particulares  **/

		// S	Intereses o corrección monetaria en préstamos para adquisición de vivienda
		$intereses_vivienda = 0;
		$this->tabla_resumen['intereses_vivienda'] = $intereses_vivienda;
		// T	Pagos a salud (medicina prepagada y pólizas de seguros)
		/*
			esto debe ser traido del parametro del empleado.
		*/
		$salud_prepagada = 0;
		$this->tabla_resumen['salud_prepagada'] = $salud_prepagada;

		// U	Deducción por dependientes (artículo 387-1 ET) (máximo 10%)
		$deduccion_por_dependientes = $pagos_empleado->sum( 'valor_devengo' ) * 10 / 100;
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

	public function determinar_valor_liquidacion_tabla( $valor_comparacion, $valor_base_liquidacion, $valor_uvt )
	{
		/*
		   Rangos en UVT	   Porcentaje a liquidar
			0 a 95						0%
			>95  a 150					19%
			>150 a 360					28%
			>360 a 640					33%
			>640 a 945					35%
			>945 a 2300					37%
			>2300 en adelante			39%
		*/


			// PENDIENTE
			//
			//

		if ( $valor_comparacion >= ($valor_uvt * 4) )
        {
        	if ( $valor_comparacion < ($valor_uvt * 16) ) // 1%
        	{
				return ( $valor_base_liquidacion * 1 / 100 );
        	}

        	if ( $valor_comparacion < ($valor_uvt * 17) ) // 1.2%
        	{
				return ( $valor_base_liquidacion * 1.2 / 100 );
        	}

        	if ( $valor_comparacion < ($valor_uvt * 18) ) // 1.4%
        	{
				return ( $valor_base_liquidacion * 1.4 / 100 );
        	}

        	if ( $valor_comparacion < ($valor_uvt * 19) ) // 1.6%
        	{
				return ( $valor_base_liquidacion * 1.6 / 100 );
        	}

        	if ( $valor_comparacion < ($valor_uvt * 20) ) // 1.8%
        	{
				return ( $valor_base_liquidacion * 1.8 / 100 );
        	}

        	// >= 20SMMLV 2%
        	return ( $valor_base_liquidacion * 2 / 100 );
        }

        return 0;
	}

}