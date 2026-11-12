<?php

namespace App\Calificaciones\Services;

use App\Calificaciones\Calificacion;
use App\Calificaciones\NotaNivelacion;

class PromediosAsignaturasService
{
    public function calificaciones($colegioId, $cursoId, $periodoLectivoId, $excluirAsistencias = false)
    {
        $query = Calificacion::where('id_colegio', $colegioId)->where('curso_id', $cursoId)
            ->whereIn('id_periodo', function ($query) use ($periodoLectivoId) {
                $query->select('id')->from('sga_periodos')->where('periodo_lectivo_id', $periodoLectivoId);
            });
        if ($excluirAsistencias) $query->where('id_asignatura', '<>', config('calificaciones.asignatura_id_para_asistencias'));
        $calificaciones = $query->orderBy('id')->get(['id_periodo', 'curso_id', 'id_estudiante', 'id_asignatura', 'calificacion']);
        return $this->aplicarNivelaciones($calificaciones, $cursoId);
    }

    public function aplicarNivelaciones($calificaciones, $cursoId)
    {
        if ($calificaciones->isEmpty()) return $calificaciones;
        // Mismo alcance que Calificacion::nota_nivelacion(); el curso ya se filtró por colegio.
        // Las nivelaciones antiguas pueden no tener colegio_id informado.
        $nivelaciones = NotaNivelacion::where('curso_id', $cursoId)
            ->whereIn('periodo_id', $calificaciones->pluck('id_periodo')->unique()->all())
            ->whereIn('estudiante_id', $calificaciones->pluck('id_estudiante')->unique()->all())
            ->whereIn('asignatura_id', $calificaciones->pluck('id_asignatura')->unique()->all())
            ->orderBy('id')->get(['periodo_id', 'estudiante_id', 'asignatura_id', 'calificacion']);
        $indice = [];
        foreach ($nivelaciones as $nota) {
            $e = (int)$nota->estudiante_id; $a = (int)$nota->asignatura_id; $p = (int)$nota->periodo_id;
            if (!isset($indice[$e][$a][$p])) $indice[$e][$a][$p] = $nota;
        }
        foreach ($calificaciones as $nota) {
            $e = (int)$nota->id_estudiante; $a = (int)$nota->id_asignatura; $p = (int)$nota->id_periodo;
            $nota->lbl_nivelacion = '';
            if (isset($indice[$e][$a][$p])) {
                $nota->calificacion = $indice[$e][$a][$p]->calificacion;
                $nota->lbl_nivelacion = 'n';
            }
        }
        return $calificaciones;
    }

    public function indexar($calificaciones)
    {
        $indice = [];
        foreach ($calificaciones as $nota) {
            $e = (int)$nota->id_estudiante; $a = (int)$nota->id_asignatura; $p = (int)$nota->id_periodo;
            // La consulta anterior usaba first(): conservar la primera nota si hay duplicados.
            if (!isset($indice[$e][$a][$p])) $indice[$e][$a][$p] = $nota;
        }
        return $indice;
    }
}
