@php
    if (!isset($reportes_cursos) || count($reportes_cursos) == 0) {
        $reportes_cursos = [[
            'curso' => $curso,
            'filas' => $filas,
            'total_columna' => $total_columna,
            'total_columna_valor_cartera' => $total_columna_valor_cartera,
            'total_columna_valor_pagado' => $total_columna_valor_pagado,
            'gran_total' => $gran_total,
            'total_valor_cartera' => $total_valor_cartera,
            'total_valor_pagado' => $total_valor_pagado
        ]];
    }
@endphp

<div style="display: none;">
<table class="report-table">
    @foreach ($reportes_cursos as $reporte_curso_excel)
        @php
            $curso_excel = $reporte_curso_excel['curso'];
            $filas_excel = $reporte_curso_excel['filas'];
            $total_columna_excel = $reporte_curso_excel['total_columna'];
            $total_columna_valor_cartera_excel = $reporte_curso_excel['total_columna_valor_cartera'];
            $total_columna_valor_pagado_excel = $reporte_curso_excel['total_columna_valor_pagado'];
            $gran_total_excel = $reporte_curso_excel['gran_total'];
            $total_valor_cartera_excel = $reporte_curso_excel['total_valor_cartera'];
            $total_valor_pagado_excel = $reporte_curso_excel['total_valor_pagado'];
        @endphp
        <tr>
            <th colspan="14">{{ $titulo }} - Curso {{ $curso_excel->descripcion }}</th>
        </tr>
        <tr>
            <th></th>
            <th>Estudiante</th>
            <th>MAT</th>
            <th>FEB</th>
            <th>MAR</th>
            <th>ABR</th>
            <th>MAY</th>
            <th>JUN</th>
            <th>JUL</th>
            <th>AGO</th>
            <th>SEP</th>
            <th>OCT</th>
            <th>NOV</th>
            <th>{{ $lbl_total }}</th>
        </tr>
        @forelse ($filas_excel as $fila_excel)
            <tr>
                <td>{{ $fila_excel['num'] }}</td>
                <td>{{ $fila_excel['estudiante'] }}</td>
                <td>{!! $fila_excel['matricula'] !== '' ? $fila_excel['matricula'] : '&nbsp;' !!}</td>
                @foreach ($fila_excel['meses'] as $mes_excel)
                    <td>{!! $mes_excel !== '' ? $mes_excel : '&nbsp;' !!}</td>
                @endforeach
                <td>{!! $fila_excel['total_linea'] !== '' ? $fila_excel['total_linea'] : '&nbsp;' !!}</td>
            </tr>
        @empty
            <tr>
                <td colspan="14">No hay datos para mostrar.</td>
            </tr>
        @endforelse
        <tr>
            <td colspan="2">Totales</td>
            @foreach ($total_columna_excel as $idx_excel => $total_excel)
                @php
                    $col_valor_cartera_excel = $total_columna_valor_cartera_excel[$idx_excel] ?? 0;
                    $col_valor_pagado_excel = $total_columna_valor_pagado_excel[$idx_excel] ?? 0;
                @endphp
                <td>
                    ${{ number_format($col_valor_pagado_excel, 0, ',', '.') }} / ${{ number_format($col_valor_cartera_excel, 0, ',', '.') }}
                    Pend. ${{ number_format($total_excel, 0, ',', '.') }}
                </td>
            @endforeach
            <td>
                ${{ number_format($total_valor_pagado_excel, 0, ',', '.') }} / ${{ number_format($total_valor_cartera_excel, 0, ',', '.') }}
                Pend. ${{ number_format($gran_total_excel, 0, ',', '.') }}
            </td>
        </tr>
        <tr>
            <td colspan="14">&nbsp;</td>
        </tr>
    @endforeach
</table>
</div>

@foreach ($reportes_cursos as $reporte_curso)
@php
    $curso = $reporte_curso['curso'];
    $filas = $reporte_curso['filas'];
    $total_columna = $reporte_curso['total_columna'];
    $total_columna_valor_cartera = $reporte_curso['total_columna_valor_cartera'];
    $total_columna_valor_pagado = $reporte_curso['total_columna_valor_pagado'];
    $gran_total = $reporte_curso['gran_total'];
    $total_valor_cartera = $reporte_curso['total_valor_cartera'];
    $total_valor_pagado = $reporte_curso['total_valor_pagado'];
@endphp

<p style="text-align: center; font-size: 15px; font-weight: bold;">
    {{ $titulo }} <br/> Curso {{ $curso->descripcion }}
</p>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th> </th>
            <th> Estudiante </th>
            <th> MAT </th>
            <th> FEB </th>
            <th> MAR </th>
            <th> ABR </th>
            <th> MAY </th>
            <th> JUN </th>
            <th> JUL </th>
            <th> AGO </th>
            <th> SEP </th>
            <th> OCT </th>
            <th> NOV </th>
            <th> {{ $lbl_total }} </th>
        </tr>
    </thead>
    <tbody>
        @forelse ($filas as $fila)
            <tr>
                <td>{{ $fila['num'] }}</td>
                <td>{{ $fila['estudiante'] }}</td>
                <td align="center">{!! $fila['matricula'] !== '' ? $fila['matricula'] : '&nbsp;' !!}</td>
                @foreach ($fila['meses'] as $mes)
                    <td align="center">{!! $mes !== '' ? $mes : '&nbsp;' !!}</td>
                @endforeach
                <td>{!! $fila['total_linea'] !== '' ? $fila['total_linea'] : '&nbsp;' !!}</td>
            </tr>
        @empty
            <tr>
                <td colspan="14">No hay datos para mostrar.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"></td>
            @foreach ($total_columna as $idx => $total)
                @php
                    $col_valor_cartera = $total_columna_valor_cartera[$idx] ?? 0;
                    $col_valor_pagado = $total_columna_valor_pagado[$idx] ?? 0;
                    $porcentaje_col = 0;
                    if ($col_valor_cartera > 0) {
                        $porcentaje_col = ($col_valor_pagado / $col_valor_cartera) * 100;
                    }
                    $badge_color = '#5cb85c';
                    if ($porcentaje_col < 60) {
                        $badge_color = '#d9534f';
                    } elseif ($porcentaje_col < 90) {
                        $badge_color = '#f0ad4e';
                    }
                @endphp
                <td>
                    ${{ number_format($col_valor_pagado, 0, ',', '.') }} / ${{ number_format($col_valor_cartera, 0, ',', '.') }}
                    <span class="label" style="background-color: {{ $badge_color }}; font-size: 16px;" title="Porcentaje de recaudo">
                        {{ number_format($porcentaje_col, 2, ',', '.') }}%
                    </span>
                    <br/>
                    <b>Pend.</b> ${{ number_format($total, 0, ',', '.') }}<br/>
                </td>
            @endforeach
            @php
                $porcentaje_recaudo = 0;
                if ($total_valor_cartera > 0) {
                    $porcentaje_recaudo = ($total_valor_pagado / $total_valor_cartera) * 100;
                }
                $badge_color_total = '#5cb85c';
                if ($porcentaje_recaudo < 60) {
                    $badge_color_total = '#d9534f';
                } elseif ($porcentaje_recaudo < 90) {
                    $badge_color_total = '#f0ad4e';
                }
            @endphp
            <td>
                ${{ number_format($total_valor_pagado, 0, ',', '.') }} / ${{ number_format($total_valor_cartera, 0, ',', '.') }}
                <span class="label" style="background-color: {{ $badge_color_total }}; font-size: 16px;" title="Porcentaje de recaudo">
                    {{ number_format($porcentaje_recaudo, 2, ',', '.') }}%
                </span>
                <br/>
                <b>Pend.</b> ${{ number_format($gran_total, 0, ',', '.') }}
            </td>
        </tr>
    </tfoot>
</table>
<br/>
@endforeach
