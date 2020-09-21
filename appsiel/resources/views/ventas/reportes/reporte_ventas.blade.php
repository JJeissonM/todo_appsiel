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
            $primer_encabezado = 'Tasas de impuesto (%)';
            break;
        case 'clase_cliente_id':
            $primer_encabezado = 'Clases de clientes';
            break;
        case 'core_tipo_transaccion_id':
            $primer_encabezado = 'Tipos de transacciones';
            break;
        case 'forma_pago':
            $primer_encabezado = 'Forma de pago';
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
        @foreach( $movimiento as $campo_agrupado => $coleccion_movimiento)

            <?php 
                $cantidad = $coleccion_movimiento->sum('cantidad');
                $precio_total = $coleccion_movimiento->sum('precio_total');
                $base_impuesto_total = $coleccion_movimiento->sum('base_impuesto_total');
            ?>

            <tr class="fila-{{$j}}">
                <td> {{ $campo_agrupado }} </td>
                <td> {{ number_format( $cantidad, 2, ',', '.') }} </td>
                @php

                    if ( $iva_incluido )
                    {
                        $precio = $precio_total;
                    }else{
                        $precio = $base_impuesto_total;
                    }

                    $precio_promedio = 0; 
                    if( $cantidad != 0 )
                    { 
                        $precio_promedio = $precio / $cantidad; 
                    } 
                @endphp
                <td> ${{ number_format( $precio_promedio, 2, ',', '.') }} </td>
                <td> ${{ number_format( $precio, 2, ',', '.') }} </td>
            </tr>

         <?php
            

            $total_1_producto += $cantidad;
            $total_2_producto += $precio;

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