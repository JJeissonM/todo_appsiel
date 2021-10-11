<h3 style="width: 100%; text-align: center;"> Documentos pendientes de Cuentas por Cobrar </h3>
<hr>

<table id="myTable" class="table table-striped">
    <thead>
        <tr>
            <th> Tercero </th>
            <th> Documento </th>
            <th> Fecha </th>
            <th> Fecha vencimiento </th>
            <th> Valor Documento </th>
            <th> Valor pagado </th>
            <th> Saldo pendiente </th>
        </tr>
    </thead>
    <tbody>
        <?php
            $cantidad_registros = count( $movimiento );

            $total_valor_documento_tercero = 0;
            $total_valor_pagado_tercero = 0;
            $total_saldo_pendiente_tercero = 0;

            $total_valor_documento_movimiento = 0;
            $total_valor_pagado_movimiento = 0;
            $total_saldo_pendiente_movimiento = 0;

            $id_tercero_anterior = 0;
            $es_primer_registro = true;
            $iteracion = 1;
            $hay_mas_registros = true;
        ?>

        @foreach( $movimiento AS $linea_movimiento )
            <?php
                
                if( $linea_movimiento->show == 0 )
                {
                    continue;
                }

                $id_tercero_actual = $linea_movimiento->core_tercero_id;
            ?>

            @if( $id_tercero_actual != $id_tercero_anterior )

                @if( !$es_primer_registro )
                    @include( 'cxc.reportes.documentos_pendientes_cxc_linea_subtotales_tabla' )
                    <tr style="background-color: white;"><td colspan="7">&nbsp;</td></tr>
                    <?php
                        $total_valor_documento_tercero = 0;
                        $total_valor_pagado_tercero = 0;
                        $total_saldo_pendiente_tercero = 0;
                    ?>
                @endif

                <?php
                    $es_primer_registro = false;
                    $id_tercero_anterior = $id_tercero_actual;
                ?>

                @include( 'cxc.reportes.documentos_pendientes_cxc_linea_documento_tabla' )

                <?php
                    $total_valor_documento_tercero += $linea_movimiento->valor_documento;
                    $total_valor_pagado_tercero += $linea_movimiento->valor_pagado;
                    $total_saldo_pendiente_tercero += $linea_movimiento->saldo_pendiente;
                    
                    $total_valor_documento_movimiento += $linea_movimiento->valor_documento;
                    $total_valor_pagado_movimiento += $linea_movimiento->valor_pagado;
                    $total_saldo_pendiente_movimiento += $linea_movimiento->saldo_pendiente;
                ?>
            @else
                @include( 'cxc.reportes.documentos_pendientes_cxc_linea_documento_tabla' )
                <?php
                    $total_valor_documento_tercero += $linea_movimiento->valor_documento;
                    $total_valor_pagado_tercero += $linea_movimiento->valor_pagado;
                    $total_saldo_pendiente_tercero += $linea_movimiento->saldo_pendiente;
                    
                    $total_valor_documento_movimiento += $linea_movimiento->valor_documento;
                    $total_valor_pagado_movimiento += $linea_movimiento->valor_pagado;
                    $total_saldo_pendiente_movimiento += $linea_movimiento->saldo_pendiente;

                    $id_tercero_anterior = $id_tercero_actual;
                    $es_primer_registro = false;
                ?>
            @endif

            <?php
                if ( $iteracion == $cantidad_registros )
                {
                    $hay_mas_registros = false;
                }

                $iteracion++;
            ?>
        @endforeach

        @include( 'cxc.reportes.documentos_pendientes_cxc_linea_subtotales_tabla' )
        <tr style="background-color: white;"><td colspan="7">&nbsp;</td></tr>

        <tr style="background: #4a4a4a; color: white;">
            <td colspan="4">
                <strong>Total movimiento</strong>
            </td>
            <td>
                <strong> ${{ number_format($total_valor_documento_movimiento, 2, ',', '.') }} </strong>
            </td>
            <td>
                <strong> ${{ number_format($total_valor_pagado_movimiento, 2, ',', '.') }} </strong>
            </td>
            <td>
                <strong> ${{ number_format($total_saldo_pendiente_movimiento, 2, ',', '.') }} </strong>
            </td>
        </tr>
    </tbody>
</table>