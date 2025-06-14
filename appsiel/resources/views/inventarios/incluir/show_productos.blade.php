<div class="table-responsive">
    <table class="table table-bordered tabla_pdf">
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Producto</th>
                <th>Bodega</th>
                <th>Costo Unit.</th>
                <th>Cantidad</th>
                <th>Costo Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $total_cantidad=0;
                $total_costo_total=0;
            ?>
            @foreach($productos as $fila)
                <tr>
                    <td class="text-center">{{ $fila->producto_id }}</td>
                    <td>{{ $fila->producto_descripcion }}</td>
                    <td>{{ $fila->bodega_descripcion }}</td>
                    <td class="text-right">{{ '$'.number_format($fila->costo_unitario, 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($fila->cantidad, 2, ',', '.') }} {{ $fila->get_unidad_medida1() }}</td>
                    <td class="text-right">{{ '$'.number_format($fila->costo_total, 2, ',', '.') }}</td>
                </tr>
            <?php
                $total_cantidad+= $fila->cantidad;
                $total_costo_total+= $fila->costo_total;
            ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">&nbsp;</td>
                <td class="text-center"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td class="text-right"> {{ '$'.number_format($total_costo_total, 2, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>