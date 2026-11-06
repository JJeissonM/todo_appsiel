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

        if ($resumen['modo_encabezados'] === 'agrupaciones') {
            $servicio = app(ColumnasCalificadasService::class);
            $activas = $servicio->activas($anio, $periodoId, $cursoId, $asignaturaId, $calificacionesAuxiliares);
            return $this->ponderarActivas($calificacionesAuxiliares, $servicio->pesos($resumen, $activas));
        }

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

        if ($encabezadoGlobal && app(AgrupacionesColumnasService::class)->activo()) return 0;
        $encabezadoAnual = $periodoId === 0 && $encabezadoGlobal;
        if ($encabezadoAnual) {
            if (!$this->encabezadosCalificacionService->manejarEncabezadosFijosPorPeriodo() || (int)$encabezado->anio <= 0) {
                return 0;
            }
            $query = CalificacionAuxiliar::where('anio', (int)$encabezado->anio);
        } else {
            $query = CalificacionAuxiliar::where('id_periodo', $periodoId);
            if ((!$usarFijos || app(AgrupacionesColumnasService::class)->activo()) && !$encabezadoGlobal) {
                $query->where('curso_id', $encabezado->curso_id)
                    ->where('id_asignatura', $encabezado->asignatura_id);
            }
        }

        $actualizadas = 0;
        $query->chunk(200, function ($auxiliares) use (&$actualizadas, $encabezadoAnual) {
            foreach ($auxiliares as $auxiliar) {
                // Los periodos con configuración propia no dependen del encabezado anual.
                if ($encabezadoAnual && $this->encabezadosCalificacionService->periodoTieneEncabezadosFijos($auxiliar->id_periodo)) {
                    continue;
                }
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

    protected function ponderarActivas($datos, array $pesos)
    {
        $total = 0;
        foreach ($pesos as $i => $peso) $total += $this->valor($datos, 'C'.$i) * $peso;
        return round($total, 2);
    }

    public function recalcularAlcancePorAuxiliar($auxiliar)
    {
        if (!app(AgrupacionesColumnasService::class)->activo()) return;
        $servicio = app(ColumnasCalificadasService::class);
        $colegio = $auxiliar->id_colegio;
        $filas = $servicio->consulta($auxiliar->anio, $auxiliar->id_periodo, $auxiliar->curso_id, $auxiliar->id_asignatura, $colegio)->get();
        $resumen = $this->encabezadosCalificacionService->getResumenParaCarga($auxiliar->anio, $auxiliar->id_periodo, $auxiliar->curso_id, $auxiliar->id_asignatura);
        $activas = $servicio->activas($auxiliar->anio, $auxiliar->id_periodo, $auxiliar->curso_id, $auxiliar->id_asignatura, ['id_colegio'=>$colegio]);
        $pesos = $servicio->pesos($resumen, $activas);
        foreach ($filas as $fila) {
            $q = Calificacion::where('anio', $fila->anio)->where('id_periodo', $fila->id_periodo)
                ->where('curso_id', $fila->curso_id)->where('id_asignatura', $fila->id_asignatura)
                ->where('id_estudiante', $fila->id_estudiante)->where('codigo_matricula', $fila->codigo_matricula);
            if ($colegio !== null) $q->where('id_colegio', $colegio);
            $q->update(['calificacion'=>$this->ponderarActivas($fila, $pesos)]);
        }
    }

    protected function valor($datos, $columna)
    {
        if (is_array($datos)) {
            return isset($datos[$columna]) && is_numeric($datos[$columna]) ? (float)$datos[$columna] : 0;
        }

        return isset($datos->$columna) && is_numeric($datos->$columna) ? (float)$datos->$columna : 0;
    }
}
