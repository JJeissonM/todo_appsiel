<div class="table-responsive" style="text-align: right;">
    <table class="table table-bordered" style="display: inline;">
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> Subtotal: &nbsp; </td>
            <td>$ &nbsp;</td>
            <td style="text-align: right;padding-right: 3px" class="totl-top"> {{ number_format($subtotal, 2, ',', '.') }} </td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> Descuentos: &nbsp; </td>
            <td>(-) $ &nbsp;</td>
            <td style="text-align: right;padding-right: 3px" class="totl-mid">{{ number_format($total_descuentos, 2, ',', '.') }} </td>
        </tr>
        @if(config('ventas.detallar_iva_cotizaciones'))
            <tr>
                <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> {{ config('ventas.etiqueta_impuesto_principal') }} {{ $impuesto_iva }}%: &nbsp; </td>
                <td> (+) $ &nbsp;</td>
                <td style="text-align: right;padding-right: 3px" class="totl-mid"> {{ number_format($total_impuestos, 2, ',', '.') }} </td>
            </tr>
        @endif         
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom"> Total: &nbsp; </td>
            <td>$ &nbsp;</td>
            <td style="text-align: right;padding-right: 3px" class="totl-bottom"> {{ number_format($total_factura, 2, ',', '.') }} </td>
        </tr>
        @if(!config('ventas.detallar_iva_cotizaciones'))
            <tr>
                <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom" colspan="2"> IVA NO inluido </td>
            </tr>
        @endif 
    </table>
</div>