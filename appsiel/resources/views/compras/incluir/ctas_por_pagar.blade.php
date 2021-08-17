<h3 style="width: 100%; text-align: center;"> Documentos pendientes de Cuentas por Pagar </h3>
<hr>
<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th style="display: none;"> ID Proveedor </th>
                <th> Proveedor </th>
                <th> Documento interno </th>
                <th> Factura del proveedor </th>
                <th> Fecha </th>
                <th> Fecha vencimiento </th>
                <th> Valor Documento </th>
                <th> Valor pagado </th>
                <th> Saldo pendiente </th>
                <th class="td_boton"> </th>
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
                    if ( $movimiento[$i]['saldo_pendiente'] > -0.1 && $movimiento[$i]['saldo_pendiente'] < 0.1 && $movimiento[$i]['id'] != 0 )
                    {
                        continue;
                    }

                    if ( $movimiento[$i]['estado'] == 'Pagado' )
                    {
                        continue;
                    }
            ?>                    
                    
                @if($movimiento[$i]['id'] != 0)
                    <tr class="fila-{{$j}}" id="{{ $movimiento[$i]['id'] }}">
                        <td style="display: none;"> {{ $movimiento[$i]['id'] }} </td>
                        <td> {{ $movimiento[$i]['tercero'] }} </td>
                        <td> {{ $movimiento[$i]['documento'] }} </td>
                        <td> {{ $movimiento[$i]['doc_proveedor_prefijo'] }} - {{ $movimiento[$i]['doc_proveedor_consecutivo'] }} </td>
                        <td> {{ $movimiento[$i]['fecha'] }} </td>
                        <td> {{ $movimiento[$i]['fecha_vencimiento'] }} </td>
                        <td> ${{ number_format($movimiento[$i]['valor_documento'], 2, ',', '.') }} </td>
                        <td> ${{ number_format($movimiento[$i]['valor_pagado'], 2, ',', '.') }} </td>
                        <td class="col_saldo_pendiente" data-saldo_pendiente="{{$movimiento[$i]['saldo_pendiente']}}"> ${{ number_format($movimiento[$i]['saldo_pendiente'], 2, ',', '.') }} </td>

                        <td style="display: none;" class="td_boton"> 
                            {{ Form::text('text_aplicar_'.$movimiento[$i]['id'], $movimiento[$i]['saldo_pendiente'], [ 'id' => 'text_aplicar_'.$movimiento[$i]['id'], 'class' => 'text_aplicar' ] ) }} 
                            <a href="#" class="btn btn-success btn-xs btn_agregar_documento" style="display: none;"><i class="fa fa-check"></i></a>
                        </td>
                    </tr>
                @else
                    <?php 
                        if ( $movimiento[$i]['sub_total'] > -0.1 && $movimiento[$i]['sub_total'] < 0.1 ) {
                            continue;
                        }
                    ?>
                    <tr class="fila-{{$j}}" id="{{ $movimiento[$i]['id'] }}" style="background: #4a4a4a; color: white;">
                        <td style="display: none;"> {{ $movimiento[$i]['id'] }} </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><strong>Sub Total</strong></td>
                        <td class="col_saldo_pendiente" data-saldo_pendiente="{{$movimiento[$i]['sub_total']}}"><strong> ${{ number_format($movimiento[$i]['sub_total'], 2, ',', '.') }} </strong></td>
                    </tr>
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

            <tr  class="fila-{{$j}}" >
                <td style="display: none;"> &nbsp; </td>
                <td colspan="5"> &nbsp; </td>
                <td> ${{ number_format($total_valor_documento, 2, ',', '.') }} </td>
                <td> ${{ number_format($total_valor_pagado, 2, ',', '.') }} </td>
                <td> ${{ number_format($total_saldo_pendiente, 2, ',', '.') }} </td>
                <td style="display: none;" class="td_boton"> &nbsp; </td>
            </tr>
        </tbody>
    </table>
</div>