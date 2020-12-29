<?php

namespace App\Nomina\ModosLiquidacion\PrestacionesSociales;

use App\Nomina\ModosLiquidacion\LiquidacionPrestacionSocial;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;

use Auth;
use Carbon\Carbon;

use App\Nomina\NomDocRegistro;
use App\Nomina\AgrupacionConcepto;

class Vacaciones implements Estrategia
{
    const DIAS_BASE_LEGALES = 360;

    /*
        ** Hay vacaciones Compensadas y Disfrutadas
        ** Tener en cuenta días disfrutados (calendario) y días propios de las vacaciones
    */
	public function calcular(LiquidacionPrestacionSocial $liquidacion)
	{
        $parametros_prestacion = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion',$liquidacion['prestacion'])
                                                                        ->where('grupo_empleado_id',$liquidacion['empleado']->grupo_empleado_id)
                                                                        ->get()->first();

        if ( $liquidacion['documento_nomina']->tipo_liquidacion == 'terminacion_contrato' )
        {
            $valor_acumulado_provision = $this->get_valor_acumulado_provision();

            // Formula del calculo            

            $dias_pendientes = $this->get_dias_pendientes( $liquidacion['empleado'], $liquidacion['documento_nomina'], $parametros_prestacion );

            dd( $dias_pendientes );

            $valor_vacaciones = $vacaciones_total - $vacaciones_pagadas;
        }

        // Si no es terminación de contrado, las vacaciones deben estár programadas (registro de TNL)
        dd([$liquidacion['prestacion'],$liquidacion['empleado'],$liquidacion['documento_nomina']]);

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

		// Cada concepto de Seguridad social se debe liquidar al final, porque se usan los valores de otros conceptos para calcular su valor

        // Un concepto de seguridad social tiene una agrupación de conceptos para el cálculo de su valor; se deben suman los valores liquidados de cada concepto de su Agrupación para cálculo

        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $liquidacion['concepto']->nom_agrupacion_id )->conceptos->pluck('id')->toArray();

        // Ingreso Base Cotización
        $total_ibc_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_doc_encabezado_id', $liquidacion['documento_nomina']->id )
                                            ->where( 'core_tercero_id', $liquidacion['empleado']->core_tercero_id )
                                            ->sum( 'valor_devengo' );

        $total_ibc_deducciones = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_doc_encabezado_id', $liquidacion['documento_nomina']->id )
                                            ->where( 'core_tercero_id', $liquidacion['empleado']->core_tercero_id )
                                            ->sum('valor_deduccion');

        $total_IBC = ($total_ibc_devengos - $total_ibc_deducciones);

        $valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza,  $total_IBC * $liquidacion['concepto']->porcentaje_sobre_basico / 100 );

        return [ 
                    [
                        'cantidad_horas' => $liquidacion['documento_nomina']->tiempo_a_liquidar,
                        'valor_devengo' => $valores->devengo,
                        'valor_deduccion' => $valores->deduccion 
                    ]
                ];
	}

    public function get_dias_pendientes( $empleado, $documento_nomina, $parametros_prestacion )
    {
        /*
         crear modelo libro fiscal de vacaciones

         */
        $dias_pagados_vacaciones = 0;

        $dias_totales_laborados = $this->diferencia_en_dias_entre_fechas( $documento_nomina->fecha, $empleado->fecha_ingreso );

        $dias_totales_vacaciones = $dias_totales_laborados * $parametros_prestacion->dias_a_liquidar / self::DIAS_BASE_LEGALES;

        $dias_pendientes = $dias_totales_vacaciones - $dias_pagados_vacaciones;
        
        dd([$dias_totales_laborados, $dias_totales_vacaciones]);

        return $dias_pendientes;
    }

    public function get_valor_acumulado_provision()
    {
        return 0;
    }

    public function diferencia_en_dias_entre_fechas( string $fecha_inicial, string $fecha_final )
    {
        $fecha_ini = Carbon::createFromFormat('Y-m-d', $fecha_inicial);
        $fecha_fin = Carbon::createFromFormat('Y-m-d', $fecha_final );

        return abs( $fecha_ini->diffInDays($fecha_fin) );
    }

    public function retirar(NomDocRegistro $registro)
    {
        $registro->delete();
    }
}