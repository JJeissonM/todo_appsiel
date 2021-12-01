<table>
    <thead>
        <tr>
            <th>
                &nbsp;
            </th>
            <th>
                {{ $anio }}
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach( $filas AS $fila )
            <?php 
                $tr_class = '';
                $label = '';
                $value = 0;
                if( gettype( $fila->datos_clase_cuenta ) != 'integer' )
                {
                    $tr_class = 'tr_abuelo';
                    $label = $fila->datos_clase_cuenta->descripcion;
                    $value = $fila->datos_clase_cuenta->valor;
                }
                if( gettype( $fila->datos_grupo_padre ) != 'integer' )
                {
                    $tr_class = 'tr_padre';
                    $label = $fila->datos_grupo_padre->descripcion;
                    $value = $fila->datos_grupo_padre->valor;
                }
                if( gettype( $fila->datos_grupo_hijo ) != 'integer' )
                {
                    $tr_class = 'tr_hijo';
                    $label = $fila->datos_grupo_hijo->descripcion;
                    $value = $fila->datos_grupo_hijo->valor;
                }
                if( gettype( $fila->datos_cuenta ) != 'integer' )
                {
                    $tr_class = 'tr_cuenta';
                    $label = $fila->datos_cuenta->descripcion;
                    $value = $fila->datos_cuenta->valor;
                }
            ?>
            <tr class="{{$tr_class}}">
                <td>
                    {{ $label }}
                </td>
                <td align="right">
                    {{ Form::TextoMoneda( $value, ''  )  }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>