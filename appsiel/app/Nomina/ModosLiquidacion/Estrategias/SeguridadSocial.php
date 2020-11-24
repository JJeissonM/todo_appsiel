<?php

namespace App\Nomina\ModosLiquidacion\Estrategias;

use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Nomina\AgrupacionConcepto;
use App\Nomina\NomDocRegistro;

class SeguridadSocial implements Estrategia
{
	public function calcular(LiquidacionConcepto $liquidacion)
	{
		// Cada concepto de Seguridad social se debe liquidar al final, porque se usan los valores de otros conceptos para calcular su valor

        // Un concepto de seguridad social tiene una agrupación de conceptos para el cálculo de su valor; se deben suman los valores liquidados de cada concepto de su Agrupación para cálculo

        $conceptos_de_la_agrupacion = AgrupacionConcepto::find( $liquidacion->concepto->nom_agrupacion_id )->conceptos->pluck('id')->toArray();

        // Ingreso Base Cotización
        $total_ibc_devengos = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_doc_encabezado_id', $liquidacion->documento_nomina->id )
                                            ->where( 'core_tercero_id', $liquidacion->empleado->core_tercero_id )
                                            ->sum('valor_devengo');

        $total_ibc_deducciones = NomDocRegistro::whereIn( 'nom_concepto_id', $conceptos_de_la_agrupacion )
                                            ->where( 'nom_doc_encabezado_id', $liquidacion->documento_nomina->id )
                                            ->where( 'core_tercero_id', $liquidacion->empleado->core_tercero_id )
                                            ->sum('valor_deduccion');

        $total_IBC = ($total_ibc_devengos - $total_ibc_deducciones);

        return [$total_IBC * $liquidacion->concepto->porcentaje_sobre_basico / 100];
	}
}