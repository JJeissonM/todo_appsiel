<p style="text-align: center; font-size: 15px; font-weight: bold;">
    
    Promedio proyectado de calificaciones por asignatura <br/> 
    Año Lectivo: {{ $periodo_lectivo->descripcion }} 
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Curso: {{$curso->descripcion}}
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Calificación mínima para ganar: {{$tope_escala_valoracion_minima+0.01}}
     <br>
     <span style="background-color: yellow;"> &#9632; Calificación	faltante.</span> 
     <span style=" font-size: 12px; color: gray;"> El promedio de la asignatura se calcula con los periodos que tienen calificación. No se tiene en cuenta el periodo de promedios (Periodo FINAL). </span>
 </p>
    <hr>

<style>
    .promedio-proyectado-tabla th, .promedio-proyectado-tabla td {
        padding: 2px 1px;
        word-wrap: break-word;
        white-space: normal;
    }
    .promedio-proyectado-tabla thead { display: table-header-group; }
    .promedio-proyectado-tabla tr { page-break-inside: avoid; }
</style>

@php
    $np = count($periodos->toArray());
    $asignaturas_por_parte = 6;
    $partes_asignaturas = array_chunk($asignaturas, $asignaturas_por_parte);
@endphp

@foreach($partes_asignaturas as $numero_parte => $asignaturas_parte)
    @if($numero_parte > 0)
        <div class="page-break"></div>
    @endif
    @php
        $cantidad_asignaturas_mostrar = count($asignaturas_parte);
    @endphp
<table data-excel-export="true" class="table table-bordered table-striped tabla_contenido promedio-proyectado-tabla" style="width:100%; table-layout:fixed; margin-top: -4px; font-size:9px;">
    <colgroup>
        <col style="width:4%;">
        <col style="width:24%;">
        @for($col=0; $col < $cantidad_asignaturas_mostrar * ($np + 1); $col++)
            <col style="width:{{ 72 / ($cantidad_asignaturas_mostrar * ($np + 1)) }}%;">
        @endfor
    </colgroup>
    <thead>
        <tr>
            <th rowspan="2"> No. </th>
            <th rowspan="2"> Estudiante </th>
            @for($i=0;$i<$cantidad_asignaturas_mostrar;$i++)
                <th colspan="{{ $np+1 }}" style="text-align: center;"> {{ $asignaturas_parte[$i]['abreviatura'] }} </th>
            @endfor
        </tr>
        <tr>
            @for($i=0;$i<$cantidad_asignaturas_mostrar;$i++)
                @foreach($periodos as $periodo)
                    <th> P{{$periodo->numero}} </th>
                @endforeach
                <th style="background: #CACACA;"> Prom </th>
            @endfor
        </tr>
    </thead>
    <tbody>
            @php
                $i = 0;
            @endphp
            @foreach($estudiantes as $estudiante)
                
                <tr class="fila-{{$i}}" >
                    <td>
                       {{ $i+1 }}
                    </td>
                    <td>
                       {{$estudiante->nombre_completo}}
                    </td>

                    @php
                    
                        for($j=0; $j < $cantidad_asignaturas_mostrar; $j++)
                        {
                            $array_periodos = get_array_periodos( $calificaciones, $estudiante->id_estudiante, $asignaturas_parte[$j]['id'], $periodos, $tope_escala_valoracion_minima );
                    @endphp        


                            <!-- Dibujar la nota de cada periodo -->
                            @foreach($periodos as $periodo)                    
                                <td>
                                    <span style="background: {{$array_periodos['calificacion'][$periodo->id]['background_color']}}; color: {{$array_periodos['calificacion'][$periodo->id]['text_color']}}" > 
                                        {{ $array_periodos['calificacion'][$periodo->id]['text'] }}
                                        <sup> {{ $array_periodos['calificacion'][$periodo->id]['lbl_nivelacion'] }} </sup>
                                    </span>               
                                </td>
                            @endforeach


                            @php
                                // Calcular y Dibujar nota promedio
                                // El promedio lo calcula con los periodos que tienen nota.
                                $prom = 0;
                                if( $array_periodos['cantidad_periodos_con_calificacion'] != 0)
                                {
                                    $prom = $array_periodos['total_nota'] / $array_periodos['cantidad_periodos_con_calificacion'];
                                }
                                $color = 'black';
                                if ( $prom <= $tope_escala_valoracion_minima )
                                {
                                    $color = 'red';
                                }
                            @endphp
                                <td style="background: #CACACA;">
                                    <span style="color: {{ $color }}"> {{ number_format( $prom, (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.') }} </span>
                                </td>    
                            @php

                        } // end for asignaturas 

                    @endphp
                </tr>
                @php $i++; @endphp
            @endforeach

    </tbody>
</table>
@endforeach

@php 
    function get_array_periodos( $calificaciones, $estudiante_id, $asignatura_id, $periodos, $tope_escala_valoracion_minima)
    {
        $cali_periodos = [];
        $periodos_listos=0;
        $periodos_faltantes=0;
        $np = count( $periodos->toArray() );

        // <!--  1ro. Crear array con las notas de cada periodo -->
        $total_nota = 0;
        foreach($periodos as $periodo)
        {
                
            $cali = $calificaciones->whereLoose( 'id_estudiante', $estudiante_id )
                                    ->whereLoose( 'id_periodo', $periodo->id )
                                    ->whereLoose( 'id_asignatura', $asignatura_id )
                                    ->first();

            if ( !is_null($cali) ) 
            {
                $cali_periodos[$periodo->id]['cali'] = $cali->calificacion;
                $cali_periodos[$periodo->id]['text'] = number_format($cali->calificacion, (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                $cali_periodos[$periodo->id]['text_color'] = 'black';
                $cali_periodos[$periodo->id]['background_color'] = 'transparent';
                $cali_periodos[$periodo->id]['lbl_nivelacion'] = $cali->lbl_nivelacion;

                if ( $cali->calificacion <= $tope_escala_valoracion_minima )
                {
                    $cali_periodos[$periodo->id]['text_color'] = 'red';
                }

                $total_nota += (float)$cali_periodos[$periodo->id]['cali'];
                $periodos_listos++;
            }else{
                $cali_periodos[$periodo->id]['cali'] = 0;
                $cali_periodos[$periodo->id]['text'] = '';
                $cali_periodos[$periodo->id]['text_color'] = '';
                $cali_periodos[$periodo->id]['background_color'] = '';
                $cali_periodos[$periodo->id]['lbl_nivelacion'] = '';
                $periodos_faltantes++;
            }
        }
        
        // 2do. Se calcula el valor de la(s) nota(s) faltante(s)
        // Prevenir división sobre cero
        $cali_faltante = 0;
        if( $periodos_faltantes != 0)
        {
            $cali_faltante = ( $tope_escala_valoracion_minima * $np - array_sum(array_column($cali_periodos, 'cali')) ) / $periodos_faltantes;
        }                    
        
        $cali_faltante_text = number_format($cali_faltante, (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
        $cali_faltante_text_color = 'black';
        if ( $cali_faltante <= $tope_escala_valoracion_minima ) {
            $cali_faltante_text_color = 'red';
        }


        // 3ro. Se asigna el valor de la(s) nota(s) faltante(s) al (a los) periodo(s) sin nota.
        foreach($periodos as $periodo)
        {   
            if ( $cali_periodos[$periodo->id]['cali'] == 'no' )
            {
                $cali_periodos[$periodo->id]['cali'] = $cali_faltante;
                $cali_periodos[$periodo->id]['text'] = $cali_faltante_text;
                $cali_periodos[$periodo->id]['text_color'] = $cali_faltante_text_color;
                $cali_periodos[$periodo->id]['background_color'] = 'yellow';
                $cali_periodos[$periodo->id]['lbl_nivelacion'] = '';
            }
        }

        return [ 'calificacion' => $cali_periodos, 'total_nota' => $total_nota, 'cantidad_periodos_con_calificacion' => $periodos_listos ];
    }
@endphp
