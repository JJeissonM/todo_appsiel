<?php

namespace App\Calificaciones\Services;

use App\Calificaciones\Calificacion;
use App\Calificaciones\CalificacionAuxiliar;
use App\Calificaciones\EncabezadoCalificacion;
use App\Calificaciones\EscalaValoracion;
use App\Calificaciones\NotaNivelacion;

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

    /**
     *    La norama dice por tres o más AREAS perdidas, se deben promediar las asignaturas del AREA.
     */
    public function get_resultado_academico($asignaturas, $periodo_lectivo_id, $periodo_id, $curso_id, $estudiante_id)
    {
        $tope_escala_valoracion_minima = EscalaValoracion::where( 'periodo_lectivo_id', $periodo_lectivo_id )->orderBy('calificacion_minima','ASC')->first()->calificacion_maxima;

        $asignaturas_perdidas = 0;
        foreach($asignaturas as $asignatura)
        {
            $calificacion = Calificacion::get_la_calificacion( $periodo_id, $curso_id, $estudiante_id, $asignatura->id);
            $nota_nivelacion = NotaNivelacion::get_la_calificacion( $periodo_id, $curso_id, $estudiante_id, $asignatura->id);
            
            $valor_calificacion = $calificacion->valor;
            if( $nota_nivelacion->valor != 0 )
            {
                $valor_calificacion = $nota_nivelacion->valor;
            }

            if ( $valor_calificacion <= $tope_escala_valoracion_minima ) {
                $asignaturas_perdidas++;
            }
        }

        if ($asignaturas_perdidas > 2) {
            return 'REPROBÓ';
        }

        return 'APROBÓ';
    }
}

