<?php
    $row_span = 5;
    if( (int)config('ventas_pos.item_bolsa_id') )
    {
        $row_span = 6;
    }
?>

<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <td rowspan="{{$row_span}}" width="65%"> <b> Firma del aceptante: </b> <br> </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> Subtotal: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-top"> $ &nbsp;{{ number_format($subtotal, 2, ',', '.') }} </td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> Descuentos: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> - $ &nbsp;{{ number_format($total_descuentos, 2, ',', '.') }} </td>
        </tr>
        @if(config('ventas.detallar_iva_cotizaciones'))
            <tr>
                <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> {{ config('ventas.etiqueta_impuesto_principal') }} {{ $impuesto_iva }}%: &nbsp; </td>
                <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-mid"> + $ &nbsp;{{ number_format($total_impuestos, 2, ',', '.') }} </td>
            </tr>
        @endif

        
        @if( (int)config('ventas_pos.item_bolsa_id') )
            <?php 
                $label_signo = '+';
                if($doc_encabezado->valor_total_bolsas < 0) {
                    $label_signo = '-';
                }
                $valor_total_bolsas = abs($doc_encabezado->valor_total_bolsas);
            ?>
            <tr>
                <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom"> Valor bolsas: &nbsp; </td>
                <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom">
                    {{ $label_signo }} $ &nbsp;{{ number_format( $valor_total_bolsas, 2, ',', '.') }} 
                    <span id="valor_total_bolsas" style="display: none;">{{$doc_encabezado->valor_total_bolsas}}</span>
                </td>
            </tr>
        @endif

        <?php 
            $label_signo = '+';
            if($doc_encabezado->valor_ajuste_al_peso < 0) {
                $label_signo = '-';
            }
            $valor_ajuste_al_peso = abs($doc_encabezado->valor_ajuste_al_peso);
        ?>
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom"> Ajuste al peso: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom">
                {{ $label_signo }} $ &nbsp;{{ number_format( $valor_ajuste_al_peso, 2, ',', '.') }} 
                <span id="valor_ajuste_al_peso" style="display: none;">{{$doc_encabezado->valor_ajuste_al_peso}}</span>
            </td>
        </tr>
        <tr>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom"> Total: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom">
                $ &nbsp;{{ number_format($total_factura, 2, ',', '.') }} 
                <span id="vlr_total_factura" style="display: none;">{{$total_factura}}</span>
            </td>
        </tr>
        @if(!config('ventas.detallar_iva_cotizaciones'))
            <tr>
                <td style="text-align: right; font-weight: bold;padding-right: 3px" class="totl-bottom" colspan="2"> IVA NO inluido </td>
            </tr>
        @endif 
    </table>
</div>