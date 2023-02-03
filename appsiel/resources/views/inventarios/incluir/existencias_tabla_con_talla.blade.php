<div class="table-responsive">
    <table style="font-size: 15px; border: 1px solid; border-collapse: collapse;" border="1" width="100%" id="myTable">

            <tr style="background: #ccc; font-weight: bold; text-align: center;">
                <td> Bodega </td>
                <td> Cód. </td>
                <td> Producto </td>
                <td> Talla </td>
                <td> Cant. </td>
                <td> Costo Prom. </td>
                <td> Costo Total </td>
            </tr>

            <?php 
            $total_cantidad=0;
            $total_costo_total=0;

            foreach ($movimientos as $linea_movimiento) {

                    $linea_movimiento->suma_cantidad = round($linea_movimiento->suma_cantidad,2);

                    $costo_promedio = 0;

                    if( $linea_movimiento->suma_cantidad != 0)
                    {
                        $costo_promedio = $linea_movimiento->suma_costo / $linea_movimiento->suma_cantidad;
                    }else{
                        $linea_movimiento->suma_costo = 0;
                    }

                    $diferencia_costo_prom = 0;//$linea_movimiento->costo_promedio_ponderado -  $costo_promedio;

                    $alerta = '';
                    if ( 10 <= $diferencia_costo_prom || $diferencia_costo_prom <= -10 )
                    {
                        $alerta = '<i class="fa fa-warning" title="Direfencia de $'.number_format( $diferencia_costo_prom, 2, ',', '.').'"></i>';
                    }
                ?>
                <!-- @ if($linea_movimiento->suma_cantidad!=0) -->
                    <tr>
                        <td> {{ $linea_movimiento->bodega->descripcion }} </td>
                        <td>{{ $linea_movimiento->id }}</td>
                        <td>{{ $linea_movimiento->producto->descripcion }} </td>
                        <td>{{ $linea_movimiento->producto->unidad_medida2 }} </td> <!-- Talla -->
                        <td>{{ number_format($linea_movimiento->suma_cantidad, 2, ',', '.') }} </td>
                        <td>{{ '$'.number_format( $costo_promedio, 2, ',', '.') }}</td>
                        <td>{{ '$'.number_format( $linea_movimiento->suma_costo, 2, ',', '.') }}</td>
                    </tr>
                <!-- @ endif -->
            <?php 
                $total_cantidad+= $linea_movimiento->suma_cantidad;
                $total_costo_total+= $linea_movimiento->suma_costo;
            } 
            ?>
            <tr>
                <td colspan="4"> &nbsp; </td>            
                <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td> &nbsp; </td>
                <td> {{ number_format($total_costo_total, 2, ',', '.') }} </td>
            </tr>
    </table>
</div>
    