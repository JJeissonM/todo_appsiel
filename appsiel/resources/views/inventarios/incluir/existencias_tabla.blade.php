<?php 
    $referencia = '';
    dd();
?>
<div class="table-responsive">
    <table class="table table-bordered">
        {{ Form::bsTableHeader(['Cód.','Producto','Cantidad','Costo Prom.','Costo Total']) }}
        <tbody>
            <?php 
            $total_cantidad=0;
            $total_costo_total=0;
            for($i=0;$i<count($productos);$i++){
                    $costo_promedio = 0;

                    if( $productos[$i]['Cantidad'] != 0)
                    {
                        $costo_promedio = $productos[$i]['Costo'] / $productos[$i]['Cantidad'];
                    }

                ?>
            	<!-- @ if($productos[$i]['Cantidad']!=0) -->
    	            <tr>
    	                <td class="text-center">{{ $productos[$i]['id'] }}</td>
    	                <td>{{ $productos[$i]['descripcion'] }} {{ $productos[$i]['unidad_medida1'] }} </td>
    	                <td class="text-center">{{ number_format($productos[$i]['Cantidad'], 0, ',', '.') }}</td>
    	                <td class="text-right">{{ '$'.number_format($productos[$i]['Costo']/$productos[$i]['Cantidad'], 0, ',', '.') }}</td>
    	                <td class="text-right">{{ '$'.number_format($productos[$i]['Costo'], 0, ',', '.') }}</td>
    	            </tr>
                <!-- @ endif -->
            <?php 
                $total_cantidad+= $productos[$i]['Cantidad'];
                $total_costo_total+= $productos[$i]['Costo'];
            } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">&nbsp;</td>
                <td class="text-center"> {{ number_format($total_cantidad, 0, ',', '.') }} </td>
                <td>&nbsp;</td>
                <td class="text-right"> {{ '$'.number_format($total_costo_total, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>