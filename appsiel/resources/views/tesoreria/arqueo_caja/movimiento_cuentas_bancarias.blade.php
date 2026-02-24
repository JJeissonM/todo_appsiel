<?php
	use App\VentasPos\Services\ReportsServices;

    $service = new ReportsServices();

    $movimentos_cuentas_bancarias = $service->get_movimentos_cuentas_bancarias($registro->fecha, $registro->creado_por);
?>

@if( $movimentos_cuentas_bancarias->count() > 0 )
    <div class="row">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th colspan="3" style="color: black !important; background-color: #f2f2f2;">
                        <center><strong>2. MOVIMIENTOS QR/TRANSF</strong></center>
                    </th>
                </tr>  
            </thead>
            <tbody>
                @foreach ( $movimentos_cuentas_bancarias as $teso_cuenta_bancaria_id => $grupo)
                    <?php 
                        $cuenta_bancaria = $grupo->first()->cuenta_bancaria;
                    ?>
                    <tr>
                        <td colspan="3" style="color: black !important; background-color: #eeeded;">
                            <center><strong>{{ $cuenta_bancaria->get_value_to_show() }}</strong></center>
                        </td>
                    </tr>
                    <tr>
                        <td style="color: black !important; background-color: #f8f6f6;">
                            <center><strong>MOTIVO</strong></center>
                        </td>
                        <td style="color: black !important; background-color: #f8f6f6;">
                            <center><strong>MOVIMIENTO</strong></center>
                        </td>
                        <td style="color: black !important; background-color: #f8f6f6;">
                            <center><strong>VALOR</strong></center>
                        </td>
                    </tr>
                    <?php 
                        $movim_motivo = $grupo->groupBy('teso_motivo_id');
                        $total_entradas = 0;
                        $total_salidas = 0;
                    ?>
                    @foreach ( $movim_motivo as $grupo_motivo )                            
                        <?php 
                            $motivo = $grupo_motivo->first()->motivo;
                        ?>
                        <tr>
                            <td>
                                {{ $motivo->descripcion }}
                            </td>
                            <td style="text-align: center;">
                                {{ ucwords($motivo->movimiento) }}
                            </td>
                            <td style="text-align: right;">
                                ${{ number_format( abs($grupo_motivo->sum('valor_movimiento')), 0, ',', '.') }}
                            </td>
                        </tr> 
                        
                        <?php 
                            if ( $motivo->movimiento == 'entrada') {
                                $total_entradas += abs($grupo_motivo->sum('valor_movimiento'));
                            }else{
                                $total_salidas += abs($grupo_motivo->sum('valor_movimiento'));
                            }
                        ?>                       
                    @endforeach
                        <tr style="border-top: 3px solid #ddd;">
                            <td>
                                Total Entrada
                            </td>
                            <td style="text-align: center;">
                                &nbsp;
                            </td>
                            <td style="text-align: right;">
                                ${{ number_format( $total_entradas, 0, ',', '.') }}
                            </td>
                        </tr> 
                        <tr>
                            <td>
                                Total Salida
                            </td>
                            <td style="text-align: center;">
                                &nbsp;
                            </td>
                            <td style="text-align: right;">
                                ${{ number_format( $total_salidas, 0, ',', '.') }}
                            </td>
                        </tr> 
                        <tr style="border-top: 3px solid #ddd;">
                            <td>
                                Total Esperado
                            </td>
                            <td style="text-align: center;">
                                &nbsp;
                            </td>
                            <td style="text-align: right; font-weight: bold;">
                                ${{ number_format( $total_entradas - $total_salidas, 0, ',', '.') }}
                            </td>
                        </tr> 
                @endforeach
            </tbody>
        </table>
    </div>
    <br>
@endif
