<table style="width: 100%; font-size: {{ $tamanino_fuente_2 }};" id="tabla_totales">
    <?php
        $total_factura_mas_recargos = $datos_factura->total_factura_mas_propina;
        if ( $datos_factura->total_factura_mas_datafono != '$0,00') {
            $total_factura_mas_recargos = $datos_factura->total_factura_mas_datafono;
        }
    ?>
    <tbody>
        <tr style="font-weight: bold;">
            <td style="text-align: right;"> SubTotal: </td>
            <td style="text-align: right;">
                <div class="lbl_total_factura" style="display: inline; margin-right: 15px;">{{$datos_factura->lbl_total_factura}} </div>
                <br>
                <div class="lbl_ajuste_al_peso" style="display: inline; margin-right: 15px; font-size: 9px;">{{$datos_factura->lbl_ajuste_al_peso}} </div>
            </td>
        </tr>
        @if( (int)config('ventas_pos.manejar_propinas') != 0 )
            <tr style="font-weight: bold;">
                <td style="text-align: right;" id="tr_total_propina"> Propina: </td>
                <td style="text-align: right;">
                    <div class="lbl_total_propina" style="display: inline; margin-right: 15px;">{{$datos_factura->lbl_total_propina}} </div>
                </td>
            </tr>
        @endif
        @if( (int)config('ventas_pos.manejar_datafono') )
            <tr style="font-weight: bold;">
                <td style="text-align: right;" id="tr_total_datafono"> Comisión: </td>
                <td style="text-align: right;">
                    <div class="lbl_total_datafono" style="display: inline; margin-right: 15px;">{{$datos_factura->lbl_total_datafono}} </div>
                </td>
            </tr>
        @endif
        <tr style="font-weight: bold;">
            <td style="text-align: right;" id="tr_total_factura_mas_recargos"> Total factura: </td>
            <td style="text-align: right;">
                <div class="lbl_total_factura_mas_recargos" style="display: inline; margin-right: 15px;">
                    {{$total_factura_mas_recargos}}
                </div>
            </td>
        </tr>
        @if( (int)config('ventas_pos.mostrar_efectivo_recibio_y_cambio') )
            @if($datos_factura->lbl_total_recibido != '')
                <tr style="font-weight: bold;" id="tr_total_recibido">
                    <td style="text-align: right;"> Recibido: </td>
                    <td style="text-align: right;">
                        <div class="lbl_total_recibido" style="display: inline; margin-right: 15px;"> {{$datos_factura->lbl_total_recibido}}</div>
                    </td>
                </tr>
                <tr style="font-weight: bold; font-size: 1.3em;" id="tr_total_cambio">
                    <td style="text-align: right;"> Cambio: </td>
                    <td style="text-align: right;">
                        <div class="lbl_total_cambio" style="display: inline; margin-right: 15px;"> {{$datos_factura->lbl_total_cambio}}</div>
                    </td>
                </tr>
            @endif
        @endif
    </tbody>
</table>
    