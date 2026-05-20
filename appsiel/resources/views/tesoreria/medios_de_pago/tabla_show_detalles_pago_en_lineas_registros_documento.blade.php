<?php 
    $total = 0;
    $lineas_registros = $doc_encabezado->lineas_registros;
    $errores_medios_pago = [];

    foreach ( $lineas_registros as $linea )
    {
        if ( (int)$linea->teso_motivo_id != 0 && is_null($linea->motivo) )
        {
            $errores_medios_pago[] = 'El motivo de tesorería ID ' . $linea->teso_motivo_id . ' no existe en la base de datos.';
        }

        if ( (int)$linea->teso_medio_recaudo_id != 0 && is_null($linea->medio_pago) )
        {
            $errores_medios_pago[] = 'El medio de recaudo ID ' . $linea->teso_medio_recaudo_id . ' no existe en la base de datos.';
        }
    }

    $errores_medios_pago = array_unique($errores_medios_pago);
?>
@if( !empty( $lineas_registros->toArray() ) )
    @if( !empty($errores_medios_pago) )
        <div class="alert alert-danger">
            <ul style="margin-bottom: 0;">
                @foreach( $errores_medios_pago as $error_medio_pago )
                    <li>{{ $error_medio_pago }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-responsive contenido">
        <table class="table table-bordered">
            {{ Form::bsTableHeader(['Motivo','Medio de pago','Caja/Cta. Bancaria','Valor']) }}
            <tbody>
                @foreach ( $lineas_registros as $linea )
                    <?php
                        $caja_banco = '';
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

                        if ( !is_null($linea->caja) )
                        {
                            $caja_banco = $linea->caja->descripcion;
                        }
                        if ( !is_null($linea->cuenta_bancaria) )
                        {
                            $entidad_financiera_descripcion = '';
                            if ( !is_null($linea->cuenta_bancaria->entidad_financiera) )
                            {
                                $entidad_financiera_descripcion = $linea->cuenta_bancaria->entidad_financiera->descripcion;
                            }

                            $caja_banco = 'Cta. ' . $linea->cuenta_bancaria->tipo_cuenta . ' ' . $entidad_financiera_descripcion . ' No. ' . $linea->cuenta_bancaria->descripcion;
                        }
                    ?>
                    <tr>
                        <td style="text-align:left;"> {{ $motivo_descripcion }} </td>
                        <td style="text-align:left;"> {{ $medio_pago_descripcion }} </td>
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
