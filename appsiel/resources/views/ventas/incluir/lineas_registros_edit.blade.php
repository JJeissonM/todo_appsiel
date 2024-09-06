<?php
    $i = 1;
?>
@foreach( $registros as $linea )
    <?php 
        $costo_unitario = $linea->item->get_costo_promedio();
    ?>
    <tr class="linea_registro" data-numero_linea="{{$i}}">
        <td style="display: none;">
            <div class="inv_motivo_id">10</div>
        </td>
        <td style="display: none;">
            <div class="inv_bodega_id">0</div>
        </td>
        <td style="display: none;">
            <div class="inv_producto_id">{{ $linea->producto_id }}</div>
        </td>
        <td style="display: none;">
            <div class="costo_unitario">{{ $costo_unitario }}</div>
        </td>
        <td style="display: none;">
            <div class="precio_unitario">{{ $linea->precio_unitario }}</div>
        </td>
        <td style="display: none;">
            <div class="base_impuesto">{{ $linea->base_impuesto }}</div>
        </td>
        <td style="display: none;">
            <div class="tasa_impuesto">{{ $linea->tasa_impuesto }}</div>
        </td>
        <td style="display: none;">
            <div class="valor_impuesto">{{ $linea->valor_impuesto }}</div>
        </td>
        <td style="display: none;">
            <div class="base_impuesto_total">{{ $linea->base_impuesto_total }}</div>
        </td>
        <td style="display: none;">
            <div class="cantidad">{{ $linea->cantidad }}</div>
        </td>
        <td style="display: none;">
            <div class="costo_total">{{ $costo_unitario * $linea->cantidad }}</div>
        </td>
        <td style="display: none;">
            <div class="precio_total">{{ $linea->precio_total }}</div>
        </td>
        <td style="display: none;">
            <div class="tasa_descuento">{{ $linea->tasa_descuento }}</div>
        </td>
        <td style="display: none;">
            <div class="valor_total_descuento">{{ $linea->valor_total_descuento }}</div>
        </td> 
        <td> &nbsp; </td>
        <td> 
            <span style="background-color:#F7B2A3;">{{ $linea->producto_id }}</span> {{ $linea->producto_id }} {{ $linea->producto_descripcion }}
        </td> 
        <td> {{ $linea->inv_motivo_descripcion }} </td>
        <td> - </td>
        <td>  
            <div class="elemento_modificar"> {{ $linea->cantidad }} </div>
        </td> 
        <td>
            $<div class="elemento_modificar"> {{ $linea->precio_unitario }}</div>
        </td> 
        <td> 
            {{ $linea->tasa_descuento }}%
        </td>
        <td class="lbl_valor_total_descuento"> 
            ${{ number_format( $linea->valor_total_descuento, 0, ',', '.') }}
        </td>
        <td> 
            {{ $linea->tasa_impuesto }}% 
        </td>
        <td class="lbl_precio_total"> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
        <td>
            <button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button>
        </td>
    </tr>
    <?php
        $i++;
    ?>
@endforeach