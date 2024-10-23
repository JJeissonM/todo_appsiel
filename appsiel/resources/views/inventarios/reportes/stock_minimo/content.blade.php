
<h3> Reporte de Stock Mínimo de existencias </h3>
<div style="font-size: 15px;">
    <br><br>
    <b>Bodega:</b> {{ $bodega }}
    <br>
    <b>Fecha:</b> {{ $fecha_corte }}
</div>

<div class="table-responsive">
    <table id="myTable" class="table table-striped table-bordered tabla_pdf">
        <thead>
            <tr>
                <th>Cód.</th>
                <th>Producto</th>
                <th>Bodega</th>
                <th>Stock Mínimo</th>
                <th>Stock Actual</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach( $productos as $producto )
                <?php

                    $porcentaje_tolerancia = 20;
                    $cantidad_tolerancia = $producto->stock_minimo * ( 1 + $porcentaje_tolerancia/100 );
                    
                    $estado = "Óptimo";
                    $celda_clase = "success";

                    if ( $producto->cantidad <= $producto->stock_minimo) 
                    {
                        $estado = "Crítico";
                        $celda_clase = "danger";
                    }elseif ( $producto->cantidad <= $cantidad_tolerancia) 
                    {
                        $estado = "Alerta";
                        $celda_clase = "warning";
                    }

                    if ( $producto->cantidad > $cantidad_tolerancia) 
                    {
                    }

                ?>
                <tr class="{{$celda_clase}}">
                    <td>{{ $producto->item_id }}</td>
                    <td>{{ $producto->item_descripcion }} </td>
                    <td>{{ $producto->bodega_descripcion }} </td>
                    <td>{{ number_format($producto->stock_minimo, 0, ',','.') }}</td>
                    <td>{{ number_format($producto->cantidad, 0, ',','.') }}</td>
                    <td> {{ $estado }} </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>