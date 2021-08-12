<div class="table-responsive">
    <table id="myTable" class="table table-striped" style="margin-top: -4px;">
        {{ Form::bsTableHeader(['Fecha','Cuenta','Tercero','Documento','Detalle','Mov. Débito','Mov. Crédito','Saldo']) }}
        <tr>
            <td> {{ $fecha_desde }} </td>
            <td colspan="6"> &nbsp; </td>
            <td> {{ number_format( $saldo_inicial , 0, ',', '.') }} </td>
        </tr>
            <?php
                $total_debito = 0;
                $total_credito = 0;
                $saldo = 0;
            ?>
        <tbody>
            @foreach( $movimiento_contable AS $linea )
                <?php 
                    $debito = $linea->valor_debito;
                    $credito = $linea->valor_credito;
                    $saldo = $saldo_inicial + $debito + $credito;

                    $tercero_numero_identificacion = 0;
                    $tercero_descripcion = '';
                    if( !is_null( $linea->tercero ) )
                    {
                        $tercero_numero_identificacion = $linea->tercero->numero_identificacion;
                        $tercero_descripcion = $linea->tercero->descripcion;
                    }

                    $cuenta_codigo = '';
                    $cuenta_descripcion = 'Error en la cuenta. ID MOV. = ' . $linea->id;
                    if ( !is_null( $linea->cuenta ) )
                    {
                        $cuenta_codigo = $linea->cuenta->codigo;
                        $cuenta_descripcion = $linea->cuenta->descripcion;
                    }
                ?>
                <tr>
                    <td> {{ $linea->fecha }}</td>
                    <td class="text-center"> {{ $cuenta_codigo }} {{ $cuenta_descripcion }}</td>
                    <td class="text-center"> {{ $tercero_numero_identificacion }} {{ $tercero_descripcion }}</td>
                    <td> {{ $linea->tipo_documento_app->prefijo }} {{ $linea->consecutivo }}</td>
                    <td> {{ $linea->detalle_operacion }}</td>
                    <td class="text-center"> {{ number_format( $linea->valor_debito , 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $linea->valor_credito , 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $saldo , 0, ',', '.') }} </td>
                </tr>

                    <?php 
                        $saldo_inicial = $saldo;
                        $total_debito += $debito;
                        $total_credito += $credito;
                    ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5"> &nbsp; </td>
                <td class="text-center"> {{ number_format($total_debito, 0, ',', '.') }} </td>
                <td class="text-center"> {{ number_format($total_credito, 0, ',', '.') }} </td>
                <td class="text-center"> {{ number_format($saldo, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>