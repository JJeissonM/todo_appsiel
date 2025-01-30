<?php
	use App\VentasPos\Services\ReportsServices;

    $service = new ReportsServices();
    $result = $service->resumen_ventas_arqueo_caja($registro->fecha, $registro->teso_caja_id);

    $total_consignaciones = 0; 
?>

@if( $result->status == 'success')
    <!-- SOLO VENTAS POS -->

    <div class="row">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td colspan="3">
                        <center><strong>RESUMEN DE VENTAS POS</strong></center>
                        @if((int)config('ventas_pos.manejar_propinas'))
                            <center>(No incluye Propinas)</center>
                        @endif
                        @if((int)config('ventas_pos.manejar_datafono'))
                            <center>(No incluye Comisión Datafono)</center>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="color: black;text-align: center;">
                        Ventas en efectivo
                    </td>
                    <td style="color: black;text-align: center;">
                        Consignaciones
                    </td>
                    <td style="color: black;text-align: center;">
                        Ventas Crédito
                    </td>
                </tr>     
            </thead>
            <tbody>
                <tr>
                    <td class="subject" style="color: black;">
                        {{ $registro->caja->descripcion }}: ${{ number_format($result->total_contado,0,',','.') }}
                    </td>
                    <td class="subject" style="color: black;">
                        <table class="table">
                            @foreach($result->totales_cuentas_bancarias as $linea_total)
                                <tr>
                                    <td style="text-align: right;">
                                        {{ $linea_total['label'] }}:
                                    </td>
                                    <td style="text-align: left;">
                                        ${{ number_format($linea_total['total'],0,',','.') }}
                                    </td>
                                </tr>
                                <?php
                                    $total_consignaciones += $linea_total['total'];
                                ?>
                            @endforeach
                        </table>                        
                    </td>
                    <td class="subject" style="color: black;">
                        Total CxC: ${{ number_format($result->total_credito,0,',','.') }}
                    </td>
                </tr>
                <tr> 
                    <td class="subject" style="color: black;" colspan="3">
                        <div style="text-align: center; color:black;">TOTAL VENTAS: $ {{ number_format($result->total_contado + $result->total_credito + $total_consignaciones, 0, ',', '.') }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
@else
    <b>Nota:</b> {{ $result->message }}
@endif
