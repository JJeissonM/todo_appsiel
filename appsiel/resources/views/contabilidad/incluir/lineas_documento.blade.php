<?php  
    $i = 1;
?>
@foreach( $doc_registros as $linea )
    
    <tr id="fila_{{$i}}" >
        <td id="cuenta_{{$i}}">
            <span style="color:white;"> {{ $linea->cuenta_id }}-</span>{{ $linea->cuenta_codigo }} 
            {{ $linea->cuenta }}
        </td>
        <td id="tercero_{{$i}}">
            @if( $linea->numero_identificacion != $tercero_encabezado_numero_identificacion )
                <span style="color:white;"> {{ $linea->tercero_id }}-</span>{{ $linea->numero_identificacion }} {{ $linea->tercero }}
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
        
        <td id="debito_{{$i}}" class="debito">$
            <div style="display: inline;"> <!-- Padre -->
                <div class="elemento_modificar" title="Doble click para modificar."> 
                    {{ $linea->valor_debito }} 
                </div> 
            </div>
        </td>

        <td id="credito_{{$i}}" class="credito">$
            <div style="display: inline;"> <!-- Padre -->
                <div class="elemento_modificar" title="Doble click para modificar."> 
                    {{ $linea->valor_credito }} 
                </div> 
            </div>
        </td>
        
        <td>
            <button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></button>
        </td>
    </tr>
    <?php  $i++; ?>
@endforeach