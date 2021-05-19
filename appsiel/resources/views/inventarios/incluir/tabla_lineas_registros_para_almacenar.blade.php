@foreach( $lineas_registros as $linea )
    <?php
        $etiqueta_producto = $linea->item->descripcion . ' (' . $linea->item->unidad_medida1 . ')';

        if ( $linea->item->unidad_medida2 != '')
        {
            $etiqueta_producto = $linea->item->descripcion . ' (' . $linea->item->unidad_medida1 . ')' . ' - Talla: ' . $linea->item->unidad_medida2 . ')';
        }

        $color = 'green';
        if ($linea->motivo->movimiento == 'salida' )
        {
            $color = 'entrada';
        }

        $color_fila = 'transparent';
        if ( ($linea->existencia_actual - abs($linea->cantidad) ) < 0 && $linea->item->tipo != 'servicio' )
        {
            $color_fila = '#FF8C8C';
        }
    ?>
    <tr id="{{$linea->inv_producto_id}}" style="background-color: {{$color_fila}}">
        <td class="text-center"> {{$linea->inv_producto_id}} </td>
        <td class="nom_prod"> {{$etiqueta_producto}}</td>
        <td>
            <span style="color:transparent;">{{$linea->motivo->id}}-</span>
            <span style="color:{{$color}};">{{ $linea->motivo->descripcion }}</span>
            <input type="hidden" class="movimiento" value="{{$linea->motivo->movimiento}}">
        </td>
        <td class="costo_unitario" align="right">@if( $linea->motivo->movimiento == 'entrada' )$<div class="elemento_modificar" title="Doble click para modificar." data-campo_modificado="costo_unitario">{{ number_format( $linea->costo_unitario, 2, '.', '') }}</div>@else${{ number_format( abs($linea->costo_unitario), 2, '.', '') }}@endif</td>
        <td class="cantidad" align="center"><div class="elemento_modificar" title="Doble click para modificar." data-campo_modificado="cantidad">{{ number_format( abs($linea->cantidad), 2, '.', '') }}</div> {{ $linea->item->unidad_medida1 }}
        </td>
        <td class="costo_total" align="right">${{ number_format( abs($linea->costo_total), 2, '.', '') }}</td>
        <td> <button type="button" class="btn btn-danger btn-xs btn_eliminar"><i class="fa fa-btn fa-trash"></i></button>
        </td>
    </tr>
@endforeach