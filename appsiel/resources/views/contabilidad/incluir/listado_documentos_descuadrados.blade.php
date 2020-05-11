<br>
<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Cuadrade de Contabilidad vs Tesorería 
    <br> Estos documentos se generaron en el módulo de contabilidad y no se reflejan en el movimiento de Tesorería 
    
    <p style=" color: orange;"> 
        NOTA: Solo se puede actualizar el movimiento de Tesorería en una Caja. Depués se puede hacer un traslado de efectivo en el módulo para mover los valores a Cuentas bancarias.
    </p>
</div>

<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Fecha','Documento','Suma débitos','Suma créditos','Suma saldos']) }}
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
                <td style="text-align: right;"> $ {{ number_format( $linea->suma_debitos, 2, ',', '.') }} </td>
                <td style="text-align: right;"> $ {{ number_format( $linea->suma_creditos, 2, ',', '.') }} </td>
                <td style="text-align: right;"> $ {{ number_format( $linea->suma_saldos, 2, ',', '.') }} </td>
            </tr>
            <?php 
                $total_debito += $linea->suma_debitos;
                $total_credito += $linea->suma_creditos;
                $total_saldo += $linea->suma_saldos;
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"> &nbsp; </td>
            <td style="text-align: right;"> $ {{ number_format($total_debito, 2, ',', '.') }} </td>
            <td style="text-align: right;"> $ {{ number_format($total_credito, 2, ',', '.') }} </td>
            <td style="text-align: right;"> $ {{ number_format($total_saldo, 2, ',', '.') }} </td>
        </tr>
    </tfoot>
</table>