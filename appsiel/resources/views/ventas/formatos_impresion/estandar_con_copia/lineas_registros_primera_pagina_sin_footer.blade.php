<table class="table table-bordered">
    {{ Form::bsTableHeader(['Cód.','Producto','U.M.','Cant.','Precio','IVA','Dcto.','Total']) }}
    <tbody>
        <?php 
            $cant_items = 0;
        ?>
        @foreach($doc_registros as $linea )
            <?php
                $unidad_medida = $linea->unidad_medida1;
            ?>

            <tr>
                <td style="text-align: center;"> {{ $linea->producto_id }} </td>
                <td> {{ $linea->item->get_value_to_show(true) }} </td>
                <td style="text-align: center;"> {{ $unidad_medida }} </td>
                <td style="text-align: center;"> {{ number_format( $linea->cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                <td style="text-align: center;"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                <td style="text-align: center;"> {{ number_format( $linea->tasa_descuento, 2, ',', '.').'%' }} </td>
                <td style="text-align: right;"> {{ '$ '.number_format( $linea->precio_total, 2, ',', '.') }} </td>
            </tr>
        @endforeach
        <tr>
            <td style="text-align: center;" colspan="8">
                Continua...
            </td>
        </tr>
        @if($row_span > 0)
            
        <tr>
            <td style="text-align: center; vertical-align: middle; font-size: 3em; color: #ddd; height:{{$row_span * 25}}px" colspan="8">
                ESPACIO EN BLANCO
            </td>
        </tr>
        @endif
    </tbody>
</table>