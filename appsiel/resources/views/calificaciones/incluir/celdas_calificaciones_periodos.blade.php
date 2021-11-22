@php 
    $prom_final=0;
    $n = 0;
@endphp
                
@foreach($periodos as $periodo)
    <td width="45px" align="center">
        @php
            $calificacion = $calificaciones->whereLoose('id_estudiante',$estudiante->id_estudiante)
                        ->whereLoose('id_periodo',$periodo->id)
                        ->first();
            $cali = 0;
            $lbl_nivelacion = '';
            if( !is_null( $calificacion ) )
            {
                $cali = $calificacion->calificacion;
                if( !is_null( $calificacion->nota_nivelacion() ) )
                {
                    $cali = $calificacion->nota_nivelacion()->calificacion;
                    $lbl_nivelacion = 'n';
                }
            }

            $text_prom = '';
            $color_text = 'black';
            if ( $cali != 0 ) 
            {
                $prom_final += $cali;
                $text_prom = number_format($cali, config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                $n++;

                if ( $cali <= $tope_escala_valoracion_minima ) {
                    $color_text = 'red';
                }
            }
        @endphp
        <span style="color: {{$color_text}};font-size: 12px; padding: 1px;"> {{ $text_prom }} <sup>{{ $lbl_nivelacion }}</sup> </span>
    </td>
@endforeach

<?php 
    if ( $n == 0 )
    {
        $n = 1;
    }
?>
<td width="45px" align="center">
    {{number_format($prom_final/$n, config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.')}}
</td>