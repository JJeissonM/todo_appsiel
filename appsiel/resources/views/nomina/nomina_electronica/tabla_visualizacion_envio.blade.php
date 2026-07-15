<div class="cuadro">
    <?php
        $total_devengos_periodo = 0;
        $total_deducciones_periodo = 0;

        foreach ($datos_vista as $comprobante_periodo) {
            foreach ($comprobante_periodo['accruals'] as $registro_periodo) {
                $amount = 0;
                if (isset($registro_periodo['amount'])) {
                    $amount = $registro_periodo['amount'];
                }
                if (isset($registro_periodo['amount-ns'])) {
                    $amount = $registro_periodo['amount-ns'];
                }

                if (isset($registro_periodo['cesantias-interest'])) {
                    $amount += $registro_periodo['cesantias-interest'];
                }

                $total_devengos_periodo += $amount;
            }

            $deductions_periodo = isset($comprobante_periodo['deductions']) ? $comprobante_periodo['deductions'] : [];
            foreach ($deductions_periodo as $registro_periodo) {
                $amount = 0;
                if (isset($registro_periodo['amount'])) {
                    $amount = $registro_periodo['amount'];
                }
                if (isset($registro_periodo['amount-ns'])) {
                    $amount = $registro_periodo['amount-ns'];
                }

                $total_deducciones_periodo += $amount;
            }
        }
    ?>

    <table class="table table-bordered" style="width:100%; margin-bottom: 15px; font-size: 13px;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th colspan="5" style="text-align: center; font-size: 16px;">
                    Envío Nómina Electrónica
                </th>
            </tr>
            <tr>
                <th style="text-align: center;">Desde</th>
                <th style="text-align: center;">Hasta</th>
                <th style="text-align: center;">Total devengos</th>
                <th style="text-align: center;">Total deducciones</th>
                <th style="text-align: center;">Saldo a pagar</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">{{ $lapso->fecha_inicial }}</td>
                <td style="text-align: center;">{{ $lapso->fecha_final }}</td>
                <td style="text-align: right;">{{ Form::TextoMoneda($total_devengos_periodo) }}</td>
                <td style="text-align: right;">{{ Form::TextoMoneda($total_deducciones_periodo) }}</td>
                <td style="text-align: right;">{{ Form::TextoMoneda($total_devengos_periodo - $total_deducciones_periodo) }}</td>
            </tr>
        </tbody>
    </table>

    @if (!empty($empleados_excluidos))
        <div class="alert alert-info">
            <strong>Empleados excluidos de la generación/envío:</strong>
            <ul style="margin-bottom: 0;">
                @foreach ($empleados_excluidos as $excluido)
                    <li>{{ $excluido }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @foreach ($datos_vista as $comprobante)

        <?php 
            $status = 'success';
            $hay_errores = false;
            
            $registros = $comprobante['accruals'];
            foreach ($registros as $registro )
            {
                if (isset($registro['status']) && $registro['status'] == 'error') 
                {
                    $hay_errores = true;
                }
            }
            
            $registros = isset($comprobante['deductions']) ? $comprobante['deductions'] : [];
            foreach ($registros as $registro )
            {
                if (isset($registro['status']) && $registro['status'] == 'error') 
                {
                    $hay_errores = true;
                }
            }

            $errores_empleado = isset($comprobante['employee_errors']) ? $comprobante['employee_errors'] : [];
            if (!empty($errores_empleado)) {
                $hay_errores = true;
            }
        ?>

        @include('nomina.reportes.tabla_datos_basicos_empleado',['empleado'=>$comprobante['empleado']])

        @if ( $hay_errores )
            <br>
            <div class="alert alert-warning">
                <i class="fa fa-warning"></i> Hay errores en algunos conceptos o datos del empleado. El Documento de soporte para este empleado NO será almacenado, ni Enviado.
                @if (!empty($errores_empleado))
                    <ul style="margin-bottom: 0;">
                        @foreach ($errores_empleado as $error_empleado)
                            <li>{{ $error_empleado['message'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <table style="width:100%; font-size: 12px;" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="border: 1px solid; text-align:center;"> Conceptos </th>
                    <th style="border: 1px solid; text-align:center;"> Días </th>
                    <th style="border: 1px solid; text-align:center;"> Devengo </th>
                    <th style="border: 1px solid; text-align:center;"> Deducción </th>
                </tr>
            </thead>
            <tbody>

                <?php
                    $registros = $comprobante['accruals'];
                    $total_devengos = 0;
                    $total_deducciones = 0;
                    $total_dias = 0;
                ?>
                @foreach ($registros as $registro )
                    <?php
                        $cantidad_horas = 0;
                        $deduccion = '';

                        $amount = 0;
                        if (isset($registro['amount'])) {
                            $amount = $registro['amount'];
                        }                    
                        if (isset($registro['amount-ns'])) {
                            $amount = $registro['amount-ns'];
                        } 
                        $cesantias_interest = isset($registro['cesantias-interest']) ? $registro['cesantias-interest'] : 0;

                        $devengo = Form::TextoMoneda( $amount );

                        $descripcion_concepto = $registro['code'];
                        if (isset($registro['concept-description']) && $registro['concept-description'] != '') {
                            $descripcion_concepto .= ' (' . $registro['concept-description'] . ')';
                        }

                        if ( isset($registro['days']))
                        {
                            $cantidad_horas = number_format( $registro['days'],2,',','.');
                        }

                        $class_tr = '';
                        if (isset($registro['status']) && $registro['status'] == 'error') {
                            $class_tr = 'danger';
                            $descripcion_concepto = $registro['message'];
                            $status = 'error';
                        }
                    ?>

                    <tr class="{{$class_tr}}">
                        <td> {{ $descripcion_concepto }} </td>
                        <td style="text-align: center;"> {{ $cantidad_horas }} </td>
                        <td> {{ $devengo }} </td>
                        <td> {{ $deduccion }} </td>
                    </tr>

                    @if($cesantias_interest != 0)
                        <tr>
                            <td>Intereses sobre cesantías</td>
                            <td style="text-align: center;">&nbsp;</td>
                            <td> {{ Form::TextoMoneda($cesantias_interest) }} </td>
                            <td>&nbsp;</td>
                        </tr>
                    @endif

                    <?php
                        $total_devengos += $amount + $cesantias_interest;
                    ?>
                @endforeach

                <?php 
                    $total_a_pagar = $total_devengos - $total_deducciones;
                ?>

                <?php
                    $registros = isset($comprobante['deductions']) ? $comprobante['deductions'] : [];
                ?>
                @foreach ($registros as $registro )
                    <?php
                        $cantidad_horas = 0;
                        $devengo = '';
                        
                        $amount = 0;
                        if (isset($registro['amount'])) {
                            $amount = $registro['amount'];
                        }                    
                        if (isset($registro['amount-ns'])) {
                            $amount = $registro['amount-ns'];
                        } 

                        $deduccion = Form::TextoMoneda( $amount );

                        $descripcion_concepto = $registro['code'];
                        if (isset($registro['concept-description']) && $registro['concept-description'] != '') {
                            $descripcion_concepto .= ' (' . $registro['concept-description'] . ')';
                        }

                        if ( isset($registro['days']))
                        {
                            $cantidad_horas = number_format( $registro['days'],2,',','.');
                        }

                        $class_tr = '';
                        if (isset($registro['status']) && $registro['status'] == 'error') {
                            $class_tr = 'danger';
                            $descripcion_concepto = $registro['message'];
                            $status = 'error';
                        }
                    ?>

                    <tr class="{{$class_tr}}">
                        <td> {{ $descripcion_concepto }} </td>
                        <td style="text-align: center;"> {{ $cantidad_horas }} </td>
                        <td> {{ $devengo }} </td>
                        <td> {{ $deduccion }} </td>
                    </tr>

                    <?php
                        $total_deducciones += $amount;
                    ?>
                @endforeach

                <?php 
                    $total_a_pagar = $total_devengos - $total_deducciones;
                ?>

                <tr>
                    <td>Totales</td>
                    <td style="text-align: center;"><hr> {{ $total_dias }} </td>
                    <td><hr> {{ Form::TextoMoneda($total_devengos) }} </td>
                    <td><hr> {{ Form::TextoMoneda($total_deducciones) }} </td>
                </tr>

                <tr>
                    <td colspan="4">&nbsp;</td>
                </tr>

                @if ( $total_a_pagar < 0 )
                    <tr>
                        <td colspan="4">
                            <div class="alert alert-warning" style="margin-bottom: 0;">
                                <i class="fa fa-warning"></i> Advertencia: el saldo a pagar de este empleado queda negativo porque las deducciones superan los devengos.
                            </div>
                        </td>
                    </tr>
                @endif

                <tr>
                    <td colspan="4">
                        <b>Saldo a pagar: </b> 
                        <span style="font-size: 1.2em;"> ${{ number_format( $total_a_pagar, 0, ',', '.') }}</span> ( {{ NumerosEnLetras::convertir( $total_a_pagar, 'pesos', false ) }})
                    </td>
                </tr>
            </tbody>
        </table>   
        
        <hr>
        
    @endforeach

    <input type="hidden" id="status" name="status" value="{{$status}}">
    <input type="hidden" id="arr_ids_docs_generados" name="arr_ids_docs_generados" value="{{$arr_ids_docs_generados}}">
</div>
