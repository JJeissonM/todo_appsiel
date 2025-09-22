<?php
	use App\VentasPos\Services\ReportsServices;

    $service = new ReportsServices();
    $result = $service->resumen_ventas_arqueo_caja($registro->fecha, $registro->teso_caja_id);

    $total_consignaciones = 0; 

    $recaudos = $service->get_movimentos_trasacciones_recaudos($registro->fecha);
?>

@if( $result->status == 'success')
    <!-- SOLO VENTAS POS -->

    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <td colspan="2" style="color: black !important; background-color: #f2f2f2;">
                            <center><strong>RESUMEN DE VENTAS</strong></center>
                            @if((int)config('ventas_pos.manejar_propinas'))
                                <center>(No incluye Propinas)</center>
                            @endif
                            @if((int)config('ventas_pos.manejar_datafono'))
                                <center>(No incluye Comisión Datafono)</center>
                            @endif
                        </td>
                    </tr>  
                </thead>
                <tbody>
                    <tr>
                        <td style="color: black; text-align: right; width: 50%;">
                            Ventas de contado:
                        </td>
                        <td style="color: black;">
                            <?php 
                                $total_consignaciones = 0;
                                foreach($result->totales_cuentas_bancarias as $linea_total)
                                {
                                    $total_consignaciones += $linea_total['total'];
                                }
                            ?>
                            ${{ number_format($result->total_contado + $total_consignaciones,0,',','.') }}

                        </td>
                    </tr>
                    <tr> 
                        <td style="color: black; text-align: right;">
                            Ventas a crédito:
                        </td>
                        <td style="color: black;" colspan="3">
                            ${{ number_format($result->total_credito,0,',','.') }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="color: black; font-weight: bold; text-align: right;">
                            Total ventas:
                        </td>
                        <td style="color: black; font-weight: bold;">
                            ${{ number_format($result->total_contado + $result->total_credito + $total_consignaciones,0,',','.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="col-md-6">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <td colspan="2" style="color: black !important; background-color: #f2f2f2;">
                            <center><strong>RESUMEN DE INGRESOS DE TESORERÍA</strong></center>
                            @if((int)config('ventas_pos.manejar_propinas'))
                                <center>(No incluye Propinas)</center>
                            @endif
                            @if((int)config('ventas_pos.manejar_datafono'))
                                <center>(No incluye Comisión Datafono)</center>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="color: black !important;text-align: center; background-color: #f2f2f2;">
                            Efectivo
                        </td>
                        <td style="color: black !important;text-align: center; background-color: #f2f2f2;">
                            QR/Transf.
                        </td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $total_ingresos_contado = $result->total_contado + $recaudos->where('teso_caja_id', $registro->teso_caja_id)->sum('valor_movimiento');
                        $total_consignaciones = 0;
                    ?>
                    <tr>
                        <td class="subject" style="color: black;">
                            {{ $registro->caja->descripcion }}: ${{ number_format( $total_ingresos_contado, 0, ',', '.') }}
                        </td>
                        <td class="subject" style="color: black;">
                            <table class="table">
                                @foreach($result->totales_cuentas_bancarias as $linea_total)
                                    <tr>
                                        <td style="text-align: right;">
                                            {{ $linea_total['label'] }}:
                                        </td>
                                        <td style="text-align: right;">
                                            ${{ number_format($linea_total['total'] + $recaudos->where('teso_cuenta_bancaria_id', $linea_total['teso_cuenta_bancaria_id'])->sum('valor_movimiento'),0,',','.') }}
                                        </td>
                                    </tr>
                                    <?php
                                        $total_consignaciones += $linea_total['total'] +  $recaudos->where('teso_cuenta_bancaria_id', $linea_total['teso_cuenta_bancaria_id'])->sum('valor_movimiento');
                                    ?>
                                @endforeach
                            </table>                        
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td style="color: black; font-weight: bold; text-align: center;" colspan="2">
                            Total ingresos de Tesorería: ${{ number_format( $total_ingresos_contado + $total_consignaciones,0,',','.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
    </div>
    <br>
@else
    <b>Nota:</b> {{ $result->message }}
@endif
