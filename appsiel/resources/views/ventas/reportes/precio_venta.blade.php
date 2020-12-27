<h3 style="width: 100%; text-align: center;"> Estadísticas de precios de ventas (Sin IVA) </h3>
<hr>
<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th> Producto </th>
                <th> Cliente </th>
                <th> Cantidad total </th>
                <th> Precio promedio </th>
                <th> Precio total </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
                $total_1_producto = 0;
                $total_2_producto = 0;

                $producto_anterior_id = 0;
                $primera_linea = true;
            ?>
            @foreach( $movimiento as $linea)
                <?php
                    $nombre_producto = $linea->producto;
                    $nombre_cliente = $linea->cliente;
                ?>

                @if( !$primera_linea )
                    <!-- Se verifica si se cambió de producto y se agrega la línea de totales del producto anterior -->
                    @if( $linea->inv_producto_id != $producto_anterior_id )
                        <tr style=" background-color: #67cefb; font-weight: bolder;">
                            <td> </td>
                            <td> </td>
                            <td> {{ number_format( $total_1_producto, 2, ',', '.') }} </td>
                            @php $total_precio_promedio = 0; if( $total_1_producto != 0 ){ $total_precio_promedio = $total_2_producto / $total_1_producto; } @endphp
                            <td> ${{ number_format( $total_precio_promedio, 2, ',', '.') }} </td>
                            <td> ${{ number_format( $total_2_producto, 2, ',', '.') }} </td>
                        </tr>
                        <?php
                            $j = 1;
                            $total_1_producto = 0;
                            $total_2_producto = 0;
                        ?>
                    @else
                        <?php
                            $nombre_producto = '';
                        ?>
                    @endif
                @endif

                <tr class="fila-{{$j}}">
                    <td> {{ $nombre_producto }} </td>
                    <td> {{ $nombre_cliente }} </td>
                    <td> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                    @php $precio_promedio = 0; if( $linea->cantidad != 0 ){ $precio_promedio = $linea->base_impuesto_total / $linea->cantidad; } @endphp
                    <td> ${{ number_format( $precio_promedio, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $linea->base_impuesto_total, 2, ',', '.') }} </td>
                </tr>

             <?php
                
                $producto_anterior_id = $linea->inv_producto_id;
                $primera_linea = false;

                $total_1_producto += $linea->cantidad;
                $total_2_producto += $linea->base_impuesto_total;

                $j++;
                if ($j==3) {
                    $j=1;
                }
            ?>
            @endforeach

            <tr style=" background-color: #67cefb; font-weight: bolder;">
                <td> </td>
                <td> </td>
                <td> {{ number_format( $total_1_producto, 2, ',', '.') }} </td>
                @php $total_precio_promedio = 0; if( $total_1_producto != 0 ){ $total_precio_promedio = $total_2_producto / $total_1_producto; } @endphp
                <td> ${{ number_format( $total_precio_promedio, 2, ',', '.') }} </td>
                <td> ${{ number_format( $total_2_producto, 2, ',', '.') }} </td>
            </tr>
        </tbody>
    </table>
</div>