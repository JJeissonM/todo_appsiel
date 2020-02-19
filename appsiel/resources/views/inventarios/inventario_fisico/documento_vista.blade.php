<div>

    IF = Inventario Físico

    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','Cantidad IF','Costo Total IF','Cantidad sistema','Costo Total sistema','DIF. Cantidad','DIF. Total sistema','']) }}
        </thead>
        <tbody>
            <?php 
            
            $total_cantidad = 0;
            $total_documento = 0;
            $total_cantidad_sistema = 0;
            $total_documento_sistema = 0;
            $total_cantidad_dif = 0;
            $total_documento_dif = 0;
            ?>
            @foreach($doc_registros as $linea )
                        <?php
                            $diferencia = round( $linea->cantidad - $linea->cantidad_sistema , 0);

                            $diferencia_costo = $linea->costo_total - $linea->costo_total_sistema;

                            if ( $diferencia > 0 )
                            {
                                $resultado = '<span style="color:green;">Sobrante</span>';
                            }else{
                                $resultado = '<span style="color:red;">Faltante</span>';
                            }

                            if (  (-0.0001 < $diferencia) && ($diferencia < 0.0001 ) )
                            {
                                $resultado = '<span style="color:black;"><i class="fa fa-check"></i></span>';
                                $diferencia_costo = 0;
                            }
                        ?>
                <tr>
                    <td> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->producto_descripcion }} ({{ $linea->unidad_medida1 }}) </td>

                    <!-- Datos del conteo físico -->
                    <td> {{ number_format( $linea->cantidad, 2, ',', '.') }}  </td>
                    <td> ${{ number_format( $linea->costo_total, 0, ',', '.') }} </td>

                    <!-- Datos del sistema -->
                    <td> {{ number_format( $linea->cantidad_sistema, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $linea->costo_total_sistema, 0, ',', '.') }} </td>

                    <!-- Datos de la diferencia -->
                    <td> {{ number_format( $diferencia, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $diferencia_costo, 0, ',', '.') }} </td>

                    <td> 
                        <?php
                            echo $resultado;
                        ?>
                    </td>
                </tr>
                <?php 
                    $total_cantidad += $linea->cantidad;
                    $total_documento += $linea->costo_total;
                    $total_cantidad_sistema += $linea->cantidad_sistema;
                    $total_documento_sistema += $linea->costo_total_sistema;
                    $total_cantidad_dif += ( $linea->cantidad - $linea->cantidad_sistema);
                    $total_documento_dif += ( $linea->costo_total - $linea->costo_total_sistema);
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                        <?php 
                            if ( $total_cantidad_dif > 0 )
                            {
                                $resultado = '<span style="color:green;">Sobrante</span>';
                            }else{
                                $resultado = '<span style="color:red;">Faltante</span>';
                            }

                            if (  (-0.0001 < $total_cantidad_dif) &&  ($total_cantidad_dif < 0.0001) )
                            {
                                $resultado = '<span style="color:black;"><i class="fa fa-check"></i></span>';
                                $total_documento_dif = 0;
                            }
                        ?>

                <td colspan="2">&nbsp;</td>
                <td> {{ number_format($total_cantidad, 0, ',', '.') }} </td>
                <td> {{ '$ '.number_format($total_documento, 0, ',', '.') }} </td>
                <td> {{ number_format($total_cantidad_sistema, 0, ',', '.') }} </td>
                <td> {{ '$ '.number_format($total_documento_sistema, 0, ',', '.') }} </td>
                <td> {{ number_format($total_cantidad_dif, 0, ',', '.') }} </td>
                <td> {{ '$ '.number_format($total_documento_dif, 0, ',', '.') }} </td>
                    <td> 
                        <?php

                            echo $resultado;
                        ?>
                    </td>
            </tr>
        </tfoot>
    </table>
</div>