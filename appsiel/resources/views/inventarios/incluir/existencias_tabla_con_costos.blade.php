<div class="table-responsive">
    <table class="table table-bordered table-striped" style="font-size: 15px; border: 1px solid; border-collapse: collapse;" border="1" width="100%" id="myTable">

            <tr style="background: #ccc; font-weight: bold; text-align: center;">
                <td> Cód. </td>
                <td> Producto </td>
                <td> Bodega </td>
                <td> Cantidad </td>
                <td> Costo Prom. </td>
                <td> Costo Total </td>
            </tr>

            <?php 
            $total_cantidad=0;
            $total_costo_total=0;
            for($i=0;$i<count($productos);$i++){

                    $cantidad = round($productos[$i]['Cantidad'],2);
                    
                    if (!isset($productos[$i]['CostoPromedio'])) {
                        continue;
                    }

                ?>
                @if( $productos[$i]['id'] != 0 )
    	            <tr>
    	                <td>{{ $productos[$i]['id'] }}</td>
    	                <td>{{ $productos[$i]['descripcion'] }}</td>
                        <td> {{ $productos[$i]['bodega'] }} </td>
    	                <td>{{ number_format($productos[$i]['Cantidad'], 2, ',', '.') }} </td>
                        <td>${{ number_format( $productos[$i]['CostoPromedio'], 2, ',', '.') }}</td>
                        <td>${{ number_format( $productos[$i]['Cantidad'] * $productos[$i]['CostoPromedio'], 2, ',', '.') }}</td>
    	            </tr>
                @else
                    @if($productos[$i]['Cantidad'] != 0 && $bodega == 'VARIAS')
                        <tr style="background: #4a4a4a; color: white;">
                            <td colspan="3"> &nbsp; </td>
                            <td>{{ number_format($productos[$i]['Cantidad'], 2, ',', '.') }} </td>
                            <td>${{ number_format( $productos[$i]['CostoPromedio'], 2, ',', '.') }}</td>
                            <td>${{ number_format( $productos[$i]['Cantidad'] * $productos[$i]['CostoPromedio'], 2, ',', '.') }}</td>
                        </tr>
                    @endif                    
                @endif
            <?php 
                if( $productos[$i]['id'] != 0 )
                {
                    $total_cantidad+= $productos[$i]['Cantidad'];
                    $total_costo_total+= $productos[$i]['Costo'];
                }
            } ?>
            <tr>
                <td colspan="3"> &nbsp; </td>
                <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td> &nbsp; </td>
                <td> {{ number_format($total_costo_total, 2, ',', '.') }} </td>
            </tr>
    </table>
</div>