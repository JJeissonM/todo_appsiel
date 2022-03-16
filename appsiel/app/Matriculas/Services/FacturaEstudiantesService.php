<?php 

namespace App\Matriculas\Services;

use App\Tesoreria\TesoPlanPagosEstudiante;

class FacturaEstudiantesService
{
    public function get_rows_planes_pagos($fecha_desde,$fecha_hasta)
    {
        $this->update_cartera();

        return TesoPlanPagosEstudiante::whereBetween('fecha_vencimiento',[$fecha_desde,$fecha_hasta])->get();

    }

    public function update_cartera()
    {
        TesoPlanPagosEstudiante::where('fecha_vencimiento','<', date('Y-m-d'))
                                  ->where('estado','<>', 'Pagada')
                                  ->update(['estado' => 'Vencida']);
    }
}