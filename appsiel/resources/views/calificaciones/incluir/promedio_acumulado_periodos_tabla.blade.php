
    <p style="width: 100%; text-align: center; font-size: 20px; font-weight: bold;"> Promedio acumulado por periodos </p>
    <p style="width: 100%; text-align: center; font-size: 16px; font-weight: bold;"> {{ $periodo_lectivo->descripcion }}, Curso {{ $curso->descripcion }}</p>
    <hr>    

    <table id="myTable" class="table table-striped tabla_contenido" style="margin-top: -4px;">
        <thead>
            <tr>
                <th> No. </th>
                <th> Estudiante </th>
                @foreach($periodos as $periodo)
                    <th>
                        <div class="checkbox">
                          <label><input type="checkbox" checked="checked" id="periodo_{{$periodo->id}}">{{$periodo->descripcion}}</label>
                        </div>
                    </th>
                @endforeach
                <th> Promedio acumulado </th>
            </tr>
        </thead>
        <tbody> 
            @php $i=0; @endphp
            @foreach($estudiantes as $estudiante)
                
                @php 
                    $prom_final=0;
                    $n = 0;
                @endphp
                
                <tr class="fila-{{$i}}" >
                    <td>
                       {{ $i+1 }}
                    </td>
                    <td>
                       {{$estudiante->nombre_completo}}
                    </td>
                    @foreach($periodos as $periodo)
                        <td>
                            @php 
                                // Calcular calificacion promedio del estudiante en la Collection calificaciones (todas las asignaturas)
                                $prom = $calificaciones->whereLoose('id_estudiante',$estudiante->id_estudiante)->whereLoose('id_periodo',$periodo->id)->avg('calificacion');//->all();//
                                $text_prom = '';
                                $color_text = 'black';
                                /**/if ( !is_null($prom) ) 
                                {
                                    $prom_final += $prom;
                                    $text_prom = number_format($prom, 2, ',', '.');
                                    $n++;

                                    if ( $prom <= $tope_escala_valoracion_minima ) {
                                        $color_text = 'red';
                                    }
                                }                               
                            @endphp
                            <span style="color: {{$color_text}}"> {{ $text_prom }}</span>
                        </td>
                    @endforeach
                    <td>
                        @if($n!=0)

                            @php
                                $color_text = 'black';

                                if ( $prom_final/$n <= $tope_escala_valoracion_minima ) {
                                    $color_text = 'red';
                                }
                            @endphp

                            <span style="color: {{$color_text}}"> {{ number_format( $prom_final/$n , 2, ',', '.') }}</span>
                        @endif
                    </td>
                </tr>
                @php $i++; @endphp
            @endforeach
        </tbody>
    </table>