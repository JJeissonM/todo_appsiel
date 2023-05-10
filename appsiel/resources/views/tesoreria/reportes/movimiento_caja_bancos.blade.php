{{ Form::bsBtnExcel('movimiento_tesoreria') }}
<h3>Movimiento de Cajas / Bancos</h3>
<h4> {{"Desde: ".$fecha_desde." - Hasta: ".$fecha_hasta }} </h4>
<div class="table-responsive">
    <table class="table table-striped table-bordered tabla_pdf">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Documento</th>
                <th>Tercero</th>
                <th>Caja/Banco</th>
                <th>Concepto</th>
                <th>Motivo</th>
                <th>Entradas</th>
                <th>Salidas</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $fecha_desde }}</td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> {{ '$'.number_format($saldo_inicial, 0, ',','.') }}</td>
            </tr>
            <?php
                
                $saldo = $saldo_inicial;

                $total_entradas = 0;
                $total_salidas = 0;

                foreach( $movimiento as $fila )
                {
                    $entrada = 0;
                    $salida = 0;
                    if ( $fila->valor_movimiento >= 0 )
                    {
                        $entrada = '$'.number_format( $fila->valor_movimiento, 0, ',','.');
                        $salida = '';
                        $total_entradas += $fila->valor_movimiento;
                    }else{
                        $entrada = '';
                        $salida = '$'.number_format( $fila->valor_movimiento * -1, 0, ',','.');
                        $total_salidas += $fila->valor_movimiento;
                    }

                    $saldo += $fila->valor_movimiento;
                    
                    $caja = '';
                    if( !is_null( $fila->caja ) )
                    {
                        $caja = $fila->caja->descripcion;
                    }
                    
                    $cuenta_bancaria = '';
                    if( !is_null( $fila->cuenta_bancaria ) )
                    {
                        $cuenta_bancaria = 'Cuenta ' . $fila->cuenta_bancaria->tipo_cuenta . ' ' . $fila->cuenta_bancaria->entidad_financiera->descripcion . ' No. ' . $fila->cuenta_bancaria->descripcion;
                    }

                    $detalle_operacion = $fila->descripcion;
                    $registro_linea = $fila->get_registro_linea_movimiento( $fila->teso_motivo_id, $fila->valor_movimiento );
                    if ( $registro_linea != null )
                    {
                        $detalle_operacion = $fila->descripcion . ' ' . $registro_linea->detalle_operacion;
                    }
            ?>
                <tr>
                    <td> {{ $fila->fecha }}</td>
                    <td> {{ $fila->documento_transaccion_prefijo_consecutivo }} </td>
                    <td> 
                        {{ $fila->tercero_descripcion }}
                        <?php 
                            $referencia_tercero = $fila->get_datos_referencia_tercero();
                        ?>
                        @if( !is_null($referencia_tercero) )
                            <br>
                            &nbsp;&nbsp;&nbsp;&nbsp;<span style="color: gray;"> <b>{{ $referencia_tercero->etiqueta }}:</b>{{ $referencia_tercero->valor }} </span>
                        @endif
                    </td>
                    <td> {{ $caja }} {{ $cuenta_bancaria }} </td>
                    <td> {{ $detalle_operacion }} </td>
                    <td> {{ $fila->motivo_descripcion }} </td>
                    <td> {{ $entrada }} </td>
                    <td> {{ $salida}} </td>
                    <td> ${{ number_format( $saldo, 0, ',','.') }} </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"> &nbsp; </td>
                <td> ${{ number_format( $total_entradas, 0, ',','.') }} </td>
                <td> ${{ number_format( $total_salidas, 0, ',','.') }} </td>
                <td></td>
            </tr>
            <tr>
                <td colspan="7"> Total Diferencia </td>
                <td> ${{ number_format( $total_entradas + $total_salidas, 0, ',','.') }} </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
    