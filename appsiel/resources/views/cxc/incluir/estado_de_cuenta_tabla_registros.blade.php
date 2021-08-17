<table id="myTable" class="table table-striped">
    <thead>
        <tr>
            <th> Documento </th>
            <th> Fecha </th>
            <th> Fecha <br> vencimiento </th>
            <th> Valor <br> Documento </th>
            <th> Valor <br> pagado </th>
            <th> Saldo <br> pendiente </th>
        </tr>
    </thead>
    <tbody>
        <?php
            $j = 1;
            $total_valor_documento = 0;
            $total_valor_pagado = 0;
            $total_saldo_pendiente = 0;

            $cantidad = count($movimiento);

            for($i=0; $i<$cantidad; $i++)
            { 

                if ( $movimiento[$i]['saldo_pendiente'] > -0.1 && $movimiento[$i]['saldo_pendiente'] < 0.1 )
                {
                    continue;
                }
        ?>
                    
                
            @if($movimiento[$i]['id'] != 0)
                <tr class="fila-{{$j}}" id="{{ $movimiento[$i]['id'] }}">
                    <td> {{ $movimiento[$i]['documento'] }} </td>
                    <td> {{ $movimiento[$i]['fecha'] }} </td>
                    <td> {{ $movimiento[$i]['fecha_vencimiento'] }} </td>
                    <td> ${{ number_format($movimiento[$i]['valor_documento'], 2, ',', '.') }} </td>
                    <td> ${{ number_format($movimiento[$i]['valor_pagado'], 2, ',', '.') }} </td>
                    <td class="col_saldo_pendiente" data-saldo_pendiente="{{$movimiento[$i]['saldo_pendiente']}}"> ${{ number_format($movimiento[$i]['saldo_pendiente'], 2, ',', '.') }} </td>
                </tr>
            @else
            @endif
         <?php
            $j++;
            if ($j==3) {
                $j=1;
            }
            $total_valor_documento += $movimiento[$i]['valor_documento'];
            $total_valor_pagado += $movimiento[$i]['valor_pagado'];
            $total_saldo_pendiente += $movimiento[$i]['saldo_pendiente'];
        } // END FOR
        ?>

        <tr  class="fila-{{$j}}" style="background: #4a4a4a; color: white;" >
            <td colspan="3"> &nbsp; </td>
            <td> ${{ number_format($total_valor_documento, 2, ',', '.') }} </td>
            <td> ${{ number_format($total_valor_pagado, 2, ',', '.') }} </td>
            <td> ${{ number_format($total_saldo_pendiente, 2, ',', '.') }} </td>
        </tr>
    </tbody>
</table>