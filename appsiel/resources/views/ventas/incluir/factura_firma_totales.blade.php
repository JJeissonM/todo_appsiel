<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <td rowspan="4" width="65%"> <b> Firma del aceptante: </b> <br> </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> Subtotal: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> $ &nbsp;{{ number_format($subtotal, 2, ',', '.') }} </td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> Descuentos: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> - $ &nbsp;{{ number_format($total_descuentos, 2, ',', '.') }} </td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> Impuesto IVA {{ $impuesto_iva }}%: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> + $ &nbsp;{{ number_format($total_impuestos, 2, ',', '.') }} </td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom"> Total: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom"> $ &nbsp;{{ number_format($total_factura, 2, ',', '.') }} </td>
        </tr>
    </table>
</div>