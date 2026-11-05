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

        $cantidadDiasTomados = $this->normalizarDiasEnteros(
            $cantidadDiasTomados,
            'La cantidad de días tomados debe ser un número entero mayor que cero.'
        );
        $diasCompensados = $this->normalizarDiasEnteros(
            $diasCompensados,
            'La cantidad de días compensados debe ser un número entero mayor o igual a cero.',
            true
        );

        if ($cantidadDiasTomados <= 0) {
            throw new \InvalidArgumentException(
                'La cantidad de días tomados debe ser un número entero mayor que cero.'
            );
        }

        if ($diasCompensados > ($cantidadDiasTomados / 2)) {
            throw new \InvalidArgumentException(
                'Los días compensados no pueden superar la mitad de los días de vacaciones reconocidos.'
            );
        }

        $diasVacaciones = $cantidadDiasTomados - $diasCompensados;

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
            'dias_disfrutados' => $diasVacaciones,
            'cantidad_dias_tnl' => $diasVacaciones + $diasNoHabiles,
            'sabado_es_dia_habil' => $sabadoEsDiaHabil ? 1 : 0,
        ];
    }

    protected function normalizarDiasEnteros($valor, $mensaje, $permiteCero = false)
    {
        if (!is_numeric($valor) || (float) $valor != floor((float) $valor)) {
            throw new \InvalidArgumentException($mensaje);
        }

        $valor = (int) $valor;
        if ($valor < 0 || (!$permiteCero && $valor === 0)) {
            throw new \InvalidArgumentException($mensaje);
        }

        return $valor;
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
