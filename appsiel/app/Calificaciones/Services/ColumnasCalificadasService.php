<?php
namespace App\Calificaciones\Services;

use App\Calificaciones\CalificacionAuxiliar;

class ColumnasCalificadasService
{
    public function consulta($anio, $periodo, $curso, $asignatura, $colegio = null)
    {
        $q = CalificacionAuxiliar::where('anio', $anio)->where('id_periodo', $periodo)
            ->where('curso_id', $curso)->where('id_asignatura', $asignatura);
        if ($colegio !== null) $q->where('id_colegio', $colegio);
        return $q;
    }

    public function activas($anio, $periodo, $curso, $asignatura, $pendiente = [])
    {
        $datos = is_array($pendiente) ? $pendiente : $pendiente->getAttributes();
        $q = $this->consulta($anio, $periodo, $curso, $asignatura, isset($datos['id_colegio']) ? $datos['id_colegio'] : null);
        // La fila pendiente reemplaza su versión anterior, incluso al borrar la última nota.
        if (!empty($datos['id'])) $q->where('id', '<>', $datos['id']);
        elseif (!empty($datos['id_estudiante'])) $q->where('id_estudiante', '<>', $datos['id_estudiante']);
        return $this->leerActivas($q, $datos);
    }

    public function activasFueraDeEstudiantes($anio, $periodo, $curso, $asignatura, array $estudiantes, $colegio)
    {
        $q = $this->consulta($anio, $periodo, $curso, $asignatura, $colegio)->whereNotIn('id_estudiante', $estudiantes);
        return $this->leerActivas($q, []);
    }

    private function leerActivas($q, array $datos)
    {
        $select = [];
        for ($i = 1; $i <= 15; $i++) $select[] = 'MAX(CASE WHEN COALESCE(C'.$i.',0) <> 0 THEN 1 ELSE 0 END) AS C'.$i;
        $existen = $q->selectRaw(implode(',', $select))->first();
        $activas = [];
        for ($i = 1; $i <= 15; $i++) {
            if ((int)$existen->{'C'.$i} || (isset($datos['C'.$i]) && is_numeric($datos['C'.$i]) && (float)$datos['C'.$i] != 0)) $activas[] = $i;
        }
        return $activas;
    }

    public function pesos(array $resumen, array $activas)
    {
        $sumas = []; $porcentajes = [];
        foreach ($activas as $i) {
            $c = $resumen['columnas'][$i];
            if (!$c->configurado) continue;
            $id = $c->agrupacion_id;
            $sumas[$id] = (isset($sumas[$id]) ? $sumas[$id] : 0) + $c->peso;
            $porcentajes[$id] = $c->peso_grupo;
        }
        $total = array_sum($porcentajes);
        $pesos = [];
        foreach ($activas as $i) {
            $c = $resumen['columnas'][$i];
            if ($c->configurado && $total > 0) $pesos[$i] = ($porcentajes[$c->agrupacion_id] * $c->peso / $sumas[$c->agrupacion_id]) / $total;
        }
        return $pesos;
    }
}
