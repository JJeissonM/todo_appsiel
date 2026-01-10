<div class="table-responsive">
    <table class="table table-bordered table-striped">

        <?php  
            $lbl_impuesto = config('ventas.etiqueta_impuesto_principal');
            $impuesto_impoconsumo_id = (int) config('contabilidad.impoconsumo_default_id');
            $tax_category_default = null;
            $tax_category_impoconsumo = null;

            foreach ($doc_registros as $linea) {
                if (!is_null($linea->impuesto)) {
                    if ((int) $linea->impuesto_id === $impuesto_impoconsumo_id) {
                        $tax_category_impoconsumo = 'INC';
                        break;
                    }

                    if ($tax_category_default === null) {
                        $tax_category_default = $linea->impuesto->tax_category;
                    }
                }
            }

            $lbl_impuesto = $tax_category_impoconsumo ?? $tax_category_default ?? $lbl_impuesto;
        ?>

        {{ Form::bsTableHeader(['Cód.','Producto','U.M.','Cantidad','Precio','Total bruto','Sub-total <br> (Sin IVA)','% Dcto.','Total Dcto.',$lbl_impuesto,'Total ' . $lbl_impuesto,'Total','Acción']) }}
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

                <?php 

                    $unidad_medida = $linea->item->get_unidad_medida1();

                    $producto_descripcion = $linea->item->get_value_to_show(true);
                    
                    $referencia = '';
                    if($linea->referencia != '')
                    {
                        $producto_descripcion .= ' - ' . $linea->referencia;
                    }


                ?>
                <tr>
                    <td class="text-center"> {{ $linea->producto_id }} </td>
                    <td> {{ $producto_descripcion }} </td>
                    <td class="text-center"> {{ $unidad_medida }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->cantidad * $linea->precio_unitario, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->cantidad * ($linea->precio_unitario - $linea->valor_impuesto), 0, ',', '.') }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->tasa_descuento, 2, ',', '.') }}% </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }} </td>
                    <td style="text-align: center;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->cantidad * $linea->valor_impuesto, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
                    <td>
                        @if( $doc_encabezado->forma_pago != 'contado' && $doc_encabezado->estado != 'Anulado' && !$docs_relacionados[1]  && Input::get('id_transaccion') == 23 )
                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$linea->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
                        @endif
                    </td>
                </tr>
                <?php
                    $total_cantidad += $linea->cantidad;
                    $total_bruto += (float)$linea->precio_unitario * (float)$linea->cantidad;
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

                    $total_factura += $doc_encabezado->valor_ajuste_al_peso;
                    $total_factura += $doc_encabezado->valor_total_bolsas;


                ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="3"> Cantidad de items: {{ $cantidad_items }} </td>
                <td style="text-align: center;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td colspan="9"> &nbsp; </td>
            </tr>
        </tfoot>
    </table>
</div>

@include('ventas.incluir.factura_firma_totales')
