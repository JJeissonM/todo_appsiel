<?php 
    $total = 0;
    $lineas_registros = $doc_encabezado->lineas_registros;
?>
@if( !empty( $lineas_registros->toArray() ) )
    <div class="table-responsive contenido">
        <table class="table table-bordered">
            {{ Form::bsTableHeader(['Motivo','Medio de pago','Caja/Cta. Bancaria','Valor']) }}
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
                            $caja_banco = 'Cta. ' . $linea->cuenta_bancaria->tipo_cuenta . ' ' . $linea->cuenta_bancaria->entidad_financiera->descripcion . ' No. ' . $linea->cuenta_bancaria->descripcion;
                        }
                    ?>
                    <tr>
                        <td style="text-align:left;"> {{ $linea->motivo->descripcion }} </td>
                        <td style="text-align:left;"> {{ $linea->medio_pago->descripcion }} </td>
                        <td style="text-align:left;"> {{ $caja_banco }} </td>
                        <td align="right"> ${{ number_format($linea->valor, 0, ',', '.') }} </td>
                    </tr>
                    <?php
                        $total += $linea->valor;               
                    ?>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"> &nbsp; </td>
                    <td align="right">
                       $ {{ number_format($total, 0, ',', '.') }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif