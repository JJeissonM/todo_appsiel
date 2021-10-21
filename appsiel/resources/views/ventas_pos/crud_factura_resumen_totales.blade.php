<div id="div_resumen_totales" style="font-size:13px;">
    

    <h5 style="width: 100%; text-align: center;">Totales</h5>
    <hr>

    @if( Input::get('action') == 'create_from_order' )
        <div class="container-fluid" style="text-align: center;">
            <button class="btn btn-default btn-detail" id="btn_recalcular_totales">Re-Calcular totales</button>
        </div>
        <br>
    @endif

    <div id="total_cantidad" style="display: none;"> 0</div>

    <table class="table table-bordered table-striped">
        <tr>
            <td width="35%">
                <strong> Subtotal </strong>
            </td>
            <td style="text-align: right;">
                <div id="subtotal" style="display: inline;"> $ {{ $valor_subtotal }}</div>
            </td>
        </tr>
        <tr>
            <td width="35%">
                <strong> Descuento </strong>
            </td>
            <td style="text-align: right;">
                <div id="descuento" style="display: inline;"> $ {{ $valor_descuento }}</div>
            </td>
        </tr>
        <tr>
            <td width="35%">
                <strong> Impuestos </strong>
            </td>
            <td style="text-align: right;">
                <div id="total_impuestos" style="display: inline;"> $ {{ $valor_total_impuestos }}</div>
            </td>
        </tr>
        <tr>
            <td width="35%">
                <strong> Total factura </strong>
            </td>
            <td style="text-align: right;">
                <div id="total_factura" style="display: inline;"> $ {{ number_format( $valor_total_factura,'2',',','.') }}</div>
                <input type="hidden" name="valor_total_factura" id="valor_total_factura"
                       value="{{$valor_total_factura}}">
                <br>
                <div id="lbl_ajuste_al_peso" style="display: inline; font-size: 9px;"> $ 0</div>
            </td>
        </tr>
        <tr class="warning">
            <td width="35%">
                <strong> Efectivo Recibido </strong>
            </td>
            <td style="text-align: right;">
                <input type="text" name="efectivo_recibido" id="efectivo_recibido"
                       class="form-control" autocomplete="off">
                <div id="lbl_efectivo_recibido" style="display: inline;"> $ 0</div>
            </td>
        </tr>
        <tr id="div_total_cambio" class="default">
            <td width="35%">
                <strong> Total cambio </strong>
            </td>
            <td style="text-align: right;">
                <div id="total_cambio" style="display: inline;"> $ 0</div>
            </td>
        </tr>
        <tr class="default">
            <td colspan="2" style="text-align: center;">
                <button class="btn btn-lg btn-primary" id="btn_guardar_factura" disabled="disabled"><i
                            class="fa fa-check"></i> Guardar factura
                </button>
            </td>
        </tr>
    </table>
</div>