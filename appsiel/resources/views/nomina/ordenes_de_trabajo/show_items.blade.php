<a class="btn btn-primary" href="{{ url('inv_crear_documento_desde_orden_trabajo/create?&id='.Input::get('id').'&id_modelo=167&id_transaccion=38') }}" title="Crear documento de inventario"><i class="fa fa-file-text"></i></a>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','Cantidad','Costo unitario','Costo Total']) }}
        <tbody>
            <?php
                $total_cantidad = 0;
                $total_factura = 0;
                $items = $orden_de_trabajo->items;
            ?>
            @foreach( $items as $item_orden_trabajo )
                <tr>
                    <td class="text-center"> {{ $item_orden_trabajo->item->id }} </td>
                    <td> {{ $item_orden_trabajo->item->descripcion }} ({{ $item_orden_trabajo->item->unidad_medida1 }}) </td>
                    <td align="center">
                        <div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('nom_ordenes_trabajo_cambiar_cantidad_items') . "/" . $orden_de_trabajo->id . "/" . $item_orden_trabajo->item->id }}"> {{ $item_orden_trabajo->cantidad }} 
                        </div>
                    </td>
                    <td style="text-align: right;"> $ {{ number_format( $item_orden_trabajo->costo_unitario, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $item_orden_trabajo->costo_total, 2, ',', '.') }} </td>
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
                <td align="center"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td style="text-align: right;"> $ {{ number_format($total_factura, 2, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>