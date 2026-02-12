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
                @endphp
                <td>
                    ${{ number_format($col_valor_pagado, 0, ',', '.') }} / ${{ number_format($col_valor_cartera, 0, ',', '.') }}
                    ({{ number_format($porcentaje_col, 2, ',', '.') }}%)
                    <br/>
                    <b>Pend.</b> ${{ number_format($total, 0, ',', '.') }}<br/>
                </td>
            @endforeach
            @php
                $porcentaje_recaudo = 0;
                if ($total_valor_cartera > 0) {
                    $porcentaje_recaudo = ($total_valor_pagado / $total_valor_cartera) * 100;
                }
            @endphp
            <td>
                ${{ number_format($gran_total, 0, ',', '.') }}<br/>
                ${{ number_format($total_valor_pagado, 0, ',', '.') }} / ${{ number_format($total_valor_cartera, 0, ',', '.') }}
                ({{ number_format($porcentaje_recaudo, 2, ',', '.') }}%)
            </td>
        </tr>
    </tfoot>
</table>
