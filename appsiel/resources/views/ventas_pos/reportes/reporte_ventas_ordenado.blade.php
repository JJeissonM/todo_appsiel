<h3 style="width: 100%; text-align: center;">
    REPORTE DE VENTAS 
    <span style="background: yellow; color: red;">({!! $mensaje !!})</span> 
</h3>
<hr>

<?php 
    switch ( $agrupar_por )
    {
        case 'pdv_id':
            $primer_encabezado = 'Pto. de Ventas';
            break;
        case 'inv_grupo_id':
            $primer_encabezado = 'Cat. de productos';
            break;
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

                $columna_precio  = array_column($array_lista, 'precio');
                
                array_multisort($columna_precio, SORT_DESC, $array_lista);
                        
                $cantidad_elementos = count($columna_precio);

                $j = 1;
                
                $total_1_producto = 0;
                $total_2_producto = 0;
            ?>

            @for( $i = 0; $i < $cantidad_elementos; $i++)

                <tr class="fila-{{$j}}">
                    <td> {{ $array_lista[$i]['descripcion'] }} </td>
                    <td> {{ number_format( $array_lista[$i]['cantidad'], 2, ',', '.') }} </td>
                    <td> ${{ number_format( $array_lista[$i]['precio_promedio'], 2, ',', '.') }} </td>
                    <td> ${{ number_format( $array_lista[$i]['precio'], 2, ',', '.') }} </td>
                </tr>

                @if($detalla_productos)
                    <?php 
                        $array_detalle_productos = $array_lista[$i]['array_detalle_productos'];
                        $cantidad_productos_lista = count( $array_detalle_productos );
                    ?>
                    <tr>
                        <td colspan="4">
                            <table class="table table-bordered">
                                @for( $p = 0; $p < $cantidad_productos_lista; $p++ )
                                    <tr>
                                        <td>
                                            {{ $array_detalle_productos[$p]['descripcion'] }}
                                        </td>
                                        <td>
                                            {{ number_format( $array_detalle_productos[$p]['cantidad_item'], 2, ',', '.') }}
                                        </td>
                                        <td> ${{ number_format( $array_detalle_productos[$p]['precio_promedio_item'], 2, ',', '.') }} </td>
                                        <td> ${{ number_format( $array_detalle_productos[$p]['precio_item'], 2, ',', '.') }} </td>
                                    </tr>         
                                @endfor
                            </table>
                        </td>
                    </tr>
                @endif

                <?php
                    
                    $total_1_producto += $array_lista[$i]['cantidad'];
                    $total_2_producto += $array_lista[$i]['precio'];

                    $j++;
                    if ($j==3) {
                        $j=1;
                    }
                ?>
            @endfor

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