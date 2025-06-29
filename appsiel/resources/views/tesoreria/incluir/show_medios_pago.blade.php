@if( isset($registros_tesoreria))
    @if( $registros_tesoreria != null )
        <div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Medios de Pago</div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" style="text-align: center; width: 100%;">
                {{ Form::bsTableHeader(['Caja/Banco','Monto']) }}
                <tbody>
                    <?php 
                    
                        $total_movimiento = 0;

                    ?>
                    @foreach($registros_tesoreria as $teso_movim )
                        <tr>
                            <?php
                                $caja_banco = '';
                                if( $teso_movim->caja != null )
                                {
                                    $caja_banco = $teso_movim->caja->descripcion;
                                }
                                
                                if( $teso_movim->cuenta_bancaria != null )
                                {
                                    $caja_banco = 'Cuenta ' . $teso_movim->cuenta_bancaria->tipo_cuenta . ' ' . $teso_movim->cuenta_bancaria->entidad_financiera->descripcion . ' No. ' . $teso_movim->cuenta_bancaria->descripcion;
                                }
                            ?>
                            <td> {{ $caja_banco }} </td>
                            <td class="text-right"> ${{ number_format( abs($teso_movim->valor_movimiento), 2, ',', '.') }} </td>
                        </tr>
                        <?php 
                            $total_movimiento += abs($teso_movim->valor_movimiento);
                        ?>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td style="border-top: solid 3px #ddd;">&nbsp;</td>
                        <td class="text-right" style="border-top: solid 3px #ddd;"> ${{ number_format($total_movimiento, 2, ',', '.') }} </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
@endif