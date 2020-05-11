<br>
<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Cuadrade de Contabilidad vs Tesorería 
    <br> Estos documentos se generaron en el módulo de contabilidad y no se reflejan en el movimiento de Tesorería 
    
    <p style=" color: orange;"> 
        NOTA: Solo se puede actualizar el movimiento de Tesorería en una Caja. Depués se puede hacer un traslado de efectivo en el módulo para mover los valores a Cuentas bancarias.
    </p>
</div>

<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Fecha','Documento','Detalle operación','Cód. Cuenta','Mov. débito','Mov. crédito','Saldo','core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','core_tercero_id','teso_caja_id','teso_motivo_id','valor_movimiento']) }}
    <tbody>
        <?php 
        
            $total_debito = 0;
            $total_credito = 0;
            $total_saldo = 0;
            $i=0;
        ?>
        @foreach($registros as $linea )
            @if( $linea->valor_saldo < -1 || $linea->valor_saldo > 1)
                <?php 
                    $motivo_id = 23; // Recaudo
                    if( $linea->valor_saldo < 0 )
                    {
                        $motivo_id = 27; // Diversos - Otros
                    }
                    /*$motivos = [];
                    if( $linea->valor_saldo < 0 )
                    {
                        //$motivos = $motivos_tesoreria_salida;
                        $motivos = array_keys( $motivos_tesoreria_salida );
                    }else{
                        $motivos = array_keys( $motivos_tesoreria_entrada );
                    }

                    dd( $registros ); 
                    $cajas = array_keys( $cajas );
                    array_shift($cajas);*/
                ?>
                <tr data-linea="{{$linea}}">
                    <td> {{ $linea->fecha }} </td>
                    <td> {{ $linea->documento }} </td>
                    <td> {{ $linea->detalle_operacion }} </td>
                    <td> {{ $linea->codigo_cuenta }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_debito, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_credito, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_saldo, 2, ',', '.') }} </td>
                    <td> {{ $linea->core_tipo_transaccion_id }} </td>
                    <td> {{ $linea->core_tipo_doc_app_id }} </td>
                    <td> {{ $linea->consecutivo }} </td>
                    <td> {{ $linea->core_tercero_id }} </td>
                    <td> 1 </td>
                    <td> {{ $motivo_id }} </td>
                    <td> {{ $linea->valor_saldo }} </td>
                    <!--<td> 

                         { { Form::select('motivo_id',$motivos, null, []) }}
                        { { Form::select('caja_id',$cajas, 1, []) }}
                        <button class="btn btn-primary btn-xs btn_actualizar_movimiento" onclick="actualizar_movimiento(this);" title="Agregar este registro"> <i class="fa fa-refresh"></i> </button>
                        
                    </td>-->
                </tr>
            @endif
            
            <?php 
                $total_debito += $linea->valor_debito;
                $total_credito += $linea->valor_credito;
                $total_saldo += $linea->valor_saldo;
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4"> &nbsp; </td>
            <td style="text-align: right;"> $ {{ number_format($total_debito, 2, ',', '.') }} </td>
            <td style="text-align: right;"> $ {{ number_format($total_credito, 2, ',', '.') }} </td>
            <td style="text-align: right;"> $ {{ number_format($total_saldo, 2, ',', '.') }} </td>
            <td colspan="7"> &nbsp; </td>
        </tr>
    </tfoot>
</table>

<script>
    function actualizar_movimiento( btn )
    {
        alert(btn.closest('tr').getAttribute('data-linea') );
    }
</script>