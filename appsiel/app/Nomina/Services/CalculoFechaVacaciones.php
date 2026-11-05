<?php

namespace App\Nomina\Services;

use App\Nomina\DiaFestivo;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;
use Carbon\Carbon;

class CalculoFechaVacaciones
{
    /**
     * Calcula el último día de vacaciones según la parametrización del grupo.
     *
     * El día inicial hace parte del conteo. Domingos, festivos y sábados no
     * hábiles se incluyen en el periodo calendario, pero no consumen días de
     * vacaciones.
     */
    public function calcular($grupoEmpleadoId, $fechaInicial, $cantidadDiasTomados, $diasCompensados)
    {
        $parametro = ParametroLiquidacionPrestacionesSociales::where('concepto_prestacion', 'vacaciones')
            ->where('grupo_empleado_id', (int) $grupoEmpleadoId)
            ->first();

        if (is_null($parametro)) {
            throw new \InvalidArgumentException(
                'No existe parametrización de vacaciones para el grupo de empleados seleccionado.'
            );
        }

        $diasVacaciones = (int) $cantidadDiasTomados - (int) $diasCompensados;
        if ($diasVacaciones <= 0) {
            throw new \InvalidArgumentException(
                'La cantidad de días disfrutados debe ser mayor que cero.'
            );
        }

        $fechaActual = Carbon::createFromFormat('Y-m-d', $fechaInicial);
        if ($fechaActual === false || $fechaActual->format('Y-m-d') !== $fechaInicial) {
            throw new \InvalidArgumentException('La fecha inicial de vacaciones no es válida.');
        }

        $sabadoEsDiaHabil = (int) $parametro->sabado_es_dia_habil === 1;
        $diasContados = 0;
        $diasNoHabiles = 0;

        while ($diasContados < $diasVacaciones) {
            if ($this->esDiaNoHabil($fechaActual, $sabadoEsDiaHabil)) {
                $diasNoHabiles++;
            } else {
                $diasContados++;
            }

            if ($diasContados < $diasVacaciones) {
                $fechaActual->addDay();
            }
        }

        return [
            'fecha_fin' => $fechaActual->format('Y-m-d'),
            'dias_no_habiles' => $diasNoHabiles,
            'sabado_es_dia_habil' => $sabadoEsDiaHabil ? 1 : 0,
        ];
    }

    protected function esDiaNoHabil(Carbon $fecha, $sabadoEsDiaHabil)
    {
        if ($fecha->isSunday()) {
            return true;
        }

        if (!$sabadoEsDiaHabil && $fecha->isSaturday()) {
            return true;
        }

        return DiaFestivo::where('fecha', $fecha->format('Y-m-d'))->exists();
    }
}
