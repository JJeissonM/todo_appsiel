<table class="table table-bordered contenido" align="center">
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

            $calificacion = App\Calificaciones\Calificacion::get_la_calificacion( $periodo_id, $curso->id, $estudiante->id, $asignatura->id);
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
                {{ $calificacion->escala_abreviatura }}
            </td>
            <td align="center" style="border: 1px solid grey;">
                {{ $calificacion->escala_nacional }}
            </td>
        </tr>
    @endforeach       
    </tbody>
</table>