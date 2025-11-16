
<h3>Movimientos de Caja <small>({{ $mensaje }})</small></h3>
<h4> {{"Desde: ".$fecha_desde." - Hasta: ".$fecha_hasta }} </h4>
<div class="table-responsive">
    <table class="table table-striped table-bordered tabla_pdf">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Documento</th>
                <th>Tercero</th>
                <!-- <th>Caja/Banco</th>
                <th>Concepto</th> -->
                <th>Motivo</th>
                <th>Entradas</th>
                <th>Salidas</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $fecha_desde }}</td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <!-- <td> &nbsp; </td>
                <td> &nbsp; </td> -->
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> {{ '$'.number_format($saldo_inicial, 0, ',','.') }}</td>
            </tr>
            <?php
            
                $saldo = $saldo_inicial;

                $total_entradas = 0;
                $total_salidas = 0;
            ?>

            @foreach( $lineas_movimientos as $fila )
                <?php

                    if ( $fila['forma_pago'] != 'efectivo')
                    {
                        continue;
                    }

                    $entrada = '$'.number_format( $fila['valor_entrada'], 0, ',','.');
                    $total_entradas += $fila['valor_entrada'];
                    
                    $salida = '$'.number_format( $fila['valor_salida'], 0, ',','.');
                    $total_salidas += $fila['valor_salida'];

                    $saldo += $fila['valor_entrada'] - $fila['valor_salida'];                    
                ?>
                <tr>
                    <td> {{ $fila['fecha'] }}</td>
                    <td> {{ $fila['documento'] }} </td>
                    <td> 
                        {{ $fila['tercero'] }}
                    </td>
                    <!-- <td> { { $fila['caja_o_banco'] }} </td>
                    <td> { { $fila['concepto'] }} </td> -->
                    <td> {{ $fila['motivo'] }} </td>
                    <td> 
                        @if($mensaje == $fila['caja_o_banco'])
                            {{ $entrada }}
                        @else
                            {{ $entrada }} 
                            @if($entrada != '$0')
                                <i class="fa fa-warning" title="{{ $fila['caja_o_banco'] }}"></i>
                            @endif                            
                        @endif                         
                    </td>
                    <td> {{ $salida}} </td>
                    <td> ${{ number_format( $saldo, 0, ',','.') }} </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"> &nbsp; </td>
                <td> ${{ number_format( $total_entradas, 0, ',','.') }} </td>
                <td> ${{ number_format( $total_salidas, 0, ',','.') }} </td>
                <td></td>
            </tr>
            <tr>
                <td colspan="5"> Total Diferencia </td>
                <td> ${{ number_format( $total_entradas - $total_salidas, 0, ',','.') }} </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
    