<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','U.M.','Cantidad','Precio','Total bruto','Sub-total <br> (Sin IVA)','% Dcto.','Total Dcto.','IVA','Total IVA','Total','Acción']) }}
        <tbody>
            <?php            
                $total_cantidad = 0;
                $total_bruto = 0;
                $subtotal = 0;
                $total_impuestos = 0;
                $total_descuentos = 0;
                $total_factura = 0;
                $cantidad_items = 0;
            ?>
            @foreach($doc_registros as $linea )

                <?php
                    if ($linea->cantidad == 0) {
                        continue;
                    }
                    $precio_original = $linea->precio_unitario + ( $linea->valor_total_descuento / $linea->cantidad );
                    $subtotal_linea = ( $linea->cantidad * $precio_original ) - $linea->valor_impuesto;

                    $producto_descripcion = $linea->item->get_value_to_show(true);
                    
                    $referencia = '';
                    if($linea->referencia != '')
                    {
                        $referencia = ' - ' . $linea->referencia;
                    }
                    
                    $producto_descripcion .= $referencia;

                ?>
                <tr>
                    <td class="text-center"> {{ $linea->producto_id }} </td>
                    <td> {{ $producto_descripcion }} </td>
                    <td style="text-align: center;"> {{ $linea->unidad_medida1 }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $precio_original, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->cantidad * $precio_original, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $subtotal_linea, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> {{ number_format( $linea->tasa_descuento, 2, ',', '.') }}% </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->valor_impuesto, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->precio_total, 2, ',', '.') }} </td>
                    <td>
                        @if($doc_encabezado->estado != 'Anulado' && !$docs_relacionados[1] )
                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$linea->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
                        @endif
                    </td>
                </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $total_bruto += (float)$precio_original * (float)$linea->cantidad;
                    $subtotal += $subtotal_linea;
                    $total_impuestos += (float)$linea->valor_impuesto;
                    $total_factura += $linea->precio_total;
                    $total_descuentos += $linea->valor_total_descuento;
                    $cantidad_items++;
                ?>
            @endforeach
        </tbody>
        <tfoot>
        <tr style="font-weight: bold;">
            <td colspan="3"> Cantidad de items: {{ $cantidad_items }} </td>
            <td style="text-align: center;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
            <td >&nbsp;</td>
            <td style="text-align: right;"> {{ number_format($total_bruto, 0, ',', '.') }} </td>
            <td style="text-align: right;"> {{ number_format($subtotal, 0, ',', '.') }} </td>
            <td>&nbsp;</td>
            <td style="text-align: right;"> ${{ number_format($total_descuentos, 0, ',', '.') }} </td>
            <td>&nbsp;</td>
            <td style="text-align: right;"> ${{ number_format($total_impuestos, 0, ',', '.') }} </td>
            <td style="text-align: right;"> ${{ number_format($total_factura, 0, ',', '.') }} </td>
            <td>&nbsp;</td>
        </tr>
        </tfoot>
    </table>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <td> <span style="text-align: right; font-weight: bold;"> Subtotal: </span> $ {{ number_format($subtotal, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> (-) Descuentos: </span> $ {{ number_format($total_descuentos, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> (+) Impuestos: </span> $ {{ number_format($total_impuestos, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> (-) Retenciones: </span> $ {{ number_format($valor_retenciones, 0, ',', '.') }}</td>
            <td> <span style="text-align: right; font-weight: bold;"> Total a pagar: </span> $ {{ number_format($total_factura - $valor_retenciones, 0, ',', '.') }}</td>
        </tr>
    </table>
</div>