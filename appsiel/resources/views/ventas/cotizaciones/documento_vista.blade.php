<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','U.M.','Cantidad','Precio','Total bruto','Sub-total <br> (Sin IVA)','% Dcto.','Total Dcto.','IVA','Total IVA','Total']) }}
        <tbody>
            <?php 
            
            $total_cantidad = 0;
            $total_bruto = 0;
            $subtotal = 0; // Sin impuestos
            $total_impuestos = 0;
            $total_factura = 0;
            $total_descuentos = 0;
            $cantidad_items = 0;

            $impuesto_iva = 0;//iva en firma
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td class="text-center"> {{ $linea->producto_id }} </td>
                    <?php 
                        $descripcion_item = $linea->producto_descripcion;

                        if( $linea->unidad_medida2 != '' )
                        {
                            $descripcion_item = $linea->producto_descripcion . ' - Talla: ' . $linea->unidad_medida2;
                        }
                    ?>
                    <td> {{ $descripcion_item }} </td>
                    <td class="text-center"> {{ $linea->unidad_medida1 }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->cantidad * $linea->precio_unitario, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->cantidad * ($linea->precio_unitario - $linea->valor_impuesto), 0, ',', '.') }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->tasa_descuento, 2, ',', '.') }}% </td>
                    <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->valor_total_descuento, 0, ',', '.') }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->cantidad * $linea->valor_impuesto, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> $ &nbsp;{{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $total_cantidad += $linea->cantidad;
                    $total_bruto += (float)$linea->precio_unitario * (float)$linea->cantidad;
                    //$subtotal += (float)($linea->precio_unitario - $linea->valor_impuesto) * (float)$linea->cantidad;
                    $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                    $total_factura += $linea->precio_total;
                    $total_descuentos += $linea->valor_total_descuento;
                    $cantidad_items++;
                    
                    if($linea->valor_impuesto > 0){
                        $impuesto_iva = $linea->tasa_impuesto;
                    }
                ?>
            @endforeach
                <?php
                    $subtotal = $total_factura + $total_descuentos - $total_impuestos;
                    $subtotal_sin_iva = $total_bruto - $total_impuestos;
                ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="3"> Cantidad de items: {{ $cantidad_items }} </td>
                <td style="text-align: center;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td >&nbsp;</td>
                <td style="text-align: right;"> $ &nbsp;{{ number_format($total_bruto, 0, ',', '.') }} </td>
                <td style="text-align: right;"> $ &nbsp;{{ number_format($subtotal_sin_iva, 0, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> $ &nbsp;{{ number_format($total_descuentos, 0, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> $ &nbsp;{{ number_format($total_impuestos, 0, ',', '.') }} </td>
                <td style="text-align: right;"> $ &nbsp;{{ number_format($total_factura, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>

@include('ventas.incluir.factura_firma_totales')