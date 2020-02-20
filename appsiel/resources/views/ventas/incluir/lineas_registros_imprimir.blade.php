<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Cód.','Producto','U.M.','Precio','IVA','Cantidad','Total']) }}
    <tbody>
        @foreach($doc_registros as $linea )
            <tr>
                <td> {{ $linea->producto_id }} </td>
                <td> {{ $linea->producto_descripcion }} </td>
                <td> {{ $linea->unidad_medida1 }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                <td style="text-align: right;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_total, 2, ',', '.') }} </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">&nbsp;</td>
            <td style="text-align: right;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
            <td style="text-align: right;"> ${{ number_format($total_factura, 2, ',', '.') }} </td>
        </tr>
    </tfoot>
</table>