<br>
<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Cuadrade de Contabilidad vs Tesorería 
    <br> Estos documentos se generaron en el módulo de contabilidad y no se reflejan en el movimiento de Tesorería 
    
    <p style=" color: orange;"> 
        NOTA: Solo se puede actualizar el movimiento de Tesorería en una Caja. Depués se puede hacer un traslado de efectivo en el módulo para mover los valores a Cuentas bancarias.
    </p>
</div>

<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Fecha','Documento','Detalle operación','Cód. Cuenta','Mov. débito','Mov. crédito','Saldo','Actualizar movimiento de Tesorería']) }}
    <tbody>
        <?php 
        
            $total_debito = 0;
            $total_credito = 0;
            $total_saldo = 0;
            $i=0;
        ?>
        @foreach($registros as $linea )
            <tr>
                <td> {{ $linea->fecha }} </td>
                <td> {{ $linea->documento }} </td>
                <td> {{ $linea->detalle_operacion }} </td>
                <td> {{ $linea->codigo_cuenta }} </td>
                <td style="text-align: right;"> $ {{ number_format( $linea->valor_debito, 2, ',', '.') }} </td>
                <td style="text-align: right;"> $ {{ number_format( $linea->valor_credito, 2, ',', '.') }} </td>
                <td style="text-align: right;"> $ {{ number_format( $linea->valor_saldo, 2, ',', '.') }} </td>
                <td> 
                    {{ Form::select('caja_id',$cajas, null, []) }}
                    <button class="btn btn-primary btn-xs btn_actualizar_movimiento" title="Agregar este registro"> <i class="fa fa-refresh"></i> </button>
                </td>
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
            <td> &nbsp; </td>
        </tr>
    </tfoot>
</table>