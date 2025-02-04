<?php 

namespace App\Matriculas\Services;

use App\Tesoreria\Services\PaymentBookServices;
use App\Tesoreria\TesoLibretasPago;
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

    public function store_plan_pagos($data)
    {
        // Crear la libreta
        $registro = TesoLibretasPago::create($data);

        /*      SE CREAN LOS REGISTROS DE CARTERA DE ESTUDIANTES (Plan de Pagos)    */
        $obj_libreta = new PaymentBookServices();
        $obj_libreta->create_payment_plan( $registro->id, $data['id_estudiante'], $data['valor_matricula'], $data['valor_pension_mensual'], $data['fecha_inicio'], $data['numero_periodos']);
    }
}