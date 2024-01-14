<div style="border: solid 1px #ddd; border-radius: 4px; padding: 10px;">
    <h5>RESUMEN MEDIOS DE PAGO</h5>
    <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
        <tbody>
            @foreach($ventas_por_medios_pago_con_iva as $linea)
                <?php 
                    
                ?>
                <tr>
                    <td>
                        <b>{{ $linea->medio_pago }}:</b>
                    </td>
                    <td align="right">
                        ${{ number_format( $ventas_contado_sin_iva * $linea->porcentaje_participacion_total_ventas, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td>
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
                    ${{ number_format( $movimientos->sum('base_impuesto_total'), 0, ',', '.' ) }}
                </td>
            </tr>
            <tr>
                <td align="right">
                    <b>VENTAS POR MAYOR:</b>
                </td>
                <td align="right">
                    $0
                </td>
            </tr>
        </tbody>
    </table>
</div>