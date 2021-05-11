        

<div class="row">
    <div class="col-md-6">
        <?php 
            $total = 0;
            $lineas_registros = $doc_encabezado->lineas_registros;
        ?>
        <table class="table table-bordered">
            <tr>
                <td style="text-align: center; background-color: #ddd;"> 
                    <span style="text-align: right; font-weight: bold;"> DETALLE MEDIOS DE PAGO </span> 
                </td>
            </tr>
        </table>

        <div class="table-responsive">
            <table class="table table-bordered">
                {{ Form::bsTableHeader(['Medio de pago','Caja/Cta. Bancaria','Valor']) }}
                <tbody>
                    @foreach ( $lineas_registros as $linea )
                        <?php 
                            $caja_banco = '';
                            if ( !is_null($linea->caja) )
                            {
                                $caja_banco = $linea->caja->descripcion;
                            }
                            if ( !is_null($linea->cuenta_bancaria) )
                            {
                                $caja_banco = $linea->cuenta_bancaria->descripcion;
                            }
                        ?>
                        <tr>
                            <td> {{ $linea->medio_pago->descripcion }} </td>
                            <td> {{ $caja_banco }} </td>
                            <td align="right"> ${{ number_format($linea->valor, 0, ',', '.') }} </td>
                        </tr>
                        <?php
                            $total += $linea->valor;               
                        ?>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2"> &nbsp; </td>
                        <td align="right">
                           $ {{ number_format($total, 0, ',', '.') }} 
                           <br>
                           ({{ NumerosEnLetras::convertir($total,'pesos',false) }})
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="col-md-6">
        <?php 
            $total = 0;
            $lineas_movimientos = $doc_encabezado->lineas_movimientos();
        ?>
        <table class="table table-bordered">
            <tr>
                <td style="text-align: center; background-color: #ddd;"> 
                    <span style="text-align: right; font-weight: bold;"> DETALLES DE MOVIMIENTOS </span> 
                </td>
            </tr>
        </table>

        <div class="table-responsive">
            <table class="table table-bordered">
                {{ Form::bsTableHeader(['Motivo','Valor']) }}
                <tbody>
                    @foreach ( $lineas_movimientos as $linea )
                    <?php //dd($linea); ?>
                        <tr>
                            <td> {{ $linea->motivo->descripcion }} </td>
                            <td align="right"> ${{ number_format($linea->valor_movimiento, 0, ',', '.') }} </td>
                        </tr>
                        <?php
                            $total += $linea->valor_movimiento;               
                        ?>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td> &nbsp; </td>
                        <td align="right">
                           $ {{ number_format($total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
        

        