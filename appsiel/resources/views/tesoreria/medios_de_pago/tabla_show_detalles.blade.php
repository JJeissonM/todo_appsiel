<div class="row">
    
    <div class="col-md-6">
        <table class="table table-bordered">
            <tr>
                <td style="text-align: center; background-color: #ddd;"> 
                    <span style="text-align: right; font-weight: bold;">MEDIOS DE PAGO </span> 
                </td>
            </tr>
        </table>

        @include('tesoreria.medios_de_pago.tabla_show_detalles_pago_en_lineas_registros_documento')
        @include('tesoreria.medios_de_pago.tabla_show_detalles_pago_en_encabezado_documento')

    </div>

    <div class="col-md-6">
        <?php 
            $total = 0;
            $lineas_movimientos = $doc_encabezado->lineas_movimientos();
            $errores_movimientos = [];

            foreach ( $lineas_movimientos as $linea )
            {
                if ( (int)$linea->teso_motivo_id != 0 && is_null($linea->motivo) )
                {
                    $errores_movimientos[] = 'El motivo de tesorería ID ' . $linea->teso_motivo_id . ' no existe en la base de datos.';
                }

                if ( (int)$linea->teso_medio_recaudo_id != 0 && is_null($linea->medio_pago) )
                {
                    $errores_movimientos[] = 'El medio de recaudo ID ' . $linea->teso_medio_recaudo_id . ' no existe en la base de datos.';
                }
            }

            $errores_movimientos = array_unique($errores_movimientos);
        ?>
        @if( $vistaimprimir == '')
            <table class="table table-bordered">
                <tr>
                    <td style="text-align: center; background-color: #ddd;"> 
                        <span style="text-align: right; font-weight: bold;"> DETALLES DE MOVIMIENTOS </span> 
                    </td>
                </tr>
            </table>

            @if( !empty($errores_movimientos) )
                <div class="alert alert-danger">
                    <ul style="margin-bottom: 0;">
                        @foreach( $errores_movimientos as $error_movimiento )
                            <li>{{ $error_movimiento }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="table-responsive contenido">
                <table class="table table-bordered">
                    {{ Form::bsTableHeader(['Motivo','Medio de pago','Valor']) }}
                    <tbody>
                        @foreach ( $lineas_movimientos as $linea )
                            <?php
                                $motivo_descripcion = '';
                                $medio_pago_descripcion = '';

                                if ( !is_null($linea->motivo) )
                                {
                                    $motivo_descripcion = $linea->motivo->descripcion;
                                }

                                if ( !is_null($linea->medio_pago) )
                                {
                                    $medio_pago_descripcion = $linea->medio_pago->descripcion;
                                }
                            ?>
                            <tr>
                                <td> {{ $motivo_descripcion }} </td>
                                <td> {{ $medio_pago_descripcion }} </td>
                                <td align="right"> ${{ number_format($linea->valor_movimiento, 0, ',', '.') }} </td>
                            </tr>
                            <?php
                                $total += $linea->valor_movimiento;               
                            ?>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"> &nbsp; </td>
                            <td align="right">
                               $ {{ number_format($total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

</div>
