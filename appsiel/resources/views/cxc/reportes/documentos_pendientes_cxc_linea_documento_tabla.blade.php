<tr>
    @if( $mostrar_columna_tercero )
        <td>
            {{ $linea_movimiento->numero_identificacion }} {{ $linea_movimiento->descripcion_tercero }}
            @if( $linea_movimiento->lbl_estudiante != '' )
                <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Estudiante: </b> {{ $linea_movimiento->lbl_estudiante }}
            @endif
        </td>
    @endif
    <td> {{ $linea_movimiento->documento }} </td>
    <td> {{ $linea_movimiento->fecha }} </td>
    <td> {{ $linea_movimiento->fecha_vencimiento }} </td>
    <td> ${{ number_format($linea_movimiento->valor_documento, 2, ',', '.') }} </td>
    <td> ${{ number_format($linea_movimiento->valor_pagado, 2, ',', '.') }} </td>
    <td> ${{ number_format($linea_movimiento->saldo_pendiente, 2, ',', '.') }} </td>
</tr>