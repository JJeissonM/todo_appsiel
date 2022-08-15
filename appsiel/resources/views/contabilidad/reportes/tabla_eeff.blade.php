
<style>
    table {
        border-collapse: separate;
        border-spacing: 1px;
    }
    .tr_abuelo {
      background-color: #90a4ae;
      font-weight: bold;
    }
    .tr_padre {
      background-color: #b0bec5;
      font-weight: bold;
    }
    .tr_padre > td:first-child {
      padding-left: 2rem;
    }
    .tr_hijo {
      background-color: #cfd8dc;
    }
    .tr_hijo > td:first-child {
      padding-left: 4rem;
    }
    .tr_cuenta {
      background-color: #eceff1;
    }
    .tr_cuenta > td:first-child {
      padding-left: 6rem;
    }

    .simbolo_moneda{
        float: left;
    }
  </style>
  
  <table width="100%">
    <thead>
        <tr>
            <th> &nbsp; </th>
            <th> {{ $anio }} </th>
            <th> &nbsp; </th>
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

                $lbl_cr = '';
                if( $value < 0 )
                {
                    $lbl_cr = '  CR';
                }
            ?>
            <?php 
                if (abs($value) == 0) {
                    continue;
                }
            ?>
            <tr class="{{$tr_class}}">
                <td>
                    {{ $label }}
                </td>
                <td align="right">
                    <span class="simbolo_moneda">$</span>
                    {{ number_format( abs($value), 2, ',', '.') }}              
                </td>
                <td align="center">
                    {{ $lbl_cr }}                    
                </td>
            </tr>
        @endforeach
        <tfoot>
            <?php 
                $lbl_cr = '';
                if( $gran_total < 0 )
                {
                    $lbl_cr = '  CR';
                }
            ?>
            <tr class="tr_abuelo">
                <td>
                    TOTAL
                </td>
                <td align="right">
                    <span class="simbolo_moneda">$</span>
                    {{ number_format( abs($gran_total), 2, ',', '.') }}              
                </td>
                <td align="center">
                    {{ $lbl_cr }}                    
                </td>
            </tr>
        </tfoot>
    </tbody>
</table>