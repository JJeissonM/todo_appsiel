<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','Bodega','Motivo','Costo unitario','Cantidad','Costo Total','']) }}
        <tbody>
            <?php 
            
            $total_cantidad = 0;
            $subtotal = 0;
            $total_impuestos = 0;
            $total_factura = 0;

            //print_r($doc_registros);
            ?>
            @foreach($doc_registros as $linea )
                <?php

                    $descripcion_item = $linea->item->get_value_to_show();
                    
                    $referencia = '';
                    if($linea->referencia != '')
                    {
                        $referencia = ' - ' . $linea->referencia;
                    }

                    $descripcion_item .= $referencia;
                ?>
                <tr>
                    <td class="text-center"> {{ $linea->producto_id }} </td>
                    <td> {{ $descripcion_item }} </td>
                    <td> {{ $linea->bodega_descripcion }} </td>
                    <td> {{ $linea->inv_motivo_id }} -  {{ $linea->motivo_descripcion }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->costo_unitario, 2, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->item->get_unidad_medida1() }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->costo_total, 2, ',', '.') }} </td>
                    <td>
                        @if( !in_array( $doc_encabezado->estado, ['Anulado', 'Facturada'] ) && Input::get('id_transaccion') != 2 )
                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$linea->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
                        @endif
                    </td>
                </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $total_factura += $linea->costo_total;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">&nbsp;</td>
                <td style="text-align: center;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> $ {{ number_format($total_factura, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>
</div>