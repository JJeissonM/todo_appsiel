<div class="table-responsive">
    <table class="table table-bordered table-striped" style="font-size: 15px; border: 1px solid; border-collapse: collapse;" border="1" width="100%">
        <tr style="background: #ccc; font-weight: bold; text-align: center;">
            <td> Cód. </td>
            @if((int)config('inventarios.mostrar_columna_referencia_en_reportes') == 1)
                <td> Ref. </td>
            @endif
            <td> Producto </td>
            <td> Bodega </td>
            <td> Cantidad física </td>
            @if($mostrar_cantidad)
                <td> Cantidad Sistema </td>
                <td> Dif. </td>
            @endif
        </tr>

        <?php 
            $total_cantidad=0;
            $total_costo_total=0;
        ?>
        @for($i=0;$i<count($productos);$i++)
            @if( $productos[$i]['id'] != 0 )
	            <tr>
                    <td>{{ $productos[$i]['id'] }}</td>
                    @if((int)config('inventarios.mostrar_columna_referencia_en_reportes') == 1)
                        <td> {{ $productos[$i]['referencia'] }} </td>
                    @endif
	                <td>{{ $productos[$i]['descripcion'] }}</td>
                    <td> {{ $productos[$i]['bodega'] }} </td>

                    <td> &nbsp; </td>

                    @if($mostrar_cantidad)
                        <td>{{ number_format($productos[$i]['Cantidad'], 2, ',', '.') }}</td>
                        <td> &nbsp; </td>
                    @endif
	                
	            </tr>
                <?php 
                    $total_cantidad+= $productos[$i]['Cantidad'];
                    $total_costo_total+= $productos[$i]['Costo'];
                ?>
            @else
                @if($mostrar_cantidad)
                    <tr style="background: #4a4a4a; color: white;">
                        @if((int)config('inventarios.mostrar_columna_referencia_en_reportes') == 1)
                            <td colspan="4"> &nbsp; </td>
                        @else
                            <td colspan="3"> &nbsp; </td>
                        @endif  

                        <td> &nbsp; </td>

                        <td>{{ number_format($productos[$i]['Cantidad'], 2, ',', '.') }}</td>
                        <td> &nbsp; </td>
                    </tr>
                @endif
            @endif
        @endfor
        <tr> 
            
            @if((int)config('inventarios.mostrar_columna_referencia_en_reportes') == 1)
                <td colspan="4"> &nbsp; </td>
            @else
                <td colspan="3"> &nbsp; </td>
            @endif      
            
            <td> &nbsp; </td>

            @if($mostrar_cantidad)
                <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td> &nbsp; </td>
            @endif
        </tr>
    </table>
</div>