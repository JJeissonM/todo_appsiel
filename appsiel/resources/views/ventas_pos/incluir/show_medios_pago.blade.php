@if( isset($registros_tesoreria))
    @if( $registros_tesoreria != null )
        @if( (int)config('ventas_pos.mostrar_efectivo_recibio_y_cambio') )
            <div style="text-align: center; width: 100%; font-weight: bold; background: #ddd;">Medios de Pago</div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" style="text-align: center; width: 100%;">
                    <thead>
                        <tr>
                            <th>Caja/Banco</th>
                            <th>Valor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        
                            $total_movimiento = 0;

                        ?>
                        @foreach($registros_tesoreria as $teso_movim )
                            <tr>
                                <?php
                                
                                    $caja_banco = '';
                                    if( $teso_movim->teso_caja_id != '0-' )
                                    {
                                        $caja_banco = explode('-', $teso_movim->teso_caja_id )[1];
                                    }
                                    
                                    if( $teso_movim->teso_cuenta_bancaria_id != '0-' )
                                    {
                                        $arr_caja_banco = explode('-', $teso_movim->teso_cuenta_bancaria_id );

                                        $caja_banco = $arr_caja_banco[1];
                                        if ( isset($arr_caja_banco[2]) ) {
                                            $caja_banco = $arr_caja_banco[1] . ' (' . $arr_caja_banco[2] . ')';
                                        }
                                    }
                                ?>
                                <td style="text-align: left;"> {{ $caja_banco }} </td>
                                <td style="text-align: right;"> ${{ number_format( abs($teso_movim->valor), 2, ',', '.') }} </td>
                            </tr>
                            <?php 
                                $total_movimiento += abs($teso_movim->valor);
                            ?>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>&nbsp;</td>
                            <td style="text-align: right; font-weight:bold;"> ${{ number_format($total_movimiento, 2, ',', '.') }} </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @endif
@endif