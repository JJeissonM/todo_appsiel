<br>
<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Cuadrade de Contabilidad vs Tesorería 
    <br> Estos documentos se generaron en el módulo de contabilidad y no se reflejan en el movimiento de Tesorería
</div>

@if( isset($registros_truncados) && $registros_truncados )
    <div class="alert alert-warning" style="margin-top:10px;">
        Se muestran solo los primeros {{ number_format($limite_registros, 0, ',', '.') }} registros para evitar agotamiento de memoria. Ajuste el rango de fechas para un detalle completo.
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['ID Mov. Contab.','Fecha','Documento','Detalle operación','Cód. Cuenta','Mov. débito','Mov. crédito','Saldo','core_tipo_transaccion_id','core_tipo_doc_app_id','consecutivo','core_tercero_id','teso_caja_id','teso_cuenta_bancaria_id','valor_movimiento']) }}
        <tbody>
            <?php 
            
                $total_debito = 0;
                $total_credito = 0;
                $total_saldo = 0;
                $i=0;
            ?>
            @foreach($registros as $linea )
                <tr>
                    <td> {{ $linea->id }} </td>
                    <td> {{ $linea->fecha }} </td>
                    <td class="text-center"> {{ $linea->documento }} </td>
                    <td> {{ $linea->detalle_operacion }} </td>
                    <td class="text-center"> {{ $linea->codigo_cuenta }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_debito, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_credito, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_saldo, 2, ',', '.') }} </td>
                    <td> {{ $linea->core_tipo_transaccion_id }} </td>
                    <td> {{ $linea->core_tipo_doc_app_id }} </td>
                    <td class="text-center"> {{ $linea->consecutivo }} </td>
                    <td> {{ $linea->core_tercero_id }} </td>
                    <td> {{ $linea->teso_caja_id }} </td>
                    <td> {{ $linea->teso_cuenta_bancaria_id }} </td>
                    <td> {{ $linea->valor_saldo }} </td>
                </tr>
                
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
</div>
