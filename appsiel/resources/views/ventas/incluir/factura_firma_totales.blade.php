<table class="table table-bordered">
    <tr>
        <td rowspan="3" width="80%"> <b> Firma del aceptante: </b> <br> </td>
        <td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td>
        <td style="text-align: right; font-weight: bold;"> $ {{ number_format($subtotal, 2, ',', '.') }} </td>
    </tr>
    <tr>
        <td style="text-align: right; font-weight: bold;"> Impuestos: &nbsp; </td>
        <td style="text-align: right; font-weight: bold;"> $ {{ number_format($total_impuestos, 2, ',', '.') }} </td>
    </tr>
    <tr>
        <td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td>
        <td style="text-align: right; font-weight: bold;"> $ {{ number_format($total_factura, 2, ',', '.') }} </td>
    </tr>
</table>