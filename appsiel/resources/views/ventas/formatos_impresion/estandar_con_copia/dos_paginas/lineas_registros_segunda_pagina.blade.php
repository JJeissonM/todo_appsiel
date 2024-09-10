<!-- Segunda pagina -->
<div style="width: 100%; text-align:right;">
    Pág. 2/{{$cantidad_total_paginas}}
</div>
<table class="table table-bordered table-striped">
    <tbody>
        @foreach($doc_registros_restantes as $linea )
            <?php
                $unidad_medida = $linea->unidad_medida1;
                if( $linea->producto->unidad_medida2 != '' )
                {
                    $unidad_medida = $linea->producto->unidad_medida1 . ' - Talla: ' . $linea->producto->unidad_medida2;
                }
            ?>

            <tr>
                <td align="center"> {{ $linea->producto_id }} </td>
                <td> {{ $linea->producto_descripcion }} </td>
                <td align="center"> {{ $unidad_medida }} </td>
                <td style="text-align: center;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                <td style="text-align: center;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                <td style="text-align: center;"> {{ number_format( $linea->tasa_descuento, 2, ',', '.').'%' }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_total, 2, ',', '.') }} </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        @if( $total_abonos == 0)
            <tr>
                <td colspan="8"><b>Cant. Ítems:</b>{{ $cantidad_items }}</td>
            </tr>
        @else
            <tr>
                <td>
                    <b>Cant. Ítems:</b>{{ $cantidad_items }}
                </td>
                <td>
                    <b>Total abonos:</b> ${{ number_format($total_abonos, 2, ',', '.') }} 
                </td>
                <td>
                    <b>Total saldo:</b> ${{ number_format( $total_factura - $total_abonos, 2, ',', '.') }} 
                </td>
                <td colspan="5">&nbsp;</td>
            </tr>
        @endif
    </tfoot>
</table>