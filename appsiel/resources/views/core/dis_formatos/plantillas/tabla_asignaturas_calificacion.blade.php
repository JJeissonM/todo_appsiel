<table class="contenido" style="border: 1px solid grey;
border-collapse: collapse;" align="center">
    <thead>
        <tr>
            <th style="border: 1px solid grey;background-color: #E0E0E0; width:400px;">Asignaturas</th>
            <th style="border: 1px solid grey;background-color: #E0E0E0; width:30px;">I.H.</th>
            <th colspan="3" style="border: 1px solid grey;background-color: #E0E0E0;">Calificaciones</th>
        </tr>
    </thead>
    <tbody>
    @foreach($asignaturas as $asignatura)

        <?php

            /*$calificacion = App\Calificaciones\Calificacion::where(['id_colegio'=>$colegio->id,'anio'=>$anio,
                'id_periodo'=>$periodo->id,'curso_id'=>$curso_id,
                'id_estudiante'=>$estudiante->id,'id_asignatura'=>$asignatura->id])
                ->get()->first();*/

            $calificacion = App\Calificaciones\Calificacion::get_promedio_periodos($periodos_promediar, $curso_id, $estudiante->id, $asignatura->id);

            //dd( $calificacion );

            $desempeno = (object)['sigla'=>'','escala_nacional'=> 'Sin Calificación' ];
            
            if( !is_null($calificacion) )
            {
                if ( $calificacion->valor > 0 ) {
                    $desempeno = App\Calificaciones\EscalaValoracion::where('calificacion_minima','<=',$calificacion->valor)->where('calificacion_maxima','>=',$calificacion->valor)->get()->first();
                }                    
            }else{
                $calificacion = (object)['valor'=>0 ];
            }
        ?>

        <tr style="font-size: 15px;">
            <td style="border: 1px solid grey; width:400px;">
                {{ $asignatura->descripcion }}
            </td>
            <td align="center" style="border: 1px solid grey; width:30px;">
                {{ $asignatura->intensidad_horaria }}
            </td>
            <td align="center" style="border: 1px solid grey; width:40px;">
                {{ $calificacion->valor }}
            </td>
            <td align="center" style="border: 1px solid grey; width:30px;">
                {{ $desempeno->sigla }}
            </td>
            <td align="center" style="border: 1px solid grey;">
                {{ $desempeno->escala_nacional }}
            </td>
        </tr>
    @endforeach       
    </tbody>
</table>