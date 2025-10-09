<h3> Flujo de efectivo </h3>
<hr>
<table class="table table-bordered tabla_registros" style="margin-top: -4px;">
    <thead>
        <tr>
        @for($i = 0; $i < $columns; $i++)
            <th>
                {{ $data_array[0][$i] }}
            </th>
        @endfor
            <th>
                Total
            </th>
        </tr>
    </thead>
    <tbody>
        <?php 
            $arr_total_salidas = array_fill(0, $columns, 0); // Creates an array with zeros
            $arr_total_flujo_neto = array_fill(0, $columns, 0); // Creates an array with zeros
        ?>
        @for($i = 1; $i < $rows; $i++)

            @if($data_array[$i][0] == 'TOTAL ENTRADAS')
                <tr style="background-color: #ddd;">
            @else
                <tr>
            @endif
                    <?php 
                        $total_row = 0;
                    ?>
                @for($j = 0; $j < $columns; $j++)
                    @if($j == 0)
                        <td> 
                            <b> {{ $data_array[$i][$j] }} </b>
                        </td>
                    @else
                        <td align="right"> 
                            {{ number_format($data_array[$i][$j], 0, ',', '.') }}
                            <?php 
                                $total_row += $data_array[$i][$j];

                                if ( $data_array[$i][$j] < 0) {
                                    $arr_total_salidas[$j] += $data_array[$i][$j];
                                }
                                
                                if ( $data_array[$i][0] != 'TOTAL ENTRADAS' ) {
                                    $arr_total_flujo_neto[$j] += $data_array[$i][$j];
                                }
                            ?>
                        </td>
                    @endif
                @endfor
                    <td align="right"> 
                        {{ number_format($total_row, 0, ',', '.') }}
                    </td>                    
            </tr>
        @endfor
        <tr style="background-color: #ddd;">
            <?php 
                $total_row = 0;
            ?>
            @for($j = 0; $j < $columns; $j++)
                @if($j == 0)
                    <td> 
                        <b> TOTAL SALIDAS </b>
                    </td>
                @else
                    <td align="right"> 
                        {{ number_format($arr_total_salidas[$j], 0, ',', '.') }}
                        <?php 
                            $total_row += $arr_total_salidas[$j];
                        ?>
                    </td>
                @endif
            @endfor
                <td align="right"> 
                    {{ number_format($total_row, 0, ',', '.') }}
                </td>  
        </tr>

        
        <tr style="background-color: #ddd; border-top: solid 2px;">
            <?php 
                $total_row = 0;
            ?>
            @for($j = 0; $j < $columns; $j++)
                @if($j == 0)
                    <td> 
                        <b> FLUJO NETO </b>
                    </td>
                @else
                    <td align="right"> 
                        {{ number_format( $arr_total_flujo_neto[$j], 0, ',', '.') }}
                        <?php 
                            $total_row +=  $arr_total_flujo_neto[$j];
                        ?>
                    </td>
                @endif
            @endfor
                <td align="right"> 
                    {{ number_format($total_row, 0, ',', '.') }}
                </td>  
        </tr>
    </tbody>
</table>