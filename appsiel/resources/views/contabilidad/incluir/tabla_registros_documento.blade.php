<div style="text-align: center; font-weight: bold; width: 100%; background-color: #ddd;"> Registros del documento </div>

    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Tipo transacción','Cuenta',' NIT / Tercero','Detalle','Débito','Crédito']) }}
        <tbody>
            <?php 
            
                $total_debito=0;
                $total_credito=0;
                $i=0;
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td> {{ $linea->tipo_transaccion_linea }} </td>
                    <td> {{ $linea->cuenta }} </td>
                    <td> {{ number_format( $linea->numero_identificacion, 0, ',', '.') }} / {{ $linea->tercero }} </td>
                    <td> {{ $linea->detalle_operacion }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_debito, 2, ',', '.') }} </td>
                    <td style="text-align: right;"> $ {{ number_format( $linea->valor_credito, 2, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_debito+=$linea->valor_debito;
                    $total_credito+=$linea->valor_credito;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4"> &nbsp; </td>
                <td style="text-align: right;"> $ {{ number_format($total_debito, 2, ',', '.') }} </td>
                <td style="text-align: right;"> $ {{ number_format($total_credito, 2, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>