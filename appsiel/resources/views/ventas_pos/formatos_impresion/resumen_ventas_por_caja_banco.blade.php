<table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
        <thead>
            <tr>
                <th colspan="5">RESUMEN POR MEDIOS DE PAGO</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $total_ventas_al_detal = 0;
            ?>
            @foreach($ventas_por_medios_pago_con_iva as $linea)
                <tr>
                    <td colspan="3" align="right">
                        <b>{{ $linea->caja_banco }}:</b>
                    </td>
                    <td align="right">
                        ${{ number_format( $ventas_contado_sin_iva * $linea->porcentaje_participacion_total_ventas, 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
                <?php 
                    $total_ventas_al_detal += $ventas_contado_sin_iva * $linea->porcentaje_participacion_total_ventas;
                ?>
            @endforeach
            <tr>
                <td colspan="3" align="right" style="width:40%;">
                    <b>Crédito:</b>
                </td>
                <td align="right" style="width:20%;">
                    ${{ number_format( $ventas_credito_sin_iva, 0, ',', '.') }}
                </td>
                <td></td>
            </tr>
            <tr>
                <td colspan="3" align="right">
                    <b>TOTAL:</b>
                </td>
                <td align="right">
                    ${{ number_format( $total_ventas_al_detal + $ventas_credito_sin_iva, 0, ',', '.' ) }}
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>