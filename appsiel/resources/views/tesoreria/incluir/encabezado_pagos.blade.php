<table class="table table-bordered">
    <tr>
        <td width="50%" style="border: solid 1px black; padding-top: -20px;">
            <div>
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa' )
            </div>
        </td>
        <td style="border: solid 1px black; padding-top: -20px;">
            <div style="vertical-align: center;">
                <b style="font-size: 1.4em; text-align: center; display: block;">{{ $descripcion_transaccion }}</b>
                <br/>
                <b>Documento:</b> {{ $encabezado_doc->documento }}
                <br/>
                <b>Fecha:</b> {{ $encabezado_doc->fecha }}
            </div>
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Tercero:</b> {{ $encabezado_doc->tercero }}
        </td>
        <td style="border: solid 1px black;">
            <?php
                $medio_recaudo = $encabezado_doc->medio_recaudo;
            ?>
            <b>Medio pago:</b> {{ $medio_recaudo }}
        </td>
    </tr>
    <tr>
        <td style="border: solid 1px black;">
            <b>Por concepto de:</b> {{ $encabezado_doc->detalle }}            
        </td>
        <td style="border: solid 1px black;">
            @if( $medio_recaudo == 'Efectivo' || $medio_recaudo == 'Cheque' || $medio_recaudo == 'Bono' )
                <b>Caja:</b>  {{ $encabezado_doc->caja }}
            @else
                <b>Cuenta bancaria:</b>  {{ $encabezado_doc->cuenta_bancaria }}
            @endif
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border: solid 1px black;">
            <?php
                $valor_total= '$'.number_format($encabezado_doc->valor_total, 0, ',', '.').' ('.NumerosEnLetras::convertir($encabezado_doc->valor_total,'pesos',false).')';
            ?>
            <b>Valor total:</b> {{ $valor_total }}
        </td>
    </tr>
</table>