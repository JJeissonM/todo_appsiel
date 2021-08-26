
        <tr>
            <td  colspan="2" style="border: 1px solid grey;background-color: #E0E0E0; width:400px;">Asignaturas</td>
            <td style="border: 1px solid grey;background-color: #E0E0E0; width:30px; text-align: center">I.H.</td>
            <td colspan="3" style="border: 1px solid grey;background-color: #E0E0E0; text-align: center">Calificaciones</td>
        </tr>
    @foreach($asignaturas as $asignatura)

        <?php

            $calificacion = App\Calificaciones\Calificacion::get_la_calificacion( $periodo_id, $curso->id, $estudiante->id, $asignatura->id);
        ?>
        
        <tr style="font-size: 15px;" >
            <td colspan="2" style="border: 1px solid grey; width:400px;">
                {{ $asignatura->descripcion }}
            </td>
            <td align="center" style="border: 1px solid grey; width:30px;">
                {{ $asignatura->intensidad_horaria }}
            </td>
            <td align="center" style="border: 1px solid grey; width:40px;">
                {{ number_format( $calificacion->valor, 0, ',', '.' ) }}
            </td>
            <td align="center" style="border: 1px solid grey; width:30px;">
                {{ $calificacion->escala_abreviatura }}
            </td>
            <td align="center" style="border: 1px solid grey;">
                {{ $calificacion->escala_nacional }}
            </td>
        </tr>
    @endforeach   