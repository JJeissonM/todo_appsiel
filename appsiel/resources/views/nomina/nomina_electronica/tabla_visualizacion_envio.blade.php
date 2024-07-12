<div class="cuadro">
    <h5 style="text-align: center;"> 
        Envío Nómina Eectrónica &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <b>Desde: </b>{{ $lapso->fecha_inicial }} &nbsp;&nbsp;&nbsp; <b>Hasta:</b> {{ $lapso->fecha_final }}
    </h5>

    @foreach ($datos_vista as $comprobante)

        <?php 
            $status = 'success';
            $hay_errores = false;
            
            $registros = $comprobante['accruals'];
            foreach ($registros as $registro )
            {
                if ($registro['status'] == 'error') 
                {
                    $hay_errores = true;
                }
            }
            
            $registros = $comprobante['deductions'];
            foreach ($registros as $registro )
            {
                if ($registro['status'] == 'error') 
                {
                    $hay_errores = true;
                }
            }
        ?>

        @include('nomina.reportes.tabla_datos_basicos_empleado',['empleado'=>$comprobante['empleado']])

        @if ( $hay_errores )
            <br>
            <div class="alert alert-warning">
                <i class="fa fa-warning"></i> Hay errores en algunos conceptos. El Documento de soporte para este empleado NO será almacenado, ni Enviado.
            </div>
        @endif

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
                    $registros = $comprobante['accruals'];
                    $total_devengos = 0;
                    $total_deducciones = 0;
                    $total_horas = 0;
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

                        $devengo = Form::TextoMoneda( $amount );

                        $descripcion_concepto = $registro['code'];

                        if ( isset($registro['hours']))
                        {
                            $cantidad_horas = number_format( $registro['hours'],2,',','.');
                        }

                        $class_tr = '';
                        if ($registro['status'] == 'error') {
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
                        $total_devengos += $amount;
                    ?>
                @endforeach

                <?php 
                    $total_a_pagar = $total_devengos - $total_deducciones;
                ?>

                <?php
                    $registros = $comprobante['deductions'];
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

                        if ( isset($registro['hours']))
                        {
                            $cantidad_horas = number_format( $registro['hours'],2,',','.');
                        }

                        $class_tr = '';
                        if ($registro['status'] == 'error') {
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
        
        <hr>
        
    @endforeach

    <input type="hidden" id="status" name="status" value="{{$status}}">
    <input type="hidden" id="arr_ids_docs_generados" name="arr_ids_docs_generados" value="{{$arr_ids_docs_generados}}">
</div>