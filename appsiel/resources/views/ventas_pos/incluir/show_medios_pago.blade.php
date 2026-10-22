@if( isset($registros_tesoreria))
    @if( $registros_tesoreria != null )
        @if( (int)config('ventas_pos.mostrar_efectivo_recibio_y_cambio') )
            <div style="text-align: center; width: 100%; font-weight: bold; background: #ddd;">Pagos</div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" style="text-align: center; width: 100%;">
                    <thead>
                        <tr>
                            <th>Medio</th>
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
                                    $medio_pago = isset($teso_movim->medio_pago) ? $teso_movim->medio_pago : '';
                                    if ($medio_pago == '' && isset($teso_movim->teso_medio_recaudo_id)) {
                                        $partes_medio = explode('-', (string)$teso_movim->teso_medio_recaudo_id, 2);
                                        $medio_pago = isset($partes_medio[1]) ? $partes_medio[1] : $partes_medio[0];
                                    }
                                    if( isset($teso_movim->teso_caja_id) && $teso_movim->teso_caja_id != '0-' )
                                    {
                                        $partes_caja = explode('-', (string)$teso_movim->teso_caja_id, 2);
                                        $caja_banco = isset($partes_caja[1]) ? $partes_caja[1] : '';
                                    }
                                    
                                    if( isset($teso_movim->teso_cuenta_bancaria_id) && (int)$teso_movim->teso_cuenta_bancaria_id != 0 )
                                    {
                                        $arr_caja_banco = explode('-', $teso_movim->teso_cuenta_bancaria_id, 3);

                                        $caja_banco = isset($arr_caja_banco[1]) ? $arr_caja_banco[1] : '';
                                        if ( isset($arr_caja_banco[2]) ) {
                                            $caja_banco = $arr_caja_banco[1] . ' (' . $arr_caja_banco[2] . ')';
                                        }
                                    }
                                ?>
                                <td style="text-align: left;"> {{ $medio_pago }} </td>
                                <td style="text-align: left;"> {{ $caja_banco }} </td>
                                <td style="text-align: right;"> ${{ number_format( abs($teso_movim->valor), 0, ',', '.') }} </td>
                            </tr>
                            <?php 
                                $total_movimiento += abs($teso_movim->valor);
                            ?>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>&nbsp;</td>
                            <td style="font-weight: bold;">Total</td>
                            <td style="text-align: right; font-weight:bold;"> ${{ number_format($total_movimiento, 0, ',', '.') }} </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    @endif
@endif
