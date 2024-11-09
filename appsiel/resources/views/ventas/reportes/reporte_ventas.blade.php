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

<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th> {{ $primer_encabezado }} </th>
                <th> Cantidad total </th>
                <th> Precio promedio </th>
                <th> Venta total <i class="fa fa-sort-amount-desc"></i></th>
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

                    $label = $campo_agrupado;
                    if ($agrupar_por == 'inv_producto_id') {
                        $label = $coleccion_movimiento->first()->item->get_value_to_show();
                    }
                ?>

                <tr class="fila-{{$j}}">
                    <td> {{ $label }} </td>
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

                @if($detalla_productos)
                    <?php 
                        $items = $coleccion_movimiento->groupBy('inv_producto_id');
                    ?>
                    <tr>
                        <td colspan="4">
                            <table class="table table-bordered">
                                @foreach( $items AS $item )
                                    <tr>
                                        <td>
                                            {{ $item->first()->producto }}
                                        </td>
                                        <?php 
                                            $cantidad_item = $item->sum('cantidad');
                                        ?>
                                        <td>
                                            {{ number_format( $cantidad_item, 2, ',', '.') }}
                                        </td>
                                        @php
                                            if ( $iva_incluido )
                                            {
                                                $precio_item = $item->sum('precio_total');
                                            }else{
                                                $precio_item = $item->sum('base_impuesto_total');
                                            }

                                            $precio_promedio_item = 0; 
                                            if( $cantidad_item != 0 )
                                            { 
                                                $precio_promedio_item = $precio_item / $cantidad_item; 
                                            } 
                                        @endphp
                                        <td> ${{ number_format( $precio_promedio_item, 2, ',', '.') }} </td>
                                        <td> ${{ number_format( $precio_item, 2, ',', '.') }} </td>
                                    </tr>         
                                @endforeach
                            </table>
                        </td>
                    </tr>
                @endif

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
</div>