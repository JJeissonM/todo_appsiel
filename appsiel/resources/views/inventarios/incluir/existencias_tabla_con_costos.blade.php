<style>
    .container_item {
        position: relative; /* Permite posicionar la imagen flotante de forma absoluta */
        display: inline-block; /* Asegura que el contenedor se ajuste al tamaño de la imagen */
    }

    .imagen-flotante img{
        max-width: 250px;
        max-height: 380px;
    }

    .imagen-flotante {
        position: absolute;
        top: 100%; /* Muestra la imagen flotante debajo de la base */
        left: 250px;
        display: none; /* Oculta la imagen por defecto */
        z-index: 1; /* Asegura que la imagen flotante esté por encima de otros elementos */
    }

    .container_item:hover .imagen-flotante {
        display: block; /* Muestra la imagen flotante al pasar el mouse sobre el contenedor */
    }
</style>
<div class="table-responsive">
    <table class="table table-bordered table-striped" style="font-size: 15px; border: 1px solid; border-collapse: collapse;" border="1" width="100%" id="myTable">

            <tr style="background: #ccc; font-weight: bold; text-align: center;">
                <td> Cód. </td>
                @if((int)config('inventarios.mostrar_columna_referencia_en_reportes') == 1)
                    <td> Ref. </td>
                @endif
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
                        @if((int)config('inventarios.mostrar_columna_referencia_en_reportes') == 1)
                            <td> {{ $productos[$i]['referencia'] }} </td>
                        @endif
    	                <td>
                            @if( $productos[$i]['url_imagen'] != '')
                                    <div class="container_item">
                                        {{ $productos[$i]['descripcion'] }} <i class="fa fa-image"></i>
                                        <div class="imagen-flotante">
                                            <img src="{{ $productos[$i]['url_imagen'] }}" alt="Imagen flotante">
                                        </div>
                                    </div>
                            @else
                                {{ $productos[$i]['descripcion'] }}
                            @endif
                            
                        </td>
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
                    $total_costo_total+= $productos[$i]['Cantidad'] * $productos[$i]['CostoPromedio'];
                }
            } ?>
            <tr>
                @if((int)config('inventarios.mostrar_columna_referencia_en_reportes') == 1)
                    <td colspan="4"> &nbsp; </td>
                @else
                    <td colspan="3"> &nbsp; </td>
                @endif  
                <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td> &nbsp; </td>
                <td> {{ number_format($total_costo_total, 2, ',', '.') }} </td>
            </tr>
    </table>
</div>