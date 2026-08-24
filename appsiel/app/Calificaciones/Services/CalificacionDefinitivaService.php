<?php

namespace App\Calificaciones\Services;

use App\Calificaciones\Calificacion;
use App\Calificaciones\CalificacionAuxiliar;
use Illuminate\Support\Facades\Schema;

class CalificacionDefinitivaService
{
    protected $encabezadosCalificacionService;

    public function __construct(EncabezadosCalificacionService $encabezadosCalificacionService = null)
    {
        $this->encabezadosCalificacionService = $encabezadosCalificacionService ?: app(EncabezadosCalificacionService::class);
    }

    public function calcular($anio, $periodoId, $cursoId, $asignaturaId, $calificacionesAuxiliares)
    {
        $resumen = $this->encabezadosCalificacionService->getResumenParaCarga(
            (int)$anio,
            (int)$periodoId,
            (int)$cursoId,
            (int)$asignaturaId
        );

        $columnas = [];
        for ($indice = 1; $indice < 16; $indice++) {
            if (!$resumen['usar_encabezados_fijos'] || $resumen['columnas'][$indice]->configurado) {
                $columnas[] = $indice;
            }
        }

        if ($resumen['hay_pesos']) {
            $definitiva = 0;
            foreach ($columnas as $indice) {
                $definitiva += $this->valor($calificacionesAuxiliares, 'C' . $indice)
                    * ((float)$resumen['array_pesos'][$indice] / 100);
            }

            return round($definitiva, 2);
        }

        $suma = 0;
        $cantidad = 0;
        foreach ($columnas as $indice) {
            $valor = $this->valor($calificacionesAuxiliares, 'C' . $indice);
            if ($valor != 0) {
                $suma += $valor;
                $cantidad++;
            }
        }

        return $cantidad > 0 ? round($suma / $cantidad, 2) : 0;
    }

    public function tieneCalificacionesAuxiliares($calificacionesAuxiliares)
    {
        for ($indice = 1; $indice < 16; $indice++) {
            if ($this->valor($calificacionesAuxiliares, 'C' . $indice) != 0) {
                return true;
            }
        }

        return false;
    }

    public function recalcularPorEncabezado($encabezado)
    {
        if (!Schema::hasTable('sga_calificaciones') || !Schema::hasTable('sga_calificaciones_auxiliares')) {
            return 0;
        }

        $periodoId = (int)$encabezado->periodo_id;
        $this->encabezadosCalificacionService->olvidarPeriodo($periodoId);
        $usarFijos = $this->encabezadosCalificacionService->usarEncabezadosFijosEnPeriodo($periodoId);
		$encabezadoGlobal = is_null($encabezado->curso_id) && is_null($encabezado->asignatura_id);

        $query = CalificacionAuxiliar::where('id_periodo', $periodoId);
		if (!$usarFijos && !$encabezadoGlobal) {
            $query->where('curso_id', $encabezado->curso_id)
                ->where('id_asignatura', $encabezado->asignatura_id);
        }

        $actualizadas = 0;
        $query->chunk(200, function ($auxiliares) use (&$actualizadas) {
            foreach ($auxiliares as $auxiliar) {
                $definitiva = $this->calcular(
                    $auxiliar->anio,
                    $auxiliar->id_periodo,
                    $auxiliar->curso_id,
                    $auxiliar->id_asignatura,
                    $auxiliar
                );

                $calificaciones = Calificacion::where([
                    'anio' => $auxiliar->anio,
                    'id_periodo' => $auxiliar->id_periodo,
                    'curso_id' => $auxiliar->curso_id,
                    'id_asignatura' => $auxiliar->id_asignatura,
                    'id_estudiante' => $auxiliar->id_estudiante,
                    'codigo_matricula' => $auxiliar->codigo_matricula
                ]);

                if (!is_null($auxiliar->id_colegio)) {
                    $calificaciones->where('id_colegio', $auxiliar->id_colegio);
                }

                $actualizadas += $calificaciones->update(['calificacion' => $definitiva]);
            }
        });

        return $actualizadas;
    }

    protected function valor($datos, $columna)
    {
        if (is_array($datos)) {
            return isset($datos[$columna]) && is_numeric($datos[$columna]) ? (float)$datos[$columna] : 0;
        }

        return isset($datos->$columna) && is_numeric($datos->$columna) ? (float)$datos->$columna : 0;
    }
}
