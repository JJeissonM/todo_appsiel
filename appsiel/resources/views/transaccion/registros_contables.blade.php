@if( !empty( $registros_contabilidad ) )
    <div class="contenido" style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Registros contables</div>
    <div class="table-responsive contenido">
        <table class="table table-bordered table-striped">
            {{ Form::bsTableHeader(['Código','Cuenta','Débito','Crédito']) }}
            <tbody>
                @php
                    $total_valor_debito = 0;
                    $total_valor_credito = 0;
                @endphp
                @foreach( $registros_contabilidad as $fila )
                    <tr>
                        <td class="text-center"> {{ $fila['cuenta_codigo'] }}</td>
                        <td class="text-left"> {{ $fila['cuenta_descripcion'] }}</td>
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
    </div>
@endif