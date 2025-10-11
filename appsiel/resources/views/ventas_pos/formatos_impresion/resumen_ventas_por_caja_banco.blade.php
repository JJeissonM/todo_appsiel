<div style="border: solid 1px #ddd; border-radius: 4px; padding: 10px;">
    <h5>RESUMEN POR MEDIOS DE PAGO</h5>
    <table style=" font-size: {{ $tamanino_fuente_2 }}; width=50%; ">
        <tbody>
            <?php 
                $total_ventas_al_detal = 0;
            ?>
            @foreach($ventas_por_medios_pago_con_iva as $linea)
                <tr>
                    <td>
                        <b>{{ $linea->caja_banco }}:</b>
                    </td>
                    <td align="right">
                        ${{ number_format( $ventas_contado_sin_iva * $linea->porcentaje_participacion_total_ventas, 0, ',', '.') }}
                    </td>
                </tr>
                <?php 
                    $total_ventas_al_detal += $ventas_contado_sin_iva * $linea->porcentaje_participacion_total_ventas;
                ?>
            @endforeach
            <tr>
                <td align="right">
                    <b>Crédito:</b>
                </td>
                <td align="right">
                    ${{ number_format( $ventas_credito_sin_iva, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td align="right">
                    <b>VENTAS AL DETAL:</b>
                </td>
                <td align="right">
                    ${{ number_format( $total_ventas_al_detal + $ventas_credito_sin_iva, 0, ',', '.' ) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>