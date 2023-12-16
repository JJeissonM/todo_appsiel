<table class="table table-bordered">
    <thead>
        <tr style="font-size: 15px;">
            <th style="text-align: center;">Asignatura</th>
            @if($mostrar_intensidad_horaria)
                <th style="text-align: center">I.H.</th>    
            @endif            
            <th style="text-align: center">Porcentaje</th>
            <th style="text-align: center">Nota final</th>
            <th style="text-align: center">Desempeño</th>
        </tr>
    </thead>

    <tbody>
        <?php
            $asignaturas_niveladas = [];
        ?>
        @foreach($asignaturas as $asignatura)
            <?php
                $calificacion = App\Calificaciones\Calificacion::get_la_calificacion( $periodo_id, $curso->id, $estudiante->id, $asignatura->id);
                $nota_nivelacion = App\Calificaciones\NotaNivelacion::get_la_calificacion( $periodo_id, $curso->id, $estudiante->id, $asignatura->id);
                
                $valor_calificacion = $calificacion->valor;
                $escala_nacional = $calificacion->escala_nacional;
                if( $nota_nivelacion->valor != 0 )
                {
                    //$valor_calificacion = $nota_nivelacion->valor;
                    //$escala_nacional = $nota_nivelacion->escala_nacional;

                     $asignaturas_niveladas[] = (object)[ 'asignatura' => $asignatura->descripcion, 'calificacion_real' => $nota_nivelacion->valor, 'escala_nacional' => $nota_nivelacion->escala_nacional ];
                }
            ?>
            <tr style="font-size: 14px;">
                <td style="width:400px;">
                    {{ $asignatura->descripcion }}
                </td>
                
                @if($mostrar_intensidad_horaria)
                    <td align="center">
                        {{ $asignatura->intensidad_horaria }}
                    </td>  
                @endif
                                
                <td align="center">
                    {{ number_format( $valor_calificacion / $maxima_escala_valoracion * 100, 0, ',', '.' ) }}%
                </td>
                <td align="center">
                    {{ number_format( $valor_calificacion, config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.' ) }}
                </td>
                <td align="center">
                    {{ $escala_nacional }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<?php
    if(!empty( $asignaturas_niveladas ) )
    {
        echo '<div style="width: 100%; border: 1px solid #ddd; font-size:12px;padding:5px;"><b>ASIGNATURAS NIVELADAS</b><br>';
        $primer_dato = true;
        foreach ($asignaturas_niveladas as $linea)
        {
            if ( $primer_dato )
            {
                echo $linea->asignatura . ': ' . $linea->calificacion_real;
                $primer_dato = false;
            }else{
                echo ', ' . $linea->asignatura . ': ' . $linea->calificacion_real;
            }
        }
        echo '</div>';
    }
?>

@include('core.dis_formatos.plantillas.cetificados_notas_escala_valoracion')
        