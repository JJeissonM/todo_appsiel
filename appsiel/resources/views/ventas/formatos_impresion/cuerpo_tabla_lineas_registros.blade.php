
<?php
    $total_cantidad = 0;
    $subtotal = 0;
    $total_impuestos = 0;
    $total_factura = 0;
    $array_tasas = [];
?>
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
            {{ number_format( $linea->cantidad, 2, ',', '.') }} {{ $linea->unidad_medida1 }}
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