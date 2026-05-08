<div class="cuadro">

    <?php 
        $status = 'success';
        $errores_empleado = isset($comprobante['employee_errors']) ? $comprobante['employee_errors'] : [];
        if (!empty($errores_empleado)) {
            $status = 'error';
        }
    ?>

    @include('nomina.reportes.tabla_datos_basicos_empleado',['empleado'=>$comprobante['empleado']])

    @if (!empty($errores_empleado))
        <br>
        <div class="alert alert-warning">
            <i class="fa fa-warning"></i> Hay errores en los datos del empleado.
            <ul style="margin-bottom: 0;">
                @foreach ($errores_empleado as $error_empleado)
                    <li>{{ $error_empleado['message'] }}</li>
                @endforeach
            </ul>
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
                $registros = json_decode($comprobante['accruals'],true);
                if (!is_array($registros)) {
                    $registros = [];
                }
                $total_devengos = 0;
                $total_deducciones = 0;
                $total_horas = 0;
            ?>
            @foreach ($registros as $registro )
                <?php
                    $descripcion_concepto = $registro['code'];

                    $cantidad_dias = 0;
                    $deduccion = '';
                    
                    $amount = 0;
                    if (isset($registro['amount'])) {
                        $amount = $registro['amount'];
                    }
                    if (isset($registro['amount-ns'])) {
                        $amount = $registro['amount-ns'];
                    }

                    $cesantias_interest = isset($registro['cesantias-interest']) ? $registro['cesantias-interest'] : 0;

                    if ($amount == 0 && $cesantias_interest != 0) { // No se están pagando Cesantías
                        $descripcion_concepto .= ' (Intereses)';
                        $amount = $cesantias_interest;
                    }

                    if ($cesantias_interest != 0) {
                        $descripcion_concepto .= ' (Cesantías + Intereses)';
                    }                                

                    $devengo = Form::TextoMoneda( $amount );

                    if ( isset($registro['days']))
                    {
                        $cantidad_dias = number_format( $registro['days'],2,',','.');
                    }

                    $class_tr = '';
                    if (isset($registro['status'])) {
                        if ($registro['status'] == 'error') {
                            $class_tr = 'danger';
                            $descripcion_concepto = $registro['message'];
                            $status = 'error';
                        }
                    }
                    
                ?>

                <tr class="{{$class_tr}}">
                    <td> {{ $descripcion_concepto }} </td>
                    <td style="text-align: center;"> {{ $cantidad_dias }} </td>
                    <td> {{ $devengo }} </td>
                    <td> {{ $deduccion }} </td>
                </tr>

                <?php
                    $total_devengos += $amount;
                ?>
            @endforeach

            <?php 
                $total_a_pagar = $total_devengos - $total_deducciones;
            ?>

            <?php
                $registros = json_decode($comprobante['deductions'],true);
                if (!is_array($registros)) {
                    $registros = [];
                }
            ?>
            @foreach ($registros as $registro )
                <?php

                    $amount = 0;
                    if (isset($registro['amount'])) {
                        $amount = $registro['amount'];
                    }                    
                    if (isset($registro['amount-ns'])) {
                        $amount = $registro['amount-ns'];
                    } 

                    $cantidad_dias = 0;
                    $devengo = '';
                    $deduccion = Form::TextoMoneda( $amount );

                    $descripcion_concepto = $registro['code'];

                    if ( isset($registro['days']))
                    {
                        $cantidad_dias = number_format( $registro['days'],2,',','.');
                    }

                    $class_tr = '';
                    if (isset($registro['status'])) {
                        if ($registro['status'] == 'error') {
                            $class_tr = 'danger';
                            $descripcion_concepto = $registro['message'];
                            $status = 'error';
                        }
                    }
                ?>

                <tr class="{{$class_tr}}">
                    <td> {{ $descripcion_concepto }} </td>
                    <td style="text-align: center;"> {{ $cantidad_dias }} </td>
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
                <td style="text-align: center;"><hr> {{ $total_horas }} </td>
                <td><hr> {{ Form::TextoMoneda($total_devengos) }} </td>
                <td><hr> {{ Form::TextoMoneda($total_deducciones) }} </td>
            </tr>

            <tr>
                <td colspan="4">&nbsp;</td>
            </tr>

            <tr>
                <td colspan="4">
                    <b>Saldo a pagar: </b> 
                    <span style="font-size: 1.2em;"> ${{ number_format( $total_a_pagar, 0, ',', '.') }}</span> ( {{ NumerosEnLetras::convertir( $total_a_pagar, 'pesos', false ) }})
                </td>
            </tr>
        </tbody>
    </table>
</div>
