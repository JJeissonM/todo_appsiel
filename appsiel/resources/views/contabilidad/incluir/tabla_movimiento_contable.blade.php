<div class="table-responsive">
    <table id="myTable" class="table table-striped" style="margin-top: -4px;">
        {{ Form::bsTableHeader(['Fecha','Cod. Cta.','Cuenta','CC/NIT','Tercero','Documento','Detalle','Mov. Débito','Mov. Crédito','Saldo']) }}
        <?php if( empty($agrupar_por_cuenta) ): ?>
        <tr>
            <td> {{ $fecha_desde }} </td>
            <td colspan="8"> &nbsp; </td>
            <td> {{ number_format( $saldo_inicial , 0, ',', '.') }} </td>
        </tr>
        <?php endif; ?>
            <?php
                $total_debito = 0;
                $total_credito = 0;
                $saldo = 0;
                $agrupar = !empty($agrupar_por_cuenta);
                $cuenta_actual_codigo = null;
                $subtotal_debito = 0;
                $subtotal_credito = 0;
                $saldo_cuenta = 0;
                $subtotal_debito_acumulado = 0;
                $subtotal_credito_acumulado = 0;
                $saldo_cuenta_anterior = 0;
                $codigo_cuenta_anterior = null;
                $descripcion_cuenta_anterior = '';
                $es_primera_linea = true;
            ?>
        <tbody>
            @foreach( $movimiento_contable AS $linea )
                <?php 
                    $debito = $linea->valor_debito;
                    $credito = $linea->valor_credito;

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

                    $saldo_inicial_cuenta = 0;
                    if ( $agrupar )
                    {
                        $saldo_inicial_cuenta = isset($saldos_iniciales[$linea->contab_cuenta_id]) ? (float)$saldos_iniciales[$linea->contab_cuenta_id] : 0;

                        if ( $cuenta_actual_codigo !== $cuenta_codigo )
                        {
                            $cuenta_actual_codigo = $cuenta_codigo;
                            $subtotal_debito = 0;
                            $subtotal_credito = 0;
                            $saldo_cuenta = $saldo_inicial_cuenta;
                        }

                        $saldo = $saldo_cuenta + $debito + $credito;
                    }
                    else
                    {
                        $saldo = $saldo_inicial + $debito + $credito;
                    }
                ?>
                <?php if( $agrupar && !$es_primera_linea && $codigo_cuenta_anterior !== $cuenta_codigo ): ?>
                    <tr style="background-color:#f5f5f5;font-weight:bold;">
                        <td colspan="7"> Subtotal <?php echo e($codigo_cuenta_anterior); ?> <?php echo e($descripcion_cuenta_anterior); ?> </td>
                        <td class="text-center"> {{ number_format($subtotal_debito_acumulado, 0, ',', '.') }} </td>
                        <td class="text-center"> {{ number_format($subtotal_credito_acumulado, 0, ',', '.') }} </td>
                        <td class="text-center"> {{ number_format($saldo_cuenta_anterior, 0, ',', '.') }} </td>
                    </tr>
                <?php endif; ?>
                <?php if( $agrupar && ($es_primera_linea || $codigo_cuenta_anterior !== $cuenta_codigo) ): ?>
                <tr style="background-color:#e8eaf6;font-weight:bold;">
                    <td colspan="2" class="text-center"> {{ $cuenta_codigo }}</td>
                    <td colspan="5"> {{ $cuenta_descripcion }}</td>
                    <td colspan="2" class="text-center"> Saldo inicial ({{ $fecha_desde }}) </td>
                    <td class="text-center"> {{ number_format( $saldo_inicial_cuenta , 0, ',', '.') }} </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td> {{ $linea->fecha }}</td>
                    <td class="text-center"> {{ $cuenta_codigo }}</td>
                    <td class="text-center"> {{ $cuenta_descripcion }}</td>
                    <td class="text-center"> {{ $tercero_numero_identificacion }}</td>
                    <td class="text-center"> {{ $tercero_descripcion }}</td>
                    <td> {{ $linea->tipo_documento_app->prefijo }} {{ $linea->consecutivo }}</td>
                    <td> {{ $linea->detalle_operacion }}</td>
                    <td class="text-center"> {{ number_format( $linea->valor_debito , 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $linea->valor_credito , 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $saldo , 0, ',', '.') }} </td>
                </tr>

                    <?php 
                        if ( $agrupar )
                        {
                            $subtotal_debito += $debito;
                            $subtotal_credito += $credito;
                            $saldo_cuenta = $saldo;
                            $subtotal_debito_acumulado = $subtotal_debito;
                            $subtotal_credito_acumulado = $subtotal_credito;
                            $saldo_cuenta_anterior = $saldo_cuenta;
                            $saldo_inicial = $saldo;
                        }
                        else
                        {
                            $saldo_inicial = $saldo;
                        }
                        $total_debito += $debito;
                        $total_credito += $credito;
                        $codigo_cuenta_anterior = $cuenta_codigo;
                        $descripcion_cuenta_anterior = $cuenta_descripcion;
                        $es_primera_linea = false;
                    ?>
            @endforeach
            <?php if( $agrupar && !$movimiento_contable->isEmpty() ): ?>
                <?php
                    $ultima_linea = $movimiento_contable->last();
                    $ultimo_codigo = !is_null($ultima_linea->cuenta) ? $ultima_linea->cuenta->codigo : '';
                    $ultima_descripcion = !is_null($ultima_linea->cuenta) ? $ultima_linea->cuenta->descripcion : 'Error en la cuenta.';
                ?>
                <tr style="background-color:#f5f5f5;font-weight:bold;">
                    <td colspan="7"> Subtotal {{ $ultimo_codigo }} {{ $ultima_descripcion }} </td>
                    <td class="text-center"> {{ number_format($subtotal_debito_acumulado, 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format($subtotal_credito_acumulado, 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format($saldo_cuenta_anterior, 0, ',', '.') }} </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7"> {{ $agrupar ? 'TOTAL MOVIMIENTO DEL PERIODO' : '' }} &nbsp; </td>
                <td class="text-center"> {{ number_format($total_debito, 0, ',', '.') }} </td>
                <td class="text-center"> {{ number_format($total_credito, 0, ',', '.') }} </td>
                <td class="text-center"> {{ $agrupar ? number_format(array_sum($saldos_iniciales) + $total_debito + $total_credito, 0, ',', '.') : number_format($saldo, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>