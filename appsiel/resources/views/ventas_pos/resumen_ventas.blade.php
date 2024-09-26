<h3> Resumén de ventas</h3>
<hr>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Forma de pago</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td> Efectivo </td>
                <td style="text-align: right;"> ${{ number_format($total_efectivo, 0, ',','.') }} </td>
            </tr>
            <tr>
                <td> Cuentas bancarias </td>
                <td style="text-align: right;"> ${{ number_format($total_bancos, 0, ',','.') }} </td>
            </tr>
            <tr>
                <td> Crédito </td>
                <td style="text-align: right;"> ${{ number_format($total_credito, 0, ',','.') }} </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td> <b>Total Venta</b> </td>
                <td style="text-align: right;"> <b>${{ number_format( $total_efectivo + $total_bancos + $total_credito, 0, ',','.') }}</b> </td>
            </tr>
        </tfoot>
    </table>
</div>
    