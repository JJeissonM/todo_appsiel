<!-- @ foreach ( $empleados as $empleado ) -->

    <div class="cuadro">
        <div style="text-align: center; font-size: 13px; font-weight: bold;">
            <span style="font-size:14px;">{{ $documento->empresa->descripcion }}</span>
            <br/> 
            Documento: {{ $documento->descripcion }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Fecha: {{ $documento->fecha }}
        </div>
        <div style="text-align: center; font-size: 13px; font-weight: bold; width: 100%;"> 
                Volante de nómina
        </div>

        @include('nomina.reportes.tabla_datos_basicos_empleado')

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
                    $registros = $documento->registros_liquidacion->where('core_tercero_id',$empleado->core_tercero_id)->sortByDesc('valor_devengo')->all();
                    $total_devengos = 0;
                    $total_deducciones = 0;
                    $total_horas = 0;
                ?>
                @foreach ($registros as $registro )
                    <?php
                        
                        $devengo = '';
                        $deduccion = '';

                        if ( $registro->valor_devengo != 0) 
                        {
                            $devengo = Form::TextoMoneda( $registro->valor_devengo );
                            $deduccion = '';
                        }

                        if ( $registro->valor_deduccion != 0) 
                        {
                            $devengo = '';
                            $deduccion = Form::TextoMoneda( $registro->valor_deduccion );
                        }

                        $descripcion_concepto = 'Registro > ' . $registro->id;
                        $modo_liquidacion_id = 0;

                        if ( !is_null( $registro->concepto ) )
                        {
                            $descripcion_concepto = $registro->concepto->descripcion;
                            $modo_liquidacion_id = $registro->concepto->modo_liquidacion_id;
                        }

                        $cantidad_horas = '';
                        if ( $registro->cantidad_horas != 0 )
                        {
                            $cantidad_horas = number_format( $registro->cantidad_horas,2,',','.');
                        }

                        $detalle = '';
                        if ( $registro->detalle != '' ) 
                        {
                            $detalle = $registro->detalle;
                        }
                    ?>

                    <tr>
                        <td> {{ $descripcion_concepto }} {{ $detalle }} </td>
                        <td style="text-align: center;"> {{ $cantidad_horas }} </td>
                        <td> {{ $devengo }} </td>
                        <td> {{ $deduccion }} </td>
                    </tr>

                    <?php
                        $total_devengos += $registro->valor_devengo;
                        $total_deducciones += $registro->valor_deduccion;

                        // 7: Tiempo NO Laborado, 1: tiempo laborado
                        if ( in_array( $modo_liquidacion_id, [1,7] ) ) {
                            $total_horas += $registro->cantidad_horas;
                        }
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
        <br/> 
        
        @include( 'nomina.reportes.firma_desprendibles_pagos' )
        
        {!! generado_por_appsiel() !!}
    </div>

    <div class="page-break"></div>
<!--  @ endforeach -->