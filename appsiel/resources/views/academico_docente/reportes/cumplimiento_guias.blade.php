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
            <th class="text-center">Accion</th>
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
                <td class="text-center">
                    @if( count($linea->guias_links) > 0 )
                        <button type="button" class="btn btn-xs btn-info btn-ver-guias" data-guias="{{ base64_encode(json_encode($linea->guias_links)) }}">Ver guias</button>
                    @else
                        <span class="label label-default">Sin guias</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">No se encontraron registros de guias para los filtros seleccionados.</td>
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

<div class="modal fade" id="modal_guias_cumplimiento" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Guias elaboradas</h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" style="font-size: 0.9em; margin-bottom: 12px;">
                    <tr>
                        <td><strong>Docente</strong></td>
                        <td id="modal_guias_docente"></td>
                        <td><strong>Curso</strong></td>
                        <td id="modal_guias_curso"></td>
                    </tr>
                    <tr>
                        <td><strong>Asignatura</strong></td>
                        <td id="modal_guias_asignatura"></td>
                        <td><strong>Periodo</strong></td>
                        <td id="modal_guias_periodo"></td>
                    </tr>
                </table>
                <ul id="lista_guias_cumplimiento" style="padding-left: 18px; margin: 0;"></ul>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).off('click', '.btn-ver-guias').on('click', '.btn-ver-guias', function () {
        var data = $(this).attr('data-guias');
        var fila = $(this).closest('tr').find('td');
        var guias = [];

        $('#modal_guias_docente').text($(fila[0]).text().trim());
        $('#modal_guias_curso').text($(fila[1]).text().trim());
        $('#modal_guias_asignatura').text($(fila[2]).text().trim());
        $('#modal_guias_periodo').text($(fila[3]).text().trim());

        try {
            guias = JSON.parse(atob(data));
        } catch (e) {
            guias = [];
        }

        var html = '';
        for (var i = 0; i < guias.length; i++) {
            html += '<li><a href="' + guias[i].url + '" target="_blank">' + guias[i].descripcion + '</a></li>';
        }

        if (html === '') {
            html = '<li>No hay guias para esta linea.</li>';
        }

        $('#lista_guias_cumplimiento').html(html);
        $('#modal_guias_cumplimiento').modal('show');
    });
</script>



