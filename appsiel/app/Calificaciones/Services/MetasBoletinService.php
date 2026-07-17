<?php

namespace App\Calificaciones\Services;

class MetasBoletinService
{
    public function filtrarPorTipoEstudiante($metas, $estudiante)
    {
        $esDeInclusion = $this->esEstudianteDeInclusion($estudiante);

        return $metas->filter(function ($meta) use ($esDeInclusion) {
            return $this->toBool($meta->es_para_inclusion) === $esDeInclusion;
        })->values();
    }

    public function esEstudianteDeInclusion($estudiante)
    {
        if (is_null($estudiante)) {
            return false;
        }

        return $this->toBool($estudiante->es_de_inclusion);
    }

    private function toBool($value)
    {
        return in_array($value, [1, '1', true, 'true', 'Si', 'Sí', 'si', 'sí', 'on'], true);
    }
}
