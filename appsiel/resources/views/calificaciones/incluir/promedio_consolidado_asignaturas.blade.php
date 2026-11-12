<p style="text-align: center; font-size: 15px; font-weight: bold;">
    
    Promedio consolidado de calificaciones por asignatura <span style=" font-size: 12px; color: gray;"> (todos los periodos) </span>
    <br/>  
    Año Lectivo: {{ $periodo_lectivo->descripcion }} 
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Curso: {{$curso->descripcion}}
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Calificación mínima para ganar: {{$tope_escala_valoracion_minima+0.01}}
 </p>
    <hr>

<style>
    .promedio-consolidado-tabla th, .promedio-consolidado-tabla td {
        padding: 2px 1px;
        word-wrap: break-word;
        white-space: normal;
    }
    .promedio-consolidado-tabla thead { display: table-header-group; }
    .promedio-consolidado-tabla tr { page-break-inside: avoid; }
</style>

@php
    $np = count($periodos->toArray());
    $partes_asignaturas = array_chunk($asignaturas, 8);
@endphp

@foreach($partes_asignaturas as $numero_parte => $asignaturas_parte)
    @if($numero_parte > 0)
        <div class="page-break"></div>
    @endif
    @php
        $cantidad_asignaturas_mostrar = count($asignaturas_parte);
    @endphp
<table data-excel-export="true" class="table table-bordered table-striped tabla_contenido promedio-consolidado-tabla" style="width:100%; table-layout:fixed; margin-top: -4px; font-size:11px;">
    <colgroup>
        <col style="width:4%;">
        <col style="width:24%;">
        @for($col=0; $col < $cantidad_asignaturas_mostrar; $col++)
            <col style="width:{{ 72 / $cantidad_asignaturas_mostrar }}%;">
        @endfor
    </colgroup>
    <thead>
        <tr>
            <th rowspan="2"> No. </th>
            <th rowspan="2"> Estudiante </th>
            @for($i=0;$i<$cantidad_asignaturas_mostrar;$i++)
                <th style="text-align: center;"> {{$asignaturas_parte[$i]['abreviatura']}} </th>
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
                                    
                                $cali = $calificaciones->whereLoose('id_estudiante',$estudiante->id_estudiante)->whereLoose('id_periodo',$periodo->id)->whereLoose('id_asignatura', $asignaturas_parte[$j]['id'])->first();

                                if ( !is_null($cali) ) 
                                {
                                    $cali_periodos_listos[$periodos_listos]['cali'] = $cali->calificacion;
                                    $cali_periodos_listos[$periodos_listos]['text'] = number_format($cali->calificacion, (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
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
                            
                            $cali_faltante_text =number_format($cali_faltante, (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
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
