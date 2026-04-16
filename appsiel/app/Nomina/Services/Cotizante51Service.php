<?php

namespace App\Nomina\Services;

use App\Nomina\NomContrato;

class Cotizante51Service
{
    const TIPO_COTIZANTE_TIEMPO_PARCIAL = 51;

    public function esCotizante51(NomContrato $empleado)
    {
        return (int)$empleado->tipo_cotizante === self::TIPO_COTIZANTE_TIEMPO_PARCIAL;
    }

    public function getDiasLaboradosMes(NomContrato $empleado, $diasFallback = null)
    {
        if (!$this->esCotizante51($empleado)) {
            return (int)$diasFallback;
        }

        if (!is_null($empleado->dias_laborados_mes)) {
            return $this->normalizarDias((int)$empleado->dias_laborados_mes);
        }

        return $this->normalizarDias((int)$diasFallback);
    }

    public function getIbcProporcionalPorDias($diasLaborados, $smmlv = null)
    {
        $dias = $this->normalizarDias((int)$diasLaborados);
        $smmlv = is_null($smmlv) ? (float)config('nomina.SMMLV') : (float)$smmlv;

        if ($dias <= 0.9) {
            return 0;
        }

        if ($dias <= 7.9) {
            return $smmlv * 0.25;
        }

        if ($dias <= 14.9) {
            return $smmlv * 0.5;
        }

        if ($dias <= 21.9) {
            return $smmlv * 0.75;
        }

        return $smmlv;
    }

    public function getIbcRiesgosLaborales(NomContrato $empleado, $ibcBase, $smmlv = null)
    {
        if ($this->esCotizante51($empleado)) {
            return is_null($smmlv) ? (float)config('nomina.SMMLV') : (float)$smmlv;
        }

        return (float)$ibcBase;
    }

    protected function normalizarDias($dias)
    {
        if ($dias < 0) {
            return 0;
        }

        if ($dias > 30) {
            return 30;
        }

        return $dias;
    }
}
