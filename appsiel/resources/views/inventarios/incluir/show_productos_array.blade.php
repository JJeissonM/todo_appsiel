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
        $cantidad = count($productos);
        for($i=0; $i < $cantidad; $i++)
        { ?>
            <tr>
                <td>{{ $productos[$i]['producto']->id}}</td>
                <td>{{ $productos[$i]['producto']->descripcion}}</td>
                <td>{{ $productos[$i]['bodega']}}</td>
                <td>{{ '$'.number_format($productos[$i]['costo_unitario'], 2, ',', '.') }}</td>
                <td>{{ number_format($productos[$i]['cantidad'], 2, ',', '.') }} {{ $productos[$i]['producto']->unidad_medida1 }}</td>
                <td>{{ '$'.number_format($productos[$i]['costo_total'], 2, ',', '.') }}</td>
            </tr>
        <?php 
            $total_cantidad+= $productos[$i]['cantidad'];
            $total_costo_total+= $productos[$i]['costo_total'];
        } ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">&nbsp;</td>
            <td> {{ number_format($total_cantidad, 0, ',', '.') }} </td>
            <td> {{ '$'.number_format($total_costo_total, 0, ',', '.') }} </td>
        </tr>
    </tfoot>
</table>