
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','Cantidad','Costo unitario','Costo Total','']) }}
        <tbody>
            <?php
                $total_cantidad = 0;
                $total_factura = 0;
                $items = $orden_de_trabajo->items;
            ?>
            @foreach( $items as $item_orden_trabajo )
                <tr>
                    <td> {{ $item_orden_trabajo->item->id }} </td>
                    <td> {{ $item_orden_trabajo->item->descripcion }} </td>
                    <td> {{ number_format( $item_orden_trabajo->cantidad, 2, ',', '.') }} {{ $item_orden_trabajo->item->unidad_medida1 }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $item_orden_trabajo->costo_unitario, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $item_orden_trabajo->costo_total, 2, ',', '.') }} </td>
                    <td>
                        @if( !in_array( $orden_de_trabajo->estado, ['Anulado', 'Facturada'] ) )
                            <button class="btn btn-warning btn-xs btn-detail btn_editar_registro" type="button" title="Modificar" data-linea_registro_id="{{$item_orden_trabajo->id}}"><i class="fa fa-btn fa-edit"></i>&nbsp; </button>

                            @include('components.design.ventana_modal',['titulo'=>'Editar registro','texto_mensaje'=>''])
                        @endif
                    </td>
                </tr>
                <?php 
                    $total_cantidad += $item_orden_trabajo->cantidad;
                    $total_factura += $item_orden_trabajo->costo_total;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td style="text-align: right;"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> $ {{ number_format($total_factura, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>
</div>