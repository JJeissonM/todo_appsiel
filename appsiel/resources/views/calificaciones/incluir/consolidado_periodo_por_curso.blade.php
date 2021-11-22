<p style="text-align: center; font-size: 15px; font-weight: bold;">
    
    Consolidado de calificaciones por Estudiante <br/> 
    Año: {{explode("-",$periodo->fecha_desde)[0]}} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  Periodo: {{$periodo->descripcion}}
     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Curso: {{$curso->descripcion}}
 </p>
    <hr>

 <table class="table table-bordered table-striped tabla_contenido" style="margin-top: -4px; font-size:11px;">
    <thead>
        <tr>
            <th rowspan="2" style="border: 1px solid; text-align:center;"> No. </th>
            <th rowspan="2" style="border: 1px solid; text-align:center;"> Estudiante </th>
            {!! $celdas_encabezado_areas !!}
            <th rowspan="2" style="border: 1px solid; text-align:center;">Prom.</th>
            <th rowspan="2" style="border: 1px solid; text-align:center;">Puesto</th>
        </tr>
        <tr>
            {!! $celdas_encabezado_asignaturas !!}
        </tr>
    </thead>
    <tbody>

            @php
                $cantidad_asignaturas = count($vec_asignaturas);
                
                $i = 0;                
            @endphp
            @foreach($estudiantes as $estudiante)
                
                <tr class="fila-{{$i}}" >
                    <td>
                       {{ $i+1 }}
                    </td>
                    <td>
                       {{ $estudiante->nombre_completo }}
                    </td>

                    @php $n = 0; $cali_final = 0; @endphp
                    
                    @for($j=0; $j < $cantidad_asignaturas; $j++)
                        <td>
                            @php
                                
                                $calificacion = $calificaciones->whereLoose('id_estudiante',$estudiante->id_estudiante)->whereLoose('id_asignatura', $vec_asignaturas[$j]['id'])->first();//->get('calificacion');//


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

                                $text_cali = '';
                                $color_text = 'black';
                                if ( $cali != 0 ) 
                                {
                                    $cali_final += $cali;
                                    $text_cali = number_format($cali, config('calificaciones.cantidad_decimales_mostrar_calificaciones'), ',', '.');
                                    $n++;

                                    if ( $cali <= $tope_escala_valoracion_minima ) {
                                        $color_text = 'red';
                                    }
                                }/**/
                                
                           @endphp
                           <span style="color: {{$color_text}}"> {{ $text_cali }} <sup>{{ $lbl_nivelacion }}</sup> </span>
                        </td>
                    @endfor

                    <td>
                        @if($n!=0)

                            @php
                                $color_text = 'black';

                                if ( $cali_final/$n <= $tope_escala_valoracion_minima ) {
                                    $color_text = 'red';
                                }
                            @endphp

                            <span style="color: {{$color_text}}"> {{ number_format( $cali_final/$n , 2, ',', '.') }}</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $puesto = $observaciones->where('id_estudiante',$estudiante->id_estudiante)->first();
                            $text_puesto = '';
                            if ( !is_null($puesto) ) 
                            {
                                $text_puesto = $puesto->puesto;
                            }
                            echo $text_puesto;
                        @endphp
                    </td>
                </tr>
                @php $i++; @endphp
            @endforeach
        </tbody>
    </table>