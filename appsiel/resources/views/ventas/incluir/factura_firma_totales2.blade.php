<style>
    .celda_label {
        text-align: right;
        font-weight: bold;
        padding-right: 3px;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
    }
    .celda_signo {
        width: 40px;
        text-align: right;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
    }
    .celda_valor {
        width: 80px;
        text-align: right;
        padding-right: 3px;
        border-top: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
    }
</style>
<div class="table-responsive" style="text-align: right;">
    <table class="table">
        <tr>
            <td class="celda_label"> Subtotal: &nbsp; </td>
            <td class="celda_signo">$ &nbsp;</td>
            <td class="celda_valor"> {{ number_format($subtotal, 2, ',', '.') }} </td>
        </tr>
        <tr>
            <td class="celda_label"> Descuentos: &nbsp; </td>
            <td class="celda_signo">(-) $ &nbsp;</td>
            <td class="celda_valor">{{ number_format($total_descuentos, 2, ',', '.') }} </td>
        </tr>
        @if(config('ventas.detallar_iva_cotizaciones'))
            <tr>
                <td class="celda_label"> {{ config('ventas.etiqueta_impuesto_principal') }} {{ $impuesto_iva }}%: &nbsp; </td>
                <td class="celda_signo"> (+) $ &nbsp;</td>
                <td class="celda_valor"> {{ number_format($total_impuestos, 2, ',', '.') }} </td>
            </tr>
        @endif

        <?php 
            $label_signo = '+';
            if($doc_encabezado->valor_total_bolsas < 0) {
                $label_signo = '-';
            }

            $valor_total_bolsas = abs($doc_encabezado->valor_total_bolsas);
        ?>
        <tr>
            <td class="celda_label"> Ajuste: &nbsp; </td>
            <td class="celda_signo"> {{ $label_signo }} $ &nbsp;</td>
            <td class="celda_valor"> {{ number_format($valor_total_bolsas, 0, ',', '.') }} </td>
        </tr>

        <?php 
            $label_signo = '+';
            if($doc_encabezado->valor_ajuste_al_peso < 0) {
                $label_signo = '-';
            }

            $valor_ajuste_al_peso = abs($doc_encabezado->valor_ajuste_al_peso);
        ?>
        <tr>
            <td class="celda_label"> Redondeo al peso: &nbsp; </td>
            <td class="celda_signo"> {{ $label_signo }} $ &nbsp;</td>
            <td class="celda_valor"> {{ number_format($valor_ajuste_al_peso, 0, ',', '.') }} </td>
        </tr>
        <tr>
            <td class="celda_label"> Total: &nbsp; </td>
            <td class="celda_signo">$ &nbsp;</td>
            <td class="celda_valor"> {{ number_format($total_factura, 2, ',', '.') }} </td>
        </tr>
        @if(!config('ventas.detallar_iva_cotizaciones'))
            <tr>
                <td class="celda_valor" colspan="3"> IVA NO inluido </td>
            </tr>
        @endif 
    </table>
</div>