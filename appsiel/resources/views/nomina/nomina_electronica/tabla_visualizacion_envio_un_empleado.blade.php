<div class="cuadro">

    <?php 
        $status = 'success';
    ?>

    @include('nomina.reportes.tabla_datos_basicos_empleado',['empleado'=>$comprobante['empleado']])

    <table style="width:100%; font-size: 12px;" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="border: 1px solid; text-align:center;"> Conceptos </th>
                <th style="border: 1px solid; text-align:center;"> Horas </th>
                <th style="border: 1px solid; text-align:center;"> Devengo </th>
                <th style="border: 1px solid; text-align:center;"> Deducción </th>
            </tr>
        </thead>
        <tbody>

            <?php
                $registros = json_decode($comprobante['accruals'],true);
                $total_devengos = 0;
                $total_deducciones = 0;
                $total_horas = 0;
            ?>
            @foreach ($registros as $registro )
                <?php
                    $cantidad_horas = 0;
                    $deduccion = '';
                    $devengo = Form::TextoMoneda( $registro['amount'] );

                    $descripcion_concepto = $registro['code'];

                    if ( isset($registro['hours']))
                    {
                        $cantidad_horas = number_format( $registro['hours'],2,',','.');
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
                    <td style="text-align: center;"> {{ $cantidad_horas }} </td>
                    <td> {{ $devengo }} </td>
                    <td> {{ $deduccion }} </td>
                </tr>

                <?php
                    $total_devengos += $registro['amount'];
                ?>
            @endforeach

            <?php 
                $total_a_pagar = $total_devengos - $total_deducciones;
            ?>

            <?php
                $registros = json_decode($comprobante['deductions'],true);
            ?>
            @foreach ($registros as $registro )
                <?php
                    $cantidad_horas = 0;
                    $devengo = '';
                    $deduccion = Form::TextoMoneda( $registro['amount'] );

                    $descripcion_concepto = $registro['code'];

                    if ( isset($registro['hours']))
                    {
                        $cantidad_horas = number_format( $registro['hours'],2,',','.');
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
                    <td style="text-align: center;"> {{ $cantidad_horas }} </td>
                    <td> {{ $devengo }} </td>
                    <td> {{ $deduccion }} </td>
                </tr>

                <?php
                    $total_deducciones += $registro['amount'];
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