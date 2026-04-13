<br>
<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Listado de documentos Descuadrados 
    <br> Estos documentos tienen movimientos de débito y crédito que no cuadran, por lo tanto, el saldo no es cero. 
    
    <p style=" color: orange;"> 
        NOTA: Esto ocurre cuando hay alguna caida de Internet o bloqueo en la sincronización de datos.
    </p>
</div>

<div class="table-responsive">
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
</div>