<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\NomDocRegistro;
use App\Nomina\Services\Cotizante51Service;

class PensionObligatoria implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{

        // Para empleados con tipo contrato labor_contratada o pasantes SENA
        $cotizante51Service = new Cotizante51Service();
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

        $tiempo_a_liquidar =  NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_doc_encabezado_id', $liquidacion['documento_nomina']->id )
                                            ->where( 'core_tercero_id', $liquidacion['empleado']->core_tercero_id )
                                            ->sum('cantidad_horas');

        $total_IBC = ($total_ibc_devengos - $total_ibc_deducciones);
        if ($cotizante51Service->esCotizante51($liquidacion['empleado'])) {
            $diasLaborados = 0;
            if ((float)$liquidacion['empleado']->horas_laborales > 0) {
                $diasLaborados = round((float)$liquidacion['empleado']->horas_laborales / (float)config('nomina.horas_dia_laboral'), 0);
                if ($diasLaborados < 0) {
                    $diasLaborados = 0;
                }
                if ($diasLaborados > 30) {
                    $diasLaborados = 30;
                }
            } else {
                $diasLaborados = $cotizante51Service->getDiasLaboradosMes(
                    $liquidacion['empleado'],
                    round($tiempo_a_liquidar / (float)config('nomina.horas_dia_laboral'), 0)
                );
            }
            $total_IBC = $cotizante51Service->getIbcProporcionalPorDias($diasLaborados);
        }

        $valores = get_valores_devengo_deduccion( $liquidacion['concepto']->naturaleza,  $total_IBC * $liquidacion['concepto']->porcentaje_sobre_basico / 100 );

        return [ 
                    [
                        'cantidad_horas' => $tiempo_a_liquidar,
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
