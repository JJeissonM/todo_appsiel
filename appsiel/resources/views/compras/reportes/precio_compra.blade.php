<h3 style="width: 100%; text-align: center;"> 
    Estadísticas de precios de compras 
    <span style="background: yellow; color: red;">({!! $mensaje !!})</span> 
</h3>
<hr>

<?php
    if ( $porcentaje_proyeccion_1 != 0 )
    {
        $display_porcentaje_1 = 'cell';
    }else{
        $display_porcentaje_1 = 'none';
    } 



    if ( $porcentaje_proyeccion_2 != 0 )
    {
        $display_porcentaje_2 = 'cell';
    }else{
        $display_porcentaje_2 = 'none';
    } 



    if ( $porcentaje_proyeccion_3 != 0 )
    {
        $display_porcentaje_3 = 'cell';
    }else{
        $display_porcentaje_3 = 'none';
    } 



    if ( $porcentaje_proyeccion_4 != 0 )
    {
        $display_porcentaje_4 = 'cell';
    }else{
        $display_porcentaje_4 = 'none';
    }    
?>

<div class="table-responsive">
    <table id="tbDatos" class="table table-striped">
        <thead>
            <tr>
                <th> Producto </th>
                <th> Proveedor </th>
                <th> Cantidad total </th>
                <th> Precio promedio </th>
                <th> Precio total </th>
                <th style="display: {{ $display_porcentaje_1 }}"> + {{ number_format( $porcentaje_proyeccion_1, 0, ',', '.') }}% </th>
                <th style="display: {{ $display_porcentaje_2 }}"> + {{ number_format( $porcentaje_proyeccion_2, 0, ',', '.') }}% </th>
                <th style="display: {{ $display_porcentaje_3 }}"> + {{ number_format( $porcentaje_proyeccion_3, 0, ',', '.') }}% </th>
                <th style="display: {{ $display_porcentaje_4 }}"> + {{ number_format( $porcentaje_proyeccion_4, 0, ',', '.') }}% </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
                $total_1_producto = 0;
                $total_2_producto = 0;
                $gran_total_1_producto = 0;
                $gran_total_2_producto = 0;

                $producto_anterior_id = 0;
                $producto_anterior_descripcion = '';
                $primera_linea_producto = true;

                if( $detalla_proveedores )
                {
                    $estilo_linea_subtotal = 'background-color: #67cefb; font-weight: bolder;';
                }else{
                    $estilo_linea_subtotal = '';
                }

            ?>

            @foreach( $movimiento as $linea)
                <?php

                    if ( $iva_incluido )
                    {
                        $precio = $linea->precio_total;
                    }else{
                        $precio = $linea->base_impuesto;
                    }

                    $nombre_producto = $linea->producto;
                    $nombre_proveedor = $linea->proveedor;
                ?>

                @if( $detalla_proveedores ) 
                    
                    @if( $primera_linea_producto ) <!-- Se dibujan dos filas -->
                        
                        {!! dibujar_primera_fila( $estilo_linea_subtotal, $nombre_producto, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4 ) !!}

                        {!! dibujar_fila_proveedor( $nombre_proveedor, $linea, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4, $iva_incluido ) !!}
                    @endif

                    <!-- Filas de proveedor para el mismo producto después de la primera fila -->
                    @if( $linea->inv_producto_id == $producto_anterior_id && !$primera_linea_producto )
                        {!! dibujar_fila_proveedor( $nombre_proveedor, $linea, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4, $iva_incluido ) !!}
                    @endif

                @endif

                @if( !$primera_linea_producto )
                    
                    <!-- Cuando cambia el producto, se dibuja la línea de totales del producto anterior. -->
                    @if( $linea->inv_producto_id != $producto_anterior_id )
                        
                        {!! dibujar_fila_totales_producto_anterior( $detalla_proveedores, $producto_anterior_descripcion, $total_1_producto, $total_2_producto, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4, $porcentaje_proyeccion_1, $porcentaje_proyeccion_2, $porcentaje_proyeccion_3, $porcentaje_proyeccion_4 ) !!}

                        <?php
                            $j = 1;
                            $total_1_producto = 0;
                            $total_2_producto = 0;
                            $primera_linea_producto = true;
                        ?>

                        @if( $detalla_proveedores )
                    
                            @if( $primera_linea_producto )
                                {!! dibujar_primera_fila( $estilo_linea_subtotal, $nombre_producto, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4 ) !!}

                                {!! dibujar_fila_proveedor( $nombre_proveedor, $linea, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4, $iva_incluido ) !!}
                            @endif

                        @endif
                    @endif

                @endif


                <?php

                    $total_1_producto += $linea->cantidad;
                    $total_2_producto += $precio;

                    $gran_total_1_producto += $linea->cantidad;
                    $gran_total_2_producto += $precio;
                    
                    if( $primera_linea_producto )
                    {
                        $primera_linea_producto = false;
                    }/**/

                    $producto_anterior_id = $linea->inv_producto_id;
                    $producto_anterior_descripcion = $linea->producto;

                    $j++;
                    if ($j==3) {
                        $j=1;
                    }
                ?>
            @endforeach

            <!-- Mostrar subtotales del último producto -->
            {!! dibujar_fila_totales_producto_anterior( $detalla_proveedores, $producto_anterior_descripcion, $total_1_producto, $total_2_producto, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4, $porcentaje_proyeccion_1, $porcentaje_proyeccion_2, $porcentaje_proyeccion_3, $porcentaje_proyeccion_4 ) !!}

        </tbody>

        <tfoot>
            <tr style="background: #ddd; font-weight: bolder;">
                    <td colspan="2"> TOTAL </td>
                    <td> {{ number_format( $gran_total_1_producto, 2, ',', '.') }} </td>
                    @php $total_precio_promedio = 0; if( $gran_total_1_producto != 0 ){ $total_precio_promedio = $gran_total_2_producto / $gran_total_1_producto; } @endphp
                    <td> ${{ number_format( $total_precio_promedio, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $gran_total_2_producto, 2, ',', '.') }} </td>
                    <td style="display: {{ $display_porcentaje_1 }}"> &nbsp; </td>
                    <td style="display: {{ $display_porcentaje_2 }}"> &nbsp; </td>
                    <td style="display: {{ $display_porcentaje_3 }}"> &nbsp; </td>
                    <td style="display: {{ $display_porcentaje_4 }}"> &nbsp; </td>
                </tr>
        </tfoot>

    </table>
</div>


<?php 

    function dibujar_primera_fila( $estilo_linea_subtotal, $nombre_producto, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4 )
    {
        // La primera línea solo el nombre del producto, solo en la primera iteración del producto
        return '<tr style="'.$estilo_linea_subtotal.'">
                        <td> <b> '.$nombre_producto.' </b> </td>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        <td> </td>
                        <td style="display: '.$display_porcentaje_1.'"> &nbsp; </td>
                        <td style="display: '.$display_porcentaje_2.'"> &nbsp; </td>
                        <td style="display: '.$display_porcentaje_3.'"> &nbsp; </td>
                        <td style="display: '.$display_porcentaje_4.'"> &nbsp; </td>
                    </tr>';
    }



    function dibujar_fila_proveedor( $nombre_proveedor, $linea, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4, $iva_incluido )
    {
        if ( $iva_incluido )
        {
            $precio = $linea->precio_total;
        }else{
            $precio = $linea->base_impuesto;
        }
                
        $html_linea = '';
        // Línea por cada proveedor
        $html_linea .= '<tr>
                    <td>  </td>
                    <td> '.$nombre_proveedor.' </td>
                    <td> '.number_format( $linea->cantidad, 2, ',', '.').' </td>';
                    
        $precio_promedio = 0; 
        if( $linea->cantidad != 0 )
        {
            $precio_promedio = $precio / $linea->cantidad;
        }

        $html_linea .= '<td> $'.number_format( $precio_promedio, 2, ',', '.').' </td>
                            <td> $'.number_format( $precio, 2, ',', '.').' </td>
                            <td style="display: '.$display_porcentaje_1.'"> &nbsp; </td>
                            <td style="display: '.$display_porcentaje_2.'"> &nbsp; </td>
                            <td style="display: '.$display_porcentaje_3.'"> &nbsp; </td>
                            <td style="display: '.$display_porcentaje_4.'"> &nbsp; </td>
                        </tr>';

        return $html_linea;
    }


    function get_valor_proyeccion( $total_precio_promedio, $porcentaje_proyeccion )
    {
        $proy = $total_precio_promedio + $total_precio_promedio * $porcentaje_proyeccion / 100;
        return '$'.number_format( $proy, 2, ',', '.');
    }


    function dibujar_fila_totales_producto_anterior( $detalla_proveedores, $producto_anterior_descripcion, $total_1_producto, $total_2_producto, $display_porcentaje_1, $display_porcentaje_2, $display_porcentaje_3, $display_porcentaje_4, $porcentaje_proyeccion_1, $porcentaje_proyeccion_2, $porcentaje_proyeccion_3, $porcentaje_proyeccion_4 )
    {
        $html_linea = '';

        if( $detalla_proveedores )
        {
            $producto_anterior_descripcion = 'Subtotal';
            $estilo_negrita = 'bold';
        }else{
            $estilo_negrita = 'normal';
        }
        
        $html_linea .= '<tr style="font-weight: '.$estilo_negrita .'">
                        <td> '. $producto_anterior_descripcion .' </td>
                        <td> </td>
                        <td> '. number_format( $total_1_producto, 2, ',', '.') .' </td>';

        $total_precio_promedio = 0;
        if( $total_1_producto != 0 )
        {
            $total_precio_promedio = $total_2_producto / $total_1_producto;
        }


        $html_linea .= '<td> $'. number_format( $total_precio_promedio, 2, ',', '.') .' </td>
                        <td> $'. number_format( $total_2_producto, 2, ',', '.') .' </td>
                        <td style="display: '. $display_porcentaje_1 .'">'. get_valor_proyeccion($total_precio_promedio, $porcentaje_proyeccion_1 ) .' </td>
                        <td style="display: '. $display_porcentaje_2 .'">'. get_valor_proyeccion($total_precio_promedio, $porcentaje_proyeccion_2 ) .'  </td>
                        <td style="display: '. $display_porcentaje_3 .'">'. get_valor_proyeccion($total_precio_promedio, $porcentaje_proyeccion_3 ) .'  </td>
                        <td style="display: '. $display_porcentaje_4 .'"> '.get_valor_proyeccion($total_precio_promedio, $porcentaje_proyeccion_4 ) .'  </td>
                    </tr>';

        return $html_linea;
    }
?>