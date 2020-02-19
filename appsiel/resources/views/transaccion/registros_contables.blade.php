@if( !empty( $registros_contabilidad ) )
    <h4 style="text-align: center;">Registros contables</h4>
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Código','Cuenta','Débito','Crédito']) }}
        <tbody>
            @php
                $total_valor_debito = 0;
                $total_valor_credito = 0;
            @endphp
            @foreach( $registros_contabilidad as $fila )
                <tr>
                    <td> {{ $fila['cuenta_codigo'] }}</td>
                    <td> {{ $fila['cuenta_descripcion'] }}</td>
                    <td style="text-align: right;"> {{ number_format(  $fila['valor_debito'], 2, ',', '.') }}</td>
                    <td style="text-align: right;"> {{ number_format(  $fila['valor_credito'] * -1, 2, ',', '.') }}</td>
                </tr>
                @php
                    $total_valor_debito += $fila['valor_debito'];
                    $total_valor_credito += $fila['valor_credito'] * -1;
                @endphp
            @endforeach
        </tbody>
        <tfoot>            
                <tr>
                    <td colspan="2"> &nbsp; </td>
                    <td style="text-align: right;"> {{ number_format( $total_valor_debito, 2, ',', '.') }}</td>
                    <td style="text-align: right;"> {{ number_format( $total_valor_credito, 2, ',', '.') }}</td>
                </tr>
        </tfoot>
    </table>
@endif