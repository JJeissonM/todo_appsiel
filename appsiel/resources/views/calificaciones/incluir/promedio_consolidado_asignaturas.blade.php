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
    $cantidad_decimales = (int)config('calificaciones.cantidad_decimales_mostrar_calificaciones');
    $indice_calificaciones = app(\App\Calificaciones\Services\PromediosAsignaturasService::class)->indexar($calificaciones);
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
                        for($j=0; $j < $cantidad_asignaturas_mostrar; $j++)
                        {
                            $total_nota = 0;
                            $num = 0;
                            foreach($periodos as $periodo)
                            {
                                $cali = isset($indice_calificaciones[(int)$estudiante->id_estudiante][(int)$asignaturas_parte[$j]['id']][(int)$periodo->id])
                                    ? $indice_calificaciones[(int)$estudiante->id_estudiante][(int)$asignaturas_parte[$j]['id']][(int)$periodo->id] : null;
                                if ($cali !== null) {
                                    $total_nota += (float)$cali->calificacion;
                                    $num++;
                                }
                            }
                            $prom = $num ? $total_nota / $num : 0;
                            $color = $prom <= $tope_escala_valoracion_minima ? 'red' : 'black';
                    @endphp
                        <td>
                            <span style="color: {{ $color }}"> {{ number_format( $prom, $cantidad_decimales, ',', '.') }} </span>
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
