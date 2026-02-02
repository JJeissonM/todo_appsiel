
    <div style="font-size: 10px;">
        @include('banner_colegio')
    </div>

    @php
        $tituloPeriodo = $periodo ? $periodo->descripcion : 'Todos los periodos';
        $tituloCurso = $curso ? $curso->descripcion : 'Todos los cursos';
        $tituloAsignatura = $asignatura ? $asignatura->descripcion : 'Todas las asignaturas';
        $tituloProfesor = $profesor ? $profesor->name : 'Todos los docentes';
    @endphp

    <table class="table table-bordered" style="font-size: 0.85em;">
        <tr>
            <td><strong>Periodo</strong></td>
            <td>{{ $tituloPeriodo }}</td>
            <td><strong>Curso</strong></td>
            <td>{{ $tituloCurso }}</td>
        </tr>
        <tr>
            <td><strong>Asignatura</strong></td>
            <td>{{ $tituloAsignatura }}</td>
            <td><strong>Docente</strong></td>
            <td>{{ $tituloProfesor }}</td>
        </tr>
    </table>

    <table class="table table-bordered table-striped" style="font-size: 0.85em;">
        <thead>
            <tr>
                <th>Docente</th>
                <th>Curso</th>
                <th>Asignatura</th>
                <th>Periodo</th>
                <th class="text-center">Guias elaboradas</th>
                <th class="text-center">Guias requeridas</th>
                <th class="text-center">Excedente</th>
                <th class="text-center">Cumplimiento %</th>
            </tr>
        </thead>
        <tbody>
            @forelse( $lineas as $linea )
                <tr>
                    <td>{{ $linea->profesor }}</td>
                    <td>{{ $linea->curso }}</td>
                    <td>{{ $linea->asignatura }}</td>
                    <td>{{ $linea->periodo ?? '---' }}</td>
                    <td class="text-center">{{ $linea->guias_elaboradas }}</td>
                    <td class="text-center">{{ $linea->guias_requeridas }}</td>
                    <td class="text-center">{{ $linea->excedente }}</td>
                    <td class="text-center">{{ number_format($linea->cumplimiento, 2) }}%</td>
                </tr>
            @empty
                <tr>
                <td colspan="8" class="text-center">No se encontraron registros de guias para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if( count($lineas) > 0 )
        @php
            $porcentajeGlobal = $totales['requeridas'] > 0
                ? round(($totales['elaboradas'] / $totales['requeridas']) * 100, 2)
                : ($totales['elaboradas'] ? 100 : 0);
        @endphp
        <table class="table table-bordered" style="font-size: 0.85em;">
            <tr>
                <td><strong>Total guias elaboradas</strong></td>
                <td>{{ $totales['elaboradas'] }}</td>
                <td><strong>Total guias requeridas</strong></td>
                <td>{{ $totales['requeridas'] }}</td>
                <td><strong>Cumplimiento general</strong></td>
                <td>{{ number_format($porcentajeGlobal, 2) }}%</td>
            </tr>
        </table>
    @endif
