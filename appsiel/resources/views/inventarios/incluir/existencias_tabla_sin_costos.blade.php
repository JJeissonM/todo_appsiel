<table style="font-size: 15px; border: 1px solid; border-collapse: collapse;" border="1" width="100%">

        <tr style="background: #ccc; font-weight: bold; text-align: center;">
            @if( $bodega == 'TODAS')
                <td> Bodega </td>
            @endif
            <td> Cód. </td>
            <td> Producto </td>
            <td> Cantidad física </td>
            @if($mostrar_cantidad)
                <td> Cantidad Sistema </td>
                <td> Dif. </td>
            @endif
        </tr>

        <?php 
        $total_cantidad=0;
        $total_costo_total=0;
        for($i=0;$i<count($productos);$i++){ ?>
        	<!-- @ if($productos[$i]['Cantidad']!=0) -->
	            <tr>
                    @if( $bodega == 'TODAS')
                        <td> {{ $productos[$i]['bodega'] }} </td>
                    @endif
	                <td>{{ $productos[$i]['id'] }}</td>
	                <td>{{ $productos[$i]['descripcion'] }} ({{ $productos[$i]['unidad_medida1'] }})</td>

                    <td> &nbsp; </td>

                    @if($mostrar_cantidad)
                        <td>{{ number_format($productos[$i]['Cantidad'], 2, ',', '.') }}</td>
                        <td> &nbsp; </td>
                    @endif
	                
	            </tr>
            <!-- @ endif -->
        <?php 
            $total_cantidad+= $productos[$i]['Cantidad'];
            $total_costo_total+= $productos[$i]['Costo'];
        } ?>
        <tr>
            @if( $bodega == 'TODAS')
                <td colspan="3"> &nbsp; </td>
            @else
                <td colspan="2"> &nbsp; </td>
            @endif
            
            <td> &nbsp; </td>

            @if($mostrar_cantidad)
                <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td> &nbsp; </td>
            @endif
        </tr>
</table>