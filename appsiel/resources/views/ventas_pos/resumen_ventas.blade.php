<h3> Resumén de ventas </h3>
<hr>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Facturas</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td> Contado </td> <td> ${{ number_format($total_contado, 0, ',','.') }} </td>
            </tr>
            <tr>
                <td> Crédito </td> <td> ${{ number_format($total_credito, 0, ',','.') }} </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td> Total Venta </td>
                <td> ${{ number_format( $total_contado + $total_credito, 0, ',','.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>
    