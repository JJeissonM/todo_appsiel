<?php

namespace App\Matriculas\Services;

class InclusionEstudianteService
{
    public function esDeInclusion($registro)
    {
        if (is_null($registro)) {
            return false;
        }

        return in_array($registro->es_de_inclusion, [1, '1', true, 'true', 'Si', 'Sí', 'si', 'sí', 'on'], true);
    }

    public function etiqueta($registro)
    {
        return $this->esDeInclusion($registro) ? 'Si' : 'No';
    }
}
