<h3 style="width: 100%; text-align: center;">
    REPORTE DE VENTAS 
    <span style="background: yellow; color: red;">({!! $mensaje !!})</span> 
</h3>
<hr>

<?php 
    switch ( $agrupar_por )
    {
        case 'cliente_id':
            $primer_encabezado = 'Clientes';
            break;
        case 'inv_producto_id':
            $primer_encabezado = 'Productos';
            break;
        case 'tasa_impuesto':
            $primer_encabezado = 'Tasas de impuesto';
            break;
        case 'clase_cliente_id':
            $primer_encabezado = 'Clases de clientes';
            break;
        case 'core_tipo_transaccion_id':
            $primer_encabezado = 'Tipos de transacciones';
            break;
        
        default:
            $primer_encabezado = '';
            break;
    }
?>

<table id="myTable" class="table table-striped">
    <thead>
        <tr>
            <th> {{ $primer_encabezado }} </th>
            <th> Cantidad total </th>
            <th> Precio promedio </th>
            <th> Venta total </th>
        </tr>
    </thead>
    <tbody>
        <?php
            $j = 1;
            $total_1_producto = 0;
            $total_2_producto = 0;
        ?>
        @foreach( $movimiento as $linea)

            <?php 
                switch ( $agrupar_por )
                {
                    case 'cliente_id':
                        $texto_celda = $linea->cliente;
                        break;
                    case 'inv_producto_id':
                        $texto_celda = $linea->producto;
                        break;
                    case 'tasa_impuesto':
                        $texto_celda = $linea->tasa_impuesto . '%';
                        break;
                    case 'clase_cliente_id':
                        $texto_celda = $linea->clase_cliente;
                        break;
                    case 'core_tipo_transaccion_id':
                        $texto_celda = $linea->descripcion_tipo_transaccion;
                        break;
                    
                    default:
                        $texto_celda = '';
                        break;
                }
            ?>

            <tr class="fila-{{$j}}">
                <td> {{ $texto_celda }} </td>
                <td> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                @php $precio_promedio = 0; if( $linea->cantidad != 0 ){ $precio_promedio = $linea->precio_total / $linea->cantidad; } @endphp
                <td> ${{ number_format( $precio_promedio, 2, ',', '.') }} </td>
                <td> ${{ number_format( $linea->precio_total, 2, ',', '.') }} </td>
            </tr>

         <?php
            

            $total_1_producto += $linea->cantidad;
            $total_2_producto += $linea->precio_total;

            $j++;
            if ($j==3) {
                $j=1;
            }
        ?>
        @endforeach

        <tr style=" background-color: #67cefb; font-weight: bolder;">
            <td> </td>
            <td> {{ number_format( $total_1_producto, 2, ',', '.') }} </td>
            @php $total_precio_promedio = 0; if( $total_1_producto != 0 ){ $total_precio_promedio = $total_2_producto / $total_1_producto; } @endphp
            <td> ${{ number_format( $total_precio_promedio, 2, ',', '.') }} </td>
            <td> ${{ number_format( $total_2_producto, 2, ',', '.') }} </td>
        </tr>
    </tbody>
</table>