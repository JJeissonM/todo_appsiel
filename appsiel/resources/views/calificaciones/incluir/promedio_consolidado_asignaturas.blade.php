<p style="text-align: center; font-size: 15px; font-weight: bold;">
    
    Promedio consolidado de calificaciones por asignatura <span style=" font-size: 12px; color: gray;"> (todos los periodos) </span>
    <br/>  
    Año Lectivo: {{ $periodo_lectivo->descripcion }} 
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Curso: {{$curso->descripcion}}
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Calificación mínima para ganar: {{$tope_escala_valoracion_minima+0.01}}
 </p>
    <hr>

@php
    $cantidad_asignaturas_mostrar = 9;
    $cantidad_asignaturas = count($asignaturas);
    $mostrar_segunda_parte = true;

    if ( $cantidad_asignaturas_mostrar > $cantidad_asignaturas )
    {
        $cantidad_asignaturas_mostrar = $cantidad_asignaturas;
        $mostrar_segunda_parte = false;
    } 
    
    $np = count($periodos->toArray()); // Cantidad de periodos    
@endphp
<table class="table table-bordered table-striped tabla_contenido" style="margin-top: -4px; font-size:11px;">
    <thead>
        <tr>
            <th rowspan="2"> No. </th>
            <th rowspan="2"> Estudiante </th>
            @for($i=0;$i<$cantidad_asignaturas_mostrar;$i++)
                <th style="text-align: center;"> {{$asignaturas[$i]['abreviatura']}} </th>
            @endfor
        </tr>
        <tr>
            @for($i=0;$i<$cantidad_asignaturas_mostrar;$i++)
                <th> Prom </th>
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
                        $n = 0; $cali_final = 0;
                    
                        for($j=0; $j < $cantidad_asignaturas_mostrar; $j++)
                        {
                            $cali_periodos_listos = []; // periodos que ya tienen calificación
                            $periodos_listos=0;
                            $periodos_faltantes=0;

                            // <!-- Crear array con las notas de cada periodo -->
                            foreach($periodos as $periodo)
                            {
                                    
                                $cali = $calificaciones->whereLoose('id_estudiante',$estudiante->id_estudiante)->whereLoose('id_periodo',$periodo->id)->whereLoose('id_asignatura', $asignaturas[$j]['id'])->first();

                                if ( !is_null($cali) ) 
                                {
                                    $cali_periodos_listos[$periodos_listos]['cali'] = $cali->calificacion;
                                    $cali_periodos_listos[$periodos_listos]['text'] = number_format($cali->calificacion, (int)config('califiaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                                    $cali_periodos_listos[$periodos_listos]['text_color'] = 'black';

                                    if ( $cali->calificacion <= $tope_escala_valoracion_minima ) {
                                        $cali_periodos_listos[$periodos_listos]['text_color'] = 'red';
                                    }
                                    $periodos_listos++;
                                }else{
                                    $periodos_faltantes++;
                                }
                            }
                            
                            // Prevenir división sobre cero
                            $cali_faltante = 0;
                            if( $periodos_faltantes != 0)
                            {
                                $cali_faltante = ( $tope_escala_valoracion_minima * $np - array_sum(array_column($cali_periodos_listos, 'cali')) ) / $periodos_faltantes;
                            }                        
                            
                            $cali_faltante_text =number_format($cali_faltante, (int)config('califiaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                            $cali_faltante_text_color = 'black';
                            if ( $cali_faltante <= $tope_escala_valoracion_minima ) {
                                $cali_faltante_text_color = 'red';
                            }

                            $npl=0;


                        // <!-- Dibujar la nota de cada periodo -->

                            $total_nota = 0;
                            $num = 0;
                        foreach($periodos as $periodo)
                        {
                            if ( $npl < $periodos_listos )
                            {
                                $total_nota += (float)$cali_periodos_listos[$npl]['cali'];
                                $num++;
                            }
                            $npl++;
                        } // End foreach periodos
                        $prom = 0;
                        if( $num != 0)
                        {
                            $prom = $total_nota / $num;
                        }
                        $color = 'black';
                        if ( $prom <= $tope_escala_valoracion_minima )
                        {
                            $color = 'red';
                        }
                    @endphp
                        <td>
                            <span style="color: {{ $color }}"> {{ number_format( $prom, (int)config('califiaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.') }} </span>
                        </td>    
                    @php

                    } // end for asignaturas 

                    @endphp
                </tr>
                @php $i++; @endphp
            @endforeach

    </tbody>
</table>

@if($mostrar_segunda_parte)
    <div class="page-break"></div>


    <table class="table table-bordered table-striped tabla_contenido" style="margin-top: -4px; font-size:11px;">
        <thead>
            <tr>
                <th rowspan="2"> No. </th>
                <th rowspan="2"> Estudiante </th>
                @for($j=$cantidad_asignaturas_mostrar;$j<$cantidad_asignaturas;$j++)
                    <th style="text-align: center;"> {{$asignaturas[$j]['abreviatura']}} </th>
                @endfor
            </tr>
            <tr>
                @for($j=$cantidad_asignaturas_mostrar;$j<$cantidad_asignaturas;$j++)
                    <th> Prom </th>
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
                        $n = 0; $cali_final = 0;
                    
                        for($k=$cantidad_asignaturas_mostrar; $k < $cantidad_asignaturas; $k++)
                        {
                            $cali_periodos_listos = []; // periodos que ya tienen calificación
                            $periodos_listos=0;
                            $periodos_faltantes=0;

                            // <!-- Crear array con las notas de cada periodo -->
                            foreach($periodos as $periodo)
                            {
                                    
                                $cali = $calificaciones->whereLoose('id_estudiante',$estudiante->id_estudiante)->whereLoose('id_periodo',$periodo->id)->whereLoose('id_asignatura', $asignaturas[$k]['id'])->first();

                                if ( !is_null($cali) ) 
                                {
                                    $cali_periodos_listos[$periodos_listos]['cali'] = $cali->calificacion;
                                    $cali_periodos_listos[$periodos_listos]['text'] = number_format($cali->calificacion, (int)config('califiaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                                    $cali_periodos_listos[$periodos_listos]['text_color'] = 'black';

                                    if ( $cali->calificacion <= $tope_escala_valoracion_minima ) {
                                        $cali_periodos_listos[$periodos_listos]['text_color'] = 'red';
                                    }
                                    $periodos_listos++;
                                }else{
                                    $periodos_faltantes++;
                                }
                            }

                            // Prevenir división sobre cero
                            $cali_faltante = 0;
                            if( $periodos_faltantes != 0)
                            {
                                $cali_faltante = ( $tope_escala_valoracion_minima * $np - array_sum(array_column($cali_periodos_listos, 'cali')) ) / $periodos_faltantes;
                            }
                                
                            $cali_faltante_text =number_format($cali_faltante, (int)config('califiaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                            $cali_faltante_text_color = 'black';
                            if ( $cali_faltante <= $tope_escala_valoracion_minima ) {
                                $cali_faltante_text_color = 'red';
                            }

                            $npl=0; // Se mostrarán los periodos listos en orden, pero si hay un periodo "listo" que no tenga nota y después venga un periodo listo que si tenga nota, el array solo tendrá un elemento, pero deben ser dos elementos. Así, un periodo que ya está "listo", pero con nota en cero o inexistente (por algún motivo) le mostrará una nota proyectada


                        // <!-- Dibujar la nota de cada periodo -->

                                $total_nota = 0;
                                $num = 0;
                            foreach($periodos as $periodo)
                            {
                                if ( $npl < $periodos_listos )
                                {
                                    $total_nota += (float)$cali_periodos_listos[$npl]['cali'];
                                    $num++;
                                }
                                $npl++;
                            } // End foreach periodos
                            $prom = 0;
                            if( $num != 0)
                            {
                                $prom = $total_nota / $num;
                            }
                            $color = 'black';
                            if ( $prom <= $tope_escala_valoracion_minima )
                            {
                                $color = 'red';
                            }
                            @endphp
                                <td>
                                    <span style="color: {{ $color }}"> {{ number_format( $prom, (int)config('califiaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.') }} </span>
                                </td>    
                            @php

                        } // end for asignaturas 

                        @endphp
                    </tr>
                @php $i++; @endphp
            @endforeach
        </tbody>
    </table>
@endif