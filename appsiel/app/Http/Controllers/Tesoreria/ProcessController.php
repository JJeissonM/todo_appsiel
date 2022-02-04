<?php

namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Controller;

use App\Tesoreria\TesoDocEncabezado;
use App\Matriculas\Matricula;
use App\Tesoreria\Services\PaymentBookServices;
use App\Tesoreria\TesoLibretasPago;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class ProcessController extends Controller
{    
    public function generar_libretas_de_estudiantes_matriculados()
    {
        if (Input::get('periodo_lectivo_id') == null) {
            dd('Debe enviar el parametro periodo_lectivo_id');
        }
        $fecha_inicio = '2022-02-01';
        $numero_periodos = 10;
        $valor_matricula = 680000;
        $valor_pension_mensual = 210000;
        $estado = 'Pendiente';

        $matriculas = Matricula::where('periodo_lectivo_id',(int)Input::get('periodo_lectivo_id'))->get();

        $n = 0;
        foreach ($matriculas as $matricula) {

            $ya_tiene =TesoLibretasPago::where('matricula_id', $matricula->id)->get()->first();

            if ($ya_tiene != null) {
                continue;
            }

            $libreta = TesoLibretasPago::create(
                    [
                        'id_estudiante' => $matricula->id_estudiante,
                        'matricula_id' => $matricula->id,
                        'fecha_inicio' => $fecha_inicio,
                        'valor_matricula' => $valor_matricula,
                        'valor_pension_anual' => $valor_pension_mensual * $numero_periodos,
                        'numero_periodos' => $numero_periodos,
                        'valor_pension_mensual' => $valor_pension_mensual,
                        'estado' => $estado,
                        'creado_por' => Auth::user()->email
                        ]
                );
            
            // Crear registros de cartera (Plan de pagos)
            $obj_libreta = new PaymentBookServices();
            $obj_libreta->create_payment_plan( $libreta->id, $matricula->id_estudiante, $valor_matricula, $valor_pension_mensual, $fecha_inicio, $numero_periodos);
            
            $n++;
        }

        echo "Se crearon ". $n ." Libretas con sus planes de pago";
    }
    
    public function crear_libreta_estudiante( $doc_header_id )
    {
        $document_header = TesoDocEncabezado::find( $doc_header_id );
        $document_header->accounting_movement();

        return $document_header;
    }
}