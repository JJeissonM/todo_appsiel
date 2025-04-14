
<table class="contenido table table-bordered">
<thead>
    <tr>
        <th>Asignaturas</th>
        <th>Logros</th>
        <th>Desempeño</th>
    </tr>
</thead>
<tbody>
    <?php
        //dd($estudiante);
    ?>

    @foreach($asignaturas as $asignatura) 

        <?php
            $is_the_first = true;
            if ($asignatura->id == (int)config('calificaciones.asignatura_id_para_asistencias')) {
                continue;
            }

            $logros_asignatura = $logros->where('asignatura_id', $asignatura->id)->all();


            $cant_logros = count( $logros_asignatura );
            //dd($asignatura->descripcion, $logros, $cant_logros );
        ?>

        @foreach ( $logros_asignatura as $logro )
            <tr>
                @if($is_the_first)
                    <td rowspan="{{ $cant_logros }}" style="font-size:12px; vertical-align: middle;" width="250px" >
                        <b> {{ $asignatura->descripcion }}</b>
                    </td>
                    <?php
                        $is_the_first = false;
                    ?>						
                @endif
                <td style="text-align: left;"> 
                    {{ $logro->descripcion }} 
                </td>

                <?php
                    $valor_desempenio = $todas_las_calificaciones->where('matricula_id', $estudiante->matricula_id )->where('logro_id', $logro->id)->first();
                ?>

                @if( $valor_desempenio == null )                
                    <td {!! $estilo_advertencia !!}>--</td>
                @else
                    <td> {{ $valor_desempenio->escala_valoracion->nombre_escala }} </td>
                @endif

            </tr>
        @endforeach
    
        @if($cant_logros == 0)
            <tr>
                <td style="font-size:12px; vertical-align: middle;" width="250px" >
                    <b> {{ $asignatura->descripcion }}</b>
                </td>
                <td colspan="2" {!! $estilo_advertencia !!}>
                    No hay logros registrados en este periodo.
                </td>
            </tr>
        @endif

    @endforeach