<?php

namespace App\Nomina\Services;

use InvalidArgumentException;

class RetencionFuenteLaboralService
{
    const LIMITE_RENTA_EXENTA_25_ANUAL_UVT = 790;
    const LIMITE_INTERESES_VIVIENDA_MENSUAL_UVT = 100;
    const LIMITE_SALUD_PREPAGADA_MENSUAL_UVT = 16;
    const LIMITE_DEPENDIENTES_MENSUAL_UVT = 32;
    const LIMITE_AHORRO_VOLUNTARIO_ANUAL_UVT = 3800;

    /**
     * Depura un pago laboral mensual y aplica la tabla del artículo 383 E.T.
     */
    public function calcular(array $datos)
    {
        $uvt = $this->positivo($datos, 'uvt');
        if ($uvt <= 0) {
            throw new InvalidArgumentException('Debe configurar una UVT vigente para liquidar la retención en la fuente.');
        }

        $pagos = $this->positivo($datos, 'total_pagos');
        $ingresosNoConstitutivos = min($pagos, $this->positivo($datos, 'ingresos_no_constitutivos'));
        $ingresoNeto = max(0, $pagos - $ingresosNoConstitutivos);

        $interesesVivienda = min(
            $this->positivo($datos, 'intereses_vivienda'),
            self::LIMITE_INTERESES_VIVIENDA_MENSUAL_UVT * $uvt
        );
        $saludPrepagada = min(
            $this->positivo($datos, 'salud_prepagada'),
            self::LIMITE_SALUD_PREPAGADA_MENSUAL_UVT * $uvt
        );
        $dependientes = !empty($datos['aplica_dependientes'])
            ? min($pagos * 0.10, self::LIMITE_DEPENDIENTES_MENSUAL_UVT * $uvt)
            : 0;

        $aportePensionVoluntaria = $this->positivo($datos, 'aportes_pension_voluntaria');
        $ahorroAfc = $this->positivo($datos, 'ahorros_afc');
        $ahorroVoluntarioSolicitado = $aportePensionVoluntaria + $ahorroAfc;
        $ahorroVoluntarioAcumulado = $this->positivo($datos, 'ahorro_voluntario_acumulado');
        $saldoAhorroVoluntarioAnual = max(
            0,
            (self::LIMITE_AHORRO_VOLUNTARIO_ANUAL_UVT * $uvt) - $ahorroVoluntarioAcumulado
        );
        $ahorroVoluntarioAplicado = min(
            $ahorroVoluntarioSolicitado,
            $ingresoNeto * 0.30,
            $saldoAhorroVoluntarioAnual
        );
        $factorAhorroAplicado = $ahorroVoluntarioSolicitado > 0
            ? $ahorroVoluntarioAplicado / $ahorroVoluntarioSolicitado
            : 0;
        $otrasRentasExentas = $ahorroVoluntarioAplicado + $this->positivo($datos, 'otras_rentas_exentas');
        $otrasDeducciones = $interesesVivienda + $saludPrepagada + $dependientes + $otrasRentasExentas;
        $subtotalAntesRenta25 = max(0, $ingresoNeto - $otrasDeducciones);

        $rentaExentaAcumulada = $this->positivo($datos, 'renta_exenta_25_acumulada');
        $saldoRentaExentaAnual = max(0, (self::LIMITE_RENTA_EXENTA_25_ANUAL_UVT * $uvt) - $rentaExentaAcumulada);
        $rentaExenta25Calculada = min($subtotalAntesRenta25 * 0.25, $saldoRentaExentaAnual);

        // Art. 388 E.T.: las deducciones y rentas exentas no pueden superar el 40%.
        $limiteCuarentaPorCiento = $ingresoNeto * 0.40;
        $deduccionesLimitadas = min($otrasDeducciones + $rentaExenta25Calculada, $limiteCuarentaPorCiento);
        $rentaExenta25Aplicada = min(
            $rentaExenta25Calculada,
            max(0, $limiteCuarentaPorCiento - min($otrasDeducciones, $limiteCuarentaPorCiento))
        );

        $baseRetencion = max(0, $ingresoNeto - $deduccionesLimitadas);
        $baseRetencionUvt = $baseRetencion / $uvt;
        $rango = $this->getRangoTablaUvt($baseRetencionUvt);
        $retencionUvt = max(0,
            (($baseRetencionUvt - $rango->uvts_finales_rango_anterior) * $rango->tarifa_marginal)
            + $rango->uvts_marginales
        );

        return [
            'intereses_vivienda' => $interesesVivienda,
            'salud_prepagada' => $saludPrepagada,
            'deduccion_por_dependientes' => $dependientes,
            'aportes_pension_voluntaria' => $aportePensionVoluntaria * $factorAhorroAplicado,
            'ahorros_afc' => $ahorroAfc * $factorAhorroAplicado,
            'ahorro_voluntario_aplicado' => $ahorroVoluntarioAplicado,
            'subtotal' => $subtotalAntesRenta25,
            'renta_trabajo_exenta' => $rentaExenta25Aplicada,
            'deducciones_rentas_exentas_aplicadas' => $deduccionesLimitadas,
            'limite_deducciones_rentas_exentas' => $limiteCuarentaPorCiento,
            'base_retencion' => $baseRetencion,
            'base_retencion_uvt' => $baseRetencionUvt,
            'rango' => $rango,
            'valor_retencion_uvt' => $retencionUvt,
            'valor_retencion' => round($retencionUvt * $uvt, 0),
            'porcentaje_efectivo' => $baseRetencion > 0 ? ($retencionUvt * $uvt / $baseRetencion) * 100 : 0,
        ];
    }

    public function getRangoTablaUvt($valorUvt)
    {
        $rangos = [
            [95, 0, 0, 0],
            [150, 95, 0.19, 0],
            [360, 150, 0.28, 10],
            [640, 360, 0.33, 69],
            [945, 640, 0.35, 162],
            [2300, 945, 0.37, 268],
            [PHP_INT_MAX, 2300, 0.39, 770],
        ];

        foreach ($rangos as $indice => $rango) {
            if ($valorUvt <= $rango[0]) {
                return (object)[
                    'fila_rango' => $indice + 1,
                    'uvts_iniciales' => $indice === 0 ? 0 : $rango[1],
                    'uvts_finales' => $rango[0],
                    'uvts_finales_rango_anterior' => $rango[1],
                    'tarifa_marginal' => $rango[2],
                    'uvts_marginales' => $rango[3],
                ];
            }
        }
    }

    protected function positivo(array $datos, $campo)
    {
        return max(0, isset($datos[$campo]) ? (float)$datos[$campo] : 0);
    }
}
