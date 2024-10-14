<div id="div_resumen_totales" style="font-size:13px;">
    
    <div id="total_cantidad" style="display: none;"> 0</div>

    
    @include('core.componentes.productos_y_cantidades_ingresadas')

    <br><br>
    <strong> Total factura </strong>
    <div id="total_factura" style="display: inline;"> $ {{ number_format( $valor_total_factura,'2',',','.') }}</div>
    <input type="hidden" name="valor_total_factura" id="valor_total_factura"
        value="{{$valor_total_factura}}">
    
        <br><br>
    <div id="lbl_ajuste_al_peso" style="display: inline; font-size: 9px;display: none;"> $ 0</div>

    <textarea class="form-control" id="descripcion" rows="4" placeholder="Detalle" name="descripcion" cols="70" style="font-size:1.2em; margin: 5px; border: 1px solid #ddd;"></textarea>

    <div colspan="2" style="text-align: center;">
        <button class="btn btn-lg btn-primary" id="btn_guardar_factura" disabled="disabled"><i
                    class="fa fa-check"></i> Guardar pedido
        </button>
        <button style="display: none;" class="btn btn-sm btn-success" id="btn_imprimir_pedido"><i
                    class="fa fa-print"></i> Re-Imprimir pedido
        </button>
        <button style="display: none;" class="btn btn-lg btn-primary" id="btn_modificar_pedido"><i
                    class="fa fa-check"></i> Modificar pedido
        </button>

        <br><br>
        <button style="display: none;" class="btn btn-md btn-primary" id="btn_crear_nuevo_pedido" data-pedido_label=""><i
                    class="fa fa-trash"></i> Crear Nuevo Pedido
        </button>

        <br><br>
        <!-- @ can('anular_pedido_restaurante') -->
        <button style="display: none;" class="btn btn-sm btn-danger" id="btn_anular_pedido" data-pedido_label=""><i
                    class="fa fa-trash"></i> Anular pedido
        </button>
        
        @include('ventas.pedidos.modal_usuario_supervisor')
        <!-- @ endcan -->
    </div>  

    <hr>

    <div class="table-responsive" id="table_content" style="display: none;">
        <table class="table table-bordered table-striped">
            <tr style="display: none;">
                <td>
                    <strong> Subtotal </strong>
                </td>
                <td style="text-align: right;">
                    <div id="subtotal" style="display: inline;"> $ {{ $valor_subtotal }}</div>
                </td>
            </tr>
            <tr style="display: none;">
                <td>
                    <strong> Descuento </strong>
                </td>
                <td style="text-align: right;">
                    <div id="descuento" style="display: inline;"> $ {{ $valor_descuento }}</div>
                </td>
            </tr>
            <tr style="display: none;">
                <td>
                    <strong> Impuestos </strong>
                </td>
                <td style="text-align: right;">
                    <div id="total_impuestos" style="display: inline;"> $ {{ $valor_total_impuestos }}</div>
                </td>
            </tr>
            <tr class="warning" style="display: none;">
                <td>
                    <strong> Efectivo Recibido </strong>
                </td>
                <td style="text-align: right;">
                    <input type="text" name="efectivo_recibido" id="efectivo_recibido"
                        class="form-control" autocomplete="off">
                    <div id="lbl_efectivo_recibido" style="display: inline;"> $ 999999999999</div>
                </td>
            </tr>
            <tr id="div_total_cambio" class="default" style="display: none;">
                <td>
                    <strong> Total cambio </strong>
                </td>
                <td style="text-align: right;">
                    <div id="total_cambio" style="display: inline;"> $ 0</div>
                </td>
            </tr>
        </table>
    </div>
        
</div>