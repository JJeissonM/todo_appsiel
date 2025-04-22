
@foreach( $lineas_cuerpo_boletin as $linea )
    <?php 
        if ($linea->asignatura_id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
            continue;
        }
        
        $is_the_first = true;
        
        $asignatura = $linea->asignacion_asignatura->asignatura;

        $logros_asignatura = $logros->where( 'asignatura_id', $asignatura->id )->all();

        $cant_logros = count( $logros_asignatura );

        $style_asignatura = 'style="width:{{$ancho_columna_asignatura}}px; font-size:12px; vertical-align: middle; padding-left: 15px;"';
    ?>

    @foreach ( $logros_asignatura as $logro )
        <tr>
            @if($is_the_first)
                <td rowspan="{{ $cant_logros }}" {{ $style_asignatura }} >
                    &nbsp;&nbsp;<b> {{ $asignatura->descripcion }}</b>
                </td>
                <?php
                    $is_the_first = false;
                ?>						
            @endif
            <td style="text-align: left; padding: 4px;"> 
                {{ $logro->descripcion }} 
            </td>

            <?php
                $valor_desempenio = $todas_las_calificaciones->where('matricula_id', $registro->matricula->id )->where('logro_id', $logro->id)->first();
            ?>

            @if( $valor_desempenio == null )                
                <td {!! $estilo_advertencia !!}>--</td>
            @else
                <td style="width:80px; vertical-align: middle; text-align:center; padding: 5px;"> {{ $valor_desempenio->escala_valoracion->nombre_escala }} </td>
            @endif

        </tr>
    @endforeach

    @if($cant_logros == 0)
        <tr>
            <td style="font-size:12px; vertical-align: middle;" width="250px" >
                <b> {{ $asignatura->descripcion }}</b>
            </td>
            <td colspan="2" {!! $estilo_advertencia !!}>
                No hay logros registrados en este periodo.
            </td>
        </tr>
    @endif
@endforeach {{--  End For Each Asignatura --}}