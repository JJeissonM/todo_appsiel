<div id="div_resumen_totales" style="font-size:13px;">
    
    <hr>    

    @if( Input::get('action') == 'create_from_order' )
        <div class="container-fluid" style="text-align: center;">
            <button class="btn btn-default btn-detail" id="btn_recalcular_totales">Re-Calcular totales</button>
        </div>
        <br>
    @endif

    <div id="total_cantidad" style="display: none;"> 0</div>

    <a href="javascript: void(0)" onclick="set_cliente_default()"> <i class="fa fa-refresh"></i> Cambiar a Consumidor Final </a>
        
    <table class="table table-bordered table-striped">
        <tr>
            <td colspan="2">
                <label class="control-label col-sm-3 col-md-3" for="cliente_input">Cliente:</label>              
                <div class="col-sm-9 col-md-9">
                    <input class="form-control" id="cliente_input" autocomplete="off" required="required" name="cliente_input" type="text" value="{{ $cliente->tercero->descripcion }}">
                </div>
            </td>
        </tr>
        
        @if( !(int)config('ventas_pos.ocultar_fila_descuento_resumen_totales_create') || (int)config('configuracion.liquidacion_impuestos') )
            <tr>
                <td width="35%">
                    <strong> Subtotal </strong>
                </td>
                <td style="text-align: right;" colspan="2">
                    <div id="subtotal" style="display: inline;"> $ {{ $valor_subtotal }}</div>
                </td>
            </tr>
        @endif

        @if( !(int)config('ventas_pos.ocultar_fila_descuento_resumen_totales_create') )
            <tr>
                <td width="35%">
                    <strong> Descuento </strong>
                </td>
                <td style="text-align: right;" colspan="2">
                    <div id="descuento" style="display: inline;"> $ {{ $valor_descuento }}</div>
                </td>
            </tr>
        @endif
        
        @if( (int)config('configuracion.liquidacion_impuestos') )
            <tr>
                <td width="35%">
                    <strong> Impuestos </strong>
                </td>
                <td style="text-align: right;" colspan="2">
                    <div id="total_impuestos" style="display: inline;"> $ {{ $valor_total_impuestos }}</div>
                </td>
            </tr>
        @endif

        @if( (int)config('ventas_pos.manejar_propinas') )

            @include('ventas_pos.propinas.filas_resumen_totales')
            
        @endif

        @if( (int)config('ventas_pos.manejar_datafono') )

            @include('ventas_pos.datafono.filas_resumen_totales')
            
        @endif

        <tr class="info">
            <td width="35%">
                @if( (int)config('ventas_pos.item_bolsa_id') != 0 )
                    <br>
                @endif
                <br>
                <strong> Total factura </strong>
            </td>
            <td style="text-align: right;" colspan="2">
                @if( (int)config('ventas_pos.item_bolsa_id') != 0 )
                    <div id="lbl_valor_total_bolsas" style="display: inline; font-size: 9px; color: green;"> $ 0</div>                    
                @endif
                <div id="lbl_ajuste_al_peso" style="font-size: 9px;"> $ 0</div>
                <div id="total_factura" style="font-weight: bold;"> $ {{ number_format( $valor_total_factura,'2',',','.') }}</div>
                <input type="hidden" name="valor_total_factura" id="valor_total_factura" value="{{ $valor_total_factura }}">
            </td>
        </tr>
        <tr id="div_efectivo_recibido" class="warning">
            <td width="35%">
                <strong> Efectivo Recibido </strong>
            </td>
            <td style="text-align: right;" colspan="2">
                <input type="text" name="efectivo_recibido" id="efectivo_recibido"
                       class="form-control" autocomplete="off" style="background-color: white !important; border-radius: 4px;">
                <div id="lbl_efectivo_recibido" style="display: inline;"> $ 0</div>
            </td>
        </tr>
        <tr id="div_total_cambio" class="default">
            <td width="35%">
                <strong> Total cambio </strong>
            </td>
            <td style="text-align: right;" colspan="2">
                <div id="total_cambio" style="display: inline;"> $ 0</div>
            </td>
        </tr>
        <tr class="default">
            <td colspan="3" style="text-align: center;">

                @if ( !(int)config('ventas_pos.ocultar_boton_guardar_factura_pos') )
                    <button class="btn btn-lg btn-primary" id="btn_guardar_factura" disabled="disabled">
                        <i class="fa fa-check"></i> Guardar factura
                    </button>
                @endif                
                
                <p style="color:rgb(248, 51, 51);">
                    <span id="msj_ventana_impresion_abierta" style="display: none;"><i class="fa fa-warning"></i> ¡Ventana de impresión abierta!</span>
                </p>

                <button type="button" class="btn btn-danger btn-xs" id="btn_probar" style="display: none;">Probar</button>                
            </td>
        </tr>
    </table>
</div>