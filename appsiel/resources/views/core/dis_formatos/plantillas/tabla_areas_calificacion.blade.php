<table class="table table-bordered">
    <thead>
        <tr style="font-size: 15px;">
            <th style="text-align: center;">AREAS</th>
            <th style="text-align: center">CALIFICACION CUANTITATIVA</th>
            <th style="text-align: center">CALIFICACION CUALITATIVA</th>
            @if($mostrar_intensidad_horaria)
                <th style="text-align: center">I.H.</th>    
            @endif  
        </tr>
    </thead>

    <tbody>
        <?php
            $asignaturas_niveladas = [];

            $areas = $asignaturas->groupBy('area_id');
        ?>
        @foreach($areas as $asignaturas)
            <?php
                $intensidad_horaria = 0;
                $valor_calificacion = 0;
                $cantidad = 0;
                $detalle = '(';
                $es_el_primero = true;
                foreach($asignaturas as $asignatura)
                {
                    if($asignatura->id == 146) // Italiano
                    {
                        $valor_calificacion += 5;
                    }else{
                        $calificacion = App\Calificaciones\Calificacion::get_la_calificacion( $periodo_id, $curso->id, $estudiante->id, $asignatura->id);
                        $valor_calificacion += $calificacion->valor;
                    }

                    $intensidad_horaria += $asignatura->intensidad_horaria;
                    
                    $cantidad++;

                    if ($es_el_primero) {
                        $detalle .= $asignatura->descripcion;
                        $es_el_primero = false;
                    }else{
                        $detalle .= ', ' . $asignatura->descripcion;
                    }
                    
                }

                $prom = 0;
                if ($cantidad != 0 ) {
                    $prom = $valor_calificacion / $cantidad;
                }
                
                $obj_escala_nacional = App\Calificaciones\EscalaValoracion::get_escala_segun_calificacion($prom, $periodo->periodo_lectivo_id);
                
                if ($obj_escala_nacional == null) {
                    dd($estudiante->nombre_completo, $asignatura->descripcion, $prom);
                }

                $escala_nacional = $obj_escala_nacional->escala_nacional;

                $detalle .= ')';
                
            ?>
            <tr style="font-size: 14px;">
                <td style="width:400px;">
                    {{ $asignaturas->first()->area }}
                    @if($cantidad > 0)
                        {!! $detalle !!}
                    @endif
                </td>
                <td align="center">
                    {{ number_format( $prom, 1, ',', '.' ) }}
                </td>
                <td align="center">
                    {{ $escala_nacional }}
                </td>
                
                @if($mostrar_intensidad_horaria)
                    <td align="center">
                        {{ $intensidad_horaria }}
                    </td>  
                @endif
            </tr>
        @endforeach
    </tbody>
</table>
        