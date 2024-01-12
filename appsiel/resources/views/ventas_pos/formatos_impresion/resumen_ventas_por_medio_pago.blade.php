<?php
	use App\VentasPos\Services\ReportsServices;

    $service = new ReportsServices();
    $movimiento_tesoreria_pdv = $service->get_movimiento_tesoreria_pdv($pdv_id, $fecha, $fecha);
    
    $movin_por_medio_recaudo = $movimiento_tesoreria_pdv->groupBy('teso_medio_recaudo_id');
    
    $ventas_credito_pdv = $service->get_ventas_credito_pdv($pdv_id, $fecha, $fecha);

    $total_ventas_iva_incluido = $movimiento_tesoreria_pdv->sum('valor_movimiento') + $ventas_credito_pdv;

    $ventas_base_impuesto_total = $movimientos->sum('base_impuesto_total');

    $ventas_por_medios_pago  = collect([]);
    foreach ($movin_por_medio_recaudo as $movin_grupo) {
        
        $primera_linea_movin_grupo = $movin_grupo->first();

        $ventas_por_medios_pago->push((object)[
                'medio_pago' => $primera_linea_movin_grupo->medio_pago->descripcion,
                'total_venta' => $movin_grupo->sum('valor_movimiento'),
                'porcentaje_participacion_total_ventas' => $movin_grupo->sum('valor_movimiento') / $ventas_base_impuesto_total
            ]);
    }

    $ventas_por_medios_pago->push((object)[
                'medio_pago' => 'Crédito',
                'total_venta' => $ventas_credito_pdv,
                'porcentaje_participacion_total_ventas' => (float)$ventas_credito_pdv / $ventas_base_impuesto_total
            ]);

?>

<div style="border: solid 1px #ddd; border-radius: 4px; padding: 15px;">
    <h6>RESUMEN MEDIOS DE PAGO</h6>
    <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
        <tbody>
            @foreach($ventas_por_medios_pago as $linea)
                <?php 
                    
                ?>
                <tr>
                    <td>
                        <b>{{ $linea->medio_pago }}:</b>
                    </td>
                    <td align="right">
                        ${{ number_format( $ventas_base_impuesto_total * $linea->porcentaje_participacion_total_ventas, 0, ',', '.') }}
                    </td>
                </tr>
            @endforeach
            <tr>
                <td align="right">
                    <b>VENTAS AL DETAL:</b>
                </td>
                <td align="right">
                    ${{ number_format( $ventas_base_impuesto_total, 0, ',', '.' ) }}
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