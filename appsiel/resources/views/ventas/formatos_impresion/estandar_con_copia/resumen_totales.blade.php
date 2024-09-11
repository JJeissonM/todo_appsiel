<div class="container">

    <div class="contenedor_col">
        <div class="columna_izq_resumen_totales">
            Subtotal: &nbsp;
        </div>
        <div class="columna_resumen_totales">
            $ &nbsp;{{ number_format($subtotal, 2, ',', '.') }}
        </div>
    </div>

    <div class="contenedor_col">
        <div class="columna_izq_resumen_totales">
            Descuentos: &nbsp;
        </div>
        <div class="columna_resumen_totales">
            - $ &nbsp;{{ number_format($total_descuentos, 2, ',', '.') }}
        </div>
    </div>

    <div class="contenedor_col">
        <div class="columna_izq_resumen_totales">
            {{ config('ventas.etiqueta_impuesto_principal') }} {{ $impuesto_iva }}%: &nbsp;
        </div>
        <div class="columna_resumen_totales">
            + $ &nbsp;{{ number_format($total_impuestos, 2, ',', '.') }}
        </div>
    </div>

    <div class="contenedor_col">
        <div class="columna_izq_resumen_totales">
            Total: &nbsp;
        </div>
        <div class="columna_resumen_totales">
            $ &nbsp;{{ number_format($total_factura, 2, ',', '.') }} 
                    <span id="vlr_total_factura" style="display: none;">{{$total_factura}}</span>
        </div>
    </div>
</div>