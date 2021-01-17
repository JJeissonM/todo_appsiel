{{ Form::bsBtnExcel('movimiento_tesoreria') }}
<h3>Resumen de Movimientos de Cajas/Bancos</h3>
<h4> {{"Desde: ".$fecha_desde." - Hasta: ".$fecha_hasta }} </h4>
<div class="table-responsive">
    <table class="table table-striped table-bordered tabla_pdf">
        <thead>
            <tr>
                <th>Medio Pago/Recaudo</th>
                <th>Entradas</th>
                <th>Salidas</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3"> Saldo inicial </td>
                <td> {{ '$'.number_format($saldo_inicial, 0, ',','.') }}</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: center; font-weight: bold;"> MOVIMIENTO DE CAJAS </td>
            </tr>
            <?php
                $saldo = $saldo_inicial;

                $total_entradas = 0;
                $total_salidas = 0;

                $total_entradas_cajas = 0;
                $total_salidas_cajas = 0;

                foreach( $ids_cajas as $key => $value )
                {
                    if ($value != 0)
                    {
                        $valor_entradas = $movimiento_entradas->where('teso_caja_id',$value)->sum('valor_movimiento');
                        //dd($movimiento_entradas);
                        $valor_salidas = $movimiento_salidas->where('teso_caja_id',$value)->sum('valor_movimiento');

                        $total_entradas_cajas += $valor_entradas;
                        $total_salidas_cajas += $valor_salidas;

                        $total_entradas += $valor_entradas;
                        $total_salidas += $valor_salidas;

                        $saldo += $valor_entradas + $valor_salidas;

                        $caja = App\Tesoreria\TesoCaja::find( $value );
                        
            ?>
                <tr>
                    <td> {{ $caja->descripcion }}</td>
                    <td> ${{ number_format( $valor_entradas, 0, ',','.') }} </td>
                    <td> ${{ number_format( $valor_salidas, 0, ',','.') }} </td>
                    <td> ${{ number_format( $saldo, 0, ',','.') }} </td>
                </tr>
            <?php 
                    }
                } ?>


            <tr style="background: #4a4a4a; color: white;">
                <td> &nbsp; </td>
                <td> ${{ number_format( $total_entradas_cajas, 0, ',','.') }} </td>
                <td> ${{ number_format( $total_salidas_cajas, 0, ',','.') }} </td>
                <td></td>
            </tr>

            <tr>
                <td colspan="4" style="text-align: center; font-weight: bold;"> MOVIMIENTO DE CUENTAS BANCARIAS </td>
            </tr>
            <?php


                $total_entradas_cuentas_bancarias = 0;
                $total_salidas_cuentas_bancarias = 0;

                foreach( $ids_cuentas_bancarias as $key => $value )
                {
                    if ($value != 0)
                    {
                        $valor_entradas = $movimiento_entradas->where('teso_cuenta_bancaria_id',$value)->sum('valor_movimiento');
                        //dd($movimiento_entradas);
                        $valor_salidas = $movimiento_salidas->where('teso_cuenta_bancaria_id',$value)->sum('valor_movimiento');

                        $total_entradas_cuentas_bancarias += $valor_entradas;
                        $total_salidas_cuentas_bancarias += $valor_salidas;

                        $total_entradas += $valor_entradas;
                        $total_salidas += $valor_salidas;

                        $saldo += $valor_entradas + $valor_salidas;

                        $cuenta_bancaria = App\Tesoreria\TesoCuentaBancaria::find( $value );
                        
            ?>
                <tr>
                    <td> {{ "Cuenta " . $cuenta_bancaria->tipo_cuenta . " " . $cuenta_bancaria->entidad_financiera->descripcion . " No. " . $cuenta_bancaria->descripcion }}</td>
                    <td> ${{ number_format( $valor_entradas, 0, ',','.') }} </td>
                    <td> ${{ number_format( $valor_salidas, 0, ',','.') }} </td>
                    <td> ${{ number_format( $saldo, 0, ',','.') }} </td>
                </tr>
            <?php 
                    }
                } ?>


            <tr style="background: #4a4a4a; color: white;">
                <td> &nbsp; </td>
                <td> ${{ number_format( $total_entradas_cuentas_bancarias, 0, ',','.') }} </td>
                <td> ${{ number_format( $total_salidas_cuentas_bancarias, 0, ',','.') }} </td>
                <td></td>
            </tr>

        </tbody>
        <tfoot>
            <tr>
                <td> &nbsp; </td>
                <td> ${{ number_format( $total_entradas, 0, ',','.') }} </td>
                <td> ${{ number_format( $total_salidas, 0, ',','.') }} </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
    