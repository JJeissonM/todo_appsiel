
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

                    $msj_warning = '';
                    $teso_caja_id = isset($fila['teso_caja_id']) ? (int)$fila['teso_caja_id'] : 0;
                    $caja_o_banco = isset($fila['caja_o_banco']) ? trim((string)$fila['caja_o_banco']) : '';
                    $caja_o_banco_normalizada = strtolower($caja_o_banco);
                    $mensaje_normalizado = strtolower(trim((string)$mensaje));
                    $mostrar_warning = false;

                    if ($teso_caja_id != 0) {
                        $mostrar_warning = ($teso_caja_id !== (int)$caja_pdv_id);
                    } elseif ($caja_o_banco !== '') {
                        $mostrar_warning = ($caja_o_banco_normalizada !== $mensaje_normalizado);
                    }

                    if ($mostrar_warning)
                    {
                        $msj_warning = '<i class="fa fa-warning" title="El movimiento se realizó por ' . $fila['caja_o_banco'] . ' y no por ' . $mensaje . '"></i>';
                    }                 
                ?>
                <tr>
                    <td> {{ $fila['fecha'] }}</td>
                    <td> {{ $fila['documento'] }} </td>
                    <td> 
                        {{ $fila['tercero'] }}
                    </td>
                    <td> {{ $fila['motivo'] }} </td>
                    <td> 
                        {{ $entrada }}
                        {!! $msj_warning !!}
                    </td>
                    <td>
                        {{ $salida}}
                    </td>
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
    
