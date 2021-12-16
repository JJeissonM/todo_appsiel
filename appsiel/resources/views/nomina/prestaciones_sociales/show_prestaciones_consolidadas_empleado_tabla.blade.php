<h4 style="width: 100%;text-align: center;">
    <strong> {{ $titulo }} </strong>
</h4>

<table class="table table-striped table-bordered">
    <thead>
        <tr style="font-weight: bold;">
            <th> Fecha final promedios </th>
            <th> Días laborados </th>
            <th> Acum. mes anterior </th>
            <th> Vlr. pagado mes </th>
            <th> Vlr. consol. mes </th>
            <th> Días consol. </th>
            <th> Vlr. acumulado </th>
        </tr>
    </thead>
    <tbody>
        @foreach( $lista_consolidados as $fila )
            <tr>
                <td> {{ $fila->fecha_fin_mes }} </td>
                <td> {{ number_format( $fila->dias_totales_laborados, 0, ',', '.' ) }} </td>
                <td> ${{ number_format( $fila->valor_acumulado_mes_anterior, 0, ',', '.' ) }} </td>
                <td> ${{ number_format( $fila->valor_pagado_mes, 0, ',', '.' ) }} </td>
                <td> ${{ number_format( $fila->valor_consolidado_mes, 0, ',', '.' ) }} </td>
                <td> {{ number_format( $fila->dias_consolidado_mes, 2, ',', '.' ) }} </td>
                <td> ${{ number_format( $fila->valor_acumulado, 0, ',', '.' ) }} </td>
            </tr>
        @endforeach
    </tbody>
</table>