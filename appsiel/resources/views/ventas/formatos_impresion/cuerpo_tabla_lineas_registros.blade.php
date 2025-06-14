
<?php
    $total_cantidad = 0;
    $subtotal = 0;
    $total_impuestos = 0;
    $total_factura = 0;
    $array_tasas = [];

    $arr_inv_grupos_ids = [];
    $sum_precio_total = 0;
    $sum_cantidad = 0;
    foreach ($doc_registros as $linea) {
        $arr_inv_grupos_ids[] = $linea->item->inv_grupo_id;
        $sum_precio_total += $linea->precio_total;

        if( (int)config('inventarios.categoria_id_paquetes_con_materiales_ocultos') == $linea->item->inv_grupo_id  ){
            $sum_cantidad += $linea->cantidad;
        }
    }
?>

@if( in_array( (int)config('inventarios.categoria_id_paquetes_con_materiales_ocultos'), $arr_inv_grupos_ids ) )
    @foreach($doc_registros as $linea )
        <tr>
            <?php
                $referencia = '';
                if($linea->referencia != '')
                {
                    $referencia = ' - ' . $linea->referencia;
                }

                $tasa_impuesto = '';
                $precio_unitario = '';
                $precio_total = '';
                if( (int)config('inventarios.categoria_id_paquetes_con_materiales_ocultos') == $linea->item->inv_grupo_id  ){
                    $tasa_impuesto = number_format( $linea->tasa_impuesto, 0, ',', '.') . '%';
                    $precio_unitario = ' ($' . number_format( $sum_precio_total / $sum_cantidad, 0, ',', '.') . ')';
                    $precio_total = '$' . number_format( $sum_precio_total, 0, ',', '.');
                }

            ?>
            <td> {{ $linea->producto_descripcion . $referencia }} </td>
            <td class="text-right">
                {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->item->get_unidad_medida1() }}
                {{ $precio_unitario }}
            </td>
            <td class="text-center"> {{ $tasa_impuesto }} </td>
            <td class="text-right"> {{ $precio_total }} </td>
        </tr>

        @if( $linea->valor_total_descuento != 0 )
            <tr>
                <td colspan="3" style="text-align: right;">Dcto.</td>
                <td class="text-right"> ( -${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }} ) </td>
            </tr>
        @endif
    @endforeach
@else
    @foreach($doc_registros as $linea )
        <tr>
            <?php
                $referencia = '';
                if($linea->referencia != '')
                {
                    $referencia = ' - ' . $linea->referencia;
                }
            ?>
            <td> {{ $linea->producto_descripcion . $referencia }} </td>
            <td class="text-right">
                {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->item->get_unidad_medida1() }}
                (${{ number_format( $linea->precio_unitario, 0, ',', '.') }})
            </td>
            <td class="text-center"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.') }}% </td>
            <td class="text-right"> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
        </tr>

        @if( $linea->valor_total_descuento != 0 )
            <tr>
                <td colspan="3" style="text-align: right;">Dcto.</td>
                <td class="text-right"> ( -${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }} ) </td>
            </tr>
        @endif
    @endforeach
@endif
