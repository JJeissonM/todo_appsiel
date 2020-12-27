<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Cód.','Producto','U.M.','Cantidad','Precio','IVA','Dcto.','Total']) }}
    <tbody>
        @foreach($doc_registros as $linea )
            <?php 

                $unidad_medida = $linea->unidad_medida1;
                if( $linea->producto->unidad_medida2 != '' )
                {
                    $unidad_medida = $linea->producto->unidad_medida1 . ' - Talla: ' . $linea->producto->unidad_medida2;
                }

            ?>

            <tr>
                <td> {{ $linea->producto_id }} </td>
                <td> {{ $linea->producto_descripcion }} </td>
                <td> {{ $unidad_medida }} </td>
                <td style="text-align: right;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                <td style="text-align: right;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                <td style="text-align: right;"> {{ number_format( $linea->tasa_descuento, 2, ',', '.').'%' }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_total, 2, ',', '.') }} </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3">&nbsp;</td>
            <td style="text-align: right;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
            <td colspan="3">&nbsp;</td>
            <td style="text-align: right;"> ${{ number_format($total_factura, 2, ',', '.') }} </td>
        </tr>
        @if( $total_abonos != 0)
            <tr>
                <td colspan="6">&nbsp;</td>
                <td style="text-align: right;"> Total abonos: </td>
                <td style="text-align: right;"> ${{ number_format($total_abonos, 2, ',', '.') }} </td>
            </tr>
            <tr>
                <td colspan="6">&nbsp;</td>
                <td style="text-align: right;"> Total saldo: </td>
                <td style="text-align: right;"> ${{ number_format( $total_factura - $total_abonos, 2, ',', '.') }} </td>
            </tr>
        @endif
    </tfoot>
</table>