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
                <th>Concepto</th>
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
                <td> {{ '$'.number_format($saldo_inicial, 0, ',','.') }}</td>
            </tr>
            <?php
                
                $saldo = $saldo_inicial;

                $total_entradas = 0;
                $total_salidas = 0;

                foreach( $movimiento as $fila )
                {

                    /*$modelo_crud_id = 0;
                    $url = '/';
                    if( $productos[$i]['core_tipo_transaccion_id'] != '' )
                    {
                        $modelo_crud_id = App\Sistema\TipoTransaccion::find( $productos[$i]['core_tipo_transaccion_id'] )->core_modelo_id;
                        $url = 'inventarios/'.$productos[$i]['documento_id'].'?id=8&id_modelo='.$modelo_crud_id.'&id_transaccion='.$productos[$i]['core_tipo_transaccion_id'];
                    }*/

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
                        
            ?>
                <tr>
                    <td> {{ $fila->fecha }}</td>
                    <td> {{ $fila->documento_transaccion_prefijo_consecutivo }} </td>
                    <td> {{ $fila->tercero_descripcion }} </td>
                    <td> {{ $fila->motivo_descripcion }} </td>
                    <td> {{ $entrada }} </td>
                    <td> {{ $salida}} </td>
                    <td> ${{ number_format( $saldo, 0, ',','.') }} </td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"> &nbsp; </td>
                <td> ${{ number_format( $total_entradas, 0, ',','.') }} </td>
                <td> ${{ number_format( $total_salidas, 0, ',','.') }} </td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>
    