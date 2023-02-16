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
                    <td>
                        <center><strong>RESUMEN DE VENTAS POS</strong></center>
                    </td>
                </tr>  
            </thead>
            <tbody>
                <tr>
                    <td style="color: black;text-align: center;">
                        <b>Ventas en efectivo</b>
                    </td>
                </tr>
                <tr>
                    <td class="subject" style="color: black;">
                        {{ $registro->caja->descripcion }}: ${{ number_format($result->total_contado,0,',','.') }}
                    </td>
                </tr>
                <tr> 
                    <td style="color: black;text-align: center;">
                        <b>Consignaciones</b>
                    </td>
                </tr> 
                    <td class="subject" style="color: black;">
                        <table class="table">
                            @foreach($result->totales_cuentas_bancarias as $linea_total)
                                <tr>
                                    <td>
                                        {{ $linea_total['label'] }}
                                    </td>
                                    <td>
                                        :
                                    </td>
                                    <td>
                                        ${{ number_format($linea_total['total'],0,',','.') }}
                                    </td>
                                </tr>
                                <?php
                                    $total_consignaciones += $linea_total['total'];
                                ?>
                            @endforeach
                        </table>                        
                    </td>
                <tr> 
                <tr> 
                    <td style="color: black;text-align: center;">
                        <b>Ventas Crédito</b>
                    </td>
                </tr> 
                <tr> 
                    <td class="subject" style="color: black;">
                        Total CxC: ${{ number_format($result->total_credito,0,',','.') }}
                    </td>
                </tr>
                <tr> 
                    <td class="subject" style="color: black;">
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
