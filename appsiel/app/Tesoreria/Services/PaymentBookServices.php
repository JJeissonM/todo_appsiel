<?php 

namespace App\Tesoreria\Services;

use App\Tesoreria\TesoPlanPagosEstudiante;

class PaymentBookServices
{

    public function create_payment_plan($payment_book_id, $student_id, $tuition_amount, $pension_amount, $initial_date, $periods_number)
    {
        $data = array_combine( (new TesoPlanPagosEstudiante())->getFillable(), ['','','','','','','',''] );

        // Datos comunes
        $data['id_libreta'] = $payment_book_id;
        $data['id_estudiante'] = $student_id;
        $data['estado'] = "Pendiente";

        // 1. Se agrega el registro de matrícula por pagar en la cartera de estudiantes
        // Datos del concepto de Matrícula
        $data['inv_producto_id'] = (int)config('matriculas.inv_producto_id_default_matricula');
        $data['valor_cartera'] = $tuition_amount;
        $data['saldo_pendiente'] = $tuition_amount;
        $data['fecha_vencimiento'] = $initial_date;

        $this->almacenar_linea_registro_cartera( $data );

        // 2. Se agregan los registros de pensiones por pagar
        // Datos del concepto de Pensión (por cada mes)
        $this->almacenar_registros_pension( $data, $initial_date, $periods_number, $pension_amount );
    }

    public function almacenar_linea_registro_cartera( array $data )
    {
        $cartera = new TesoPlanPagosEstudiante;
        $cartera->fill( $data );
        $cartera->save();
    }

    public function almacenar_registros_pension( $data, $initial_date, $periods_number, $pension_amount )
    {
        // Datos del concepto de Pensión (por cada mes)
        $fecha = explode( "-", $initial_date);
        $num_mes = $fecha[1];
        $num_anio = $fecha[0];
        for($i=0;$i<$periods_number;$i++)
        {
            $data['inv_producto_id'] = config('matriculas.inv_producto_id_default_pension');
            $data['valor_cartera'] = $pension_amount;
            $data['saldo_pendiente'] = $pension_amount;
            $data['fecha_vencimiento'] = $num_anio . '-' . $num_mes . '-' . $fecha[2];

            $this->almacenar_linea_registro_cartera( $data );

            $num_mes++;
            if ($num_mes>12)
            {
                $num_mes = 1;
                $num_anio++;
            }
        }
    }
}