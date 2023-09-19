<?php

namespace App\Calificaciones\Services;

use App\Calificaciones\CalificacionAuxiliar;
use App\Calificaciones\EncabezadoCalificacion;

class CalificacionesService
{
    public function get_object_calificaciones_auxiliares($periodo_id,$curso_id)
    {
        $lbl_calificaciones_aux = [];
        
        $calificaciones_aux_periodo = CalificacionAuxiliar::where([
            ['id_periodo','=',$periodo_id],
            ['curso_id', '=', $curso_id]
        ])->get();

        $columna_calificacion = 1;
        for ($columna_calificacion=1; $columna_calificacion < 16; $columna_calificacion++) { 
            $suma_calificaciones_columna = $calificaciones_aux_periodo->sum('C'.$columna_calificacion);
            
            if($suma_calificaciones_columna > 0)
            {
                $lbl_peso = '';
                $encabezado_calificacion_aux = EncabezadoCalificacion::where([
                    ['periodo_id','=',$periodo_id],
                    ['curso_id', '=', $curso_id],
                    ['columna_calificacion', '=', 'C'.$columna_calificacion]
                ])->get()->first();
                if ($encabezado_calificacion_aux != null) {
                    $lbl_peso = $encabezado_calificacion_aux->peso . '%';
                }

                $lbl_calificaciones_aux[] = (object)[
                    'label' => 'C'.$columna_calificacion,
                    'peso' => $lbl_peso
                ];
            }
        }

        return $lbl_calificaciones_aux;
    }
}

