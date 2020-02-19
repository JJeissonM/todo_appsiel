<div style="text-align: center; font-weight: bold; width: 100%; border: solid 1px #ddd;"> Detalle de documentos cruzados </div>
<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Documentos Cartera','Documentos Recaudo/Anticipos','Valor cruzado']) }}
    <tbody>
        <?php        
            $total_valor = 0;
        ?>
        @foreach($registros as $key => $value )
            <tr>
                <td>
                 Documento: {{ $value['cartera'][0] }} <br/>
                 Fecha: {{ $value['cartera'][1] }} <br/>
                </td>
                <td>
                 Documento: {{ $value['recaudo'][0] }} <br/>
                 Fecha: {{ $value['recaudo'][1] }} <br/>
                </td>
                <td>
                 ${{ number_format($value['valor_pagado'], 0, ',', '.') }}
                </td>
            </tr>
            <?php 
                $total_valor += $value['valor_pagado'];
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2">&nbsp;</td>
            <td> ${{ number_format($total_valor, 0, ',', '.') }} </td>
        </tr>
    </tfoot>
</table>