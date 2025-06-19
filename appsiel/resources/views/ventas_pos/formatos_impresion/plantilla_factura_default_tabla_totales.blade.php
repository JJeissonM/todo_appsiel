<table style="width: 100%; font-size: {{ $tamanino_fuente_2 }};" id="tabla_totales">
    <tbody>
        <tr style="font-weight: bold;">
            <td style="text-align: right;"> Total factura: </td>
            <td style="text-align: right;">
                <div class="lbl_total_factura" style="display: inline; margin-right: 15px;">{{$datos_factura->lbl_total_factura}} </div>
                
                @if( (int)config('ventas_pos.item_bolsa_id') != 0 )
                    <br>
                    <div class="lbl_valor_total_bolsas" style="display: inline; font-size: 9px;">$ {{ $datos_factura->lbl_valor_total_bolsas }}</div>                    
                @endif

                <br>
                <div class="lbl_ajuste_al_peso" style="display: inline; margin-right: 15px; font-size: 9px;">$ {{ $datos_factura->lbl_ajuste_al_peso }} </div>
            </td>
        </tr>
        @if( (int)config('ventas_pos.mostrar_efectivo_recibio_y_cambio') )
            @if($datos_factura->lbl_total_recibido !== '')
                <tr style="font-weight: bold;" id="tr_total_recibido">
                    <td style="text-align: right;"> Recibido: </td>
                    <td style="text-align: right;">
                        <div class="lbl_total_recibido" style="display: inline; margin-right: 15px;"> $ {{ number_format($datos_factura->lbl_total_recibido, 0, ',', '.') }}</div>
                    </td>
                </tr>
                <tr style="font-weight: bold; font-size: 1.3em;" id="tr_total_cambio">
                    <td style="text-align: right;"> Cambio: </td>
                    <td style="text-align: right;">
                        <div class="lbl_total_cambio" style="display: inline; margin-right: 15px;"> $ {{ number_format($datos_factura->lbl_total_cambio, 0, ',', '.')}}</div>
                    </td>
                </tr>
            @endif
        @endif
    </tbody>
</table>
    