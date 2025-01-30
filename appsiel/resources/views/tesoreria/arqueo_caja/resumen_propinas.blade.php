<?php
	use App\VentasPos\Services\ReportsServices;

    $service = new ReportsServices();
    $result = $service->resumen_propinas_arqueo_caja($registro->fecha, $registro->teso_caja_id);

    $total_propinas = 0; 
?>

@if( $result->status == 'success')
    <!-- SOLO VENTAS POS -->

    <div class="row">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <td colspan="2">
                        <center><strong>RESUMEN DE PROPINAS</strong></center>
                    </td>
                </tr>
                <tr>
                    <td style="color: black;text-align: center;">
                        Caja/Cta. Bancaria
                    </td>
                    <td style="color: black;text-align: center;">
                        Valor
                    </td>
                </tr>     
            </thead>
            <tbody>
                <tr>
                    <td>
                        {{ $registro->caja->descripcion }}:
                    </td>
                    <td style="text-align: right;">
                        ${{ number_format($result->total_caja, 0, ',', '.') }}
                    </td>
                    <?php
                        $total_propinas += $result->total_caja;
                    ?>
                </tr>
                @foreach($result->totales_cuentas_bancarias as $linea_total)
                    <tr>
                        <td>
                            {{ $linea_total['label'] }}
                        </td>
                        <td style="text-align: right;">
                            ${{ number_format($linea_total['total'],0,',','.') }}
                        </td>
                    </tr>
                    <?php
                        $total_propinas += $linea_total['total'];
                    ?>
                @endforeach
                <tr> 
                    <td>
                        TOTAL PROPINAS
                    </td>
                    <td style="text-align: right;">
                        $ {{ number_format($total_propinas, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <br>
@else
    <b>Nota:</b> {{ $result->message }}
@endif
