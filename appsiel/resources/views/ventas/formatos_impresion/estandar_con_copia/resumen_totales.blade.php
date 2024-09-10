<table class="table table-bordered">
    <tr>
        <td> Subtotal: &nbsp; </td>
        <td> $ &nbsp;{{ number_format($subtotal, 2, ',', '.') }} </td>
    </tr>
    <tr>
        <td> Descuentos: &nbsp; </td>
        <td> - $ &nbsp;{{ number_format($total_descuentos, 2, ',', '.') }} </td>
    </tr>
    @if(config('ventas.detallar_iva_cotizaciones'))
        <tr>
            <td> {{ config('ventas.etiqueta_impuesto_principal') }} {{ $impuesto_iva }}%: &nbsp; </td>
            <td> + $ &nbsp;{{ number_format($total_impuestos, 2, ',', '.') }} </td>
        </tr>
    @endif         
    <tr>
        <td> Total: &nbsp; </td>
        <td>
            $ &nbsp;{{ number_format($total_factura, 2, ',', '.') }} 
            <span id="vlr_total_factura" style="display: none;">{{$total_factura}}</span>
        </td>
    </tr>
    @if(!config('ventas.detallar_iva_cotizaciones'))
        <tr>
            <td colspan="2"> IVA NO inluido </td>
        </tr>
    @endif 
</table>