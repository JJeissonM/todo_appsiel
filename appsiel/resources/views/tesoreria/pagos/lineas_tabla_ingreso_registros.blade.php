<?php  
    $i = 1;
?>
@foreach( $doc_registros as $linea )

    <tr id="fila_{{$i}}" >        
        
        <td id="cuenta_{{$i}}">
            <span style="color:white;"> {{ $linea->motivo_id }}-</span>{{ $linea->motivo }} 
            {{ $linea->cuenta }}
        </td>
        
        <td id="tercero_{{$i}}">
            @if( $linea->numero_identificacion != $tercero_encabezado_numero_identificacion )
                <span style="color:white;"> {{ $linea->tercero_id }}-</span> {{ $linea->tercero_nombre_completo }} ( {{ number_format( $linea->numero_identificacion, 0 ) }} )
            @else
                <span style="color:white;"> -</span>
            @endif
        </td>
        
        <td id="detalle_{{$i}}">
            <div style="display: inline;">
                <div class="elemento_modificar" title="Doble click para modificar."> 
                    {{ $linea->detalle_operacion }} 
                </div> 
            </div>
        </td>
        
        <td id="debito_{{$i}}" class="valor">$
            <div style="display: inline;">
                <div class="elemento_modificar" title="Doble click para modificar."> 
                    {{ $linea->valor }} 
                </div> 
            </div>
        </td>

        <td>
            <button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-trash'></i></button>
        </td>
    </tr>
    <?php  $i++; ?>
@endforeach