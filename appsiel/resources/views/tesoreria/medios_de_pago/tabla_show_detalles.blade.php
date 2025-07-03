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
        ?>
        @if( $vistaimprimir == '')
            <table class="table table-bordered">
                <tr>
                    <td style="text-align: center; background-color: #ddd;"> 
                        <span style="text-align: right; font-weight: bold;"> DETALLES DE MOVIMIENTOS </span> 
                    </td>
                </tr>
            </table>

            <div class="table-responsive contenido">
                <table class="table table-bordered">
                    {{ Form::bsTableHeader(['Motivo','Medio de pago','Valor']) }}
                    <tbody>
                        @foreach ( $lineas_movimientos as $linea )
                            <?php
                                $medio_pago_descripcion = '';
                                if ( !is_null($linea->medio_pago) )
                                {
                                    $medio_pago_descripcion = $linea->medio_pago->descripcion;
                                }
                            ?>
                            <tr>
                                <td> {{ $linea->motivo->descripcion }} </td>
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