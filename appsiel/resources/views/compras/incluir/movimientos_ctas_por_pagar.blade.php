<h3 style="width: 100%; text-align: center;"> Movimientos de Cuentas por Pagar (CxP) </h3>
<h4 style="width: 100%; text-align: center;">Tercero: {{ $tercero->get_label_to_show() }}</h4>
<div class="alert alert-warning">
    <strong>Advertencia!</strong> 
    <br>
    Los movimientos solo reflejan los saldos entre las fechas seleccionadas.
</div>
<hr>
<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th> Fecha </th>
                <th> Documento </th>
                <th> Estado </th>
                <th> Cartera </th>
                <th> A favor </th>
                <th> Saldo </th>
            </tr>
        </thead>
        <tbody>
            <?php
                $j = 1;
                $total_valor_cartera = 0;
                $total_valor_a_favor = 0;
                $total_saldo_pendiente = 0;
            ?>                    
                    
            @foreach ( $data_ordered as $linea_movim )
                <?php
                    $total_saldo_pendiente += ($linea_movim->valor_cartera - $linea_movim->valor_a_favor);
                ?>    
                
                <tr class="fila-{{$j}}">
                    <td> {{ $linea_movim->fecha }} </td>
                    <td> {!! $linea_movim->documento !!} </td>
                    <td> {{ $linea_movim->estado }} </td>
                    <td style="text-align: right;"> ${{ number_format($linea_movim->valor_cartera, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format($linea_movim->valor_a_favor, 0, ',', '.') }} </td>
                    <td style="text-align: right;"> ${{ number_format( $total_saldo_pendiente, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $j++;
                    if ($j==3) {
                        $j=1;
                    }
                    $total_valor_cartera += $linea_movim->valor_cartera;
                    $total_valor_a_favor += $linea_movim->valor_a_favor;
                ?>
            @endforeach
            

            <tr  class="fila-{{$j}}" >
                <td colspan="3"> &nbsp; </td>
                <td style="text-align: right;"> ${{ number_format($total_valor_cartera, 0, ',', '.') }} </td>
                <td style="text-align: right;"> ${{ number_format($total_valor_a_favor, 0, ',', '.') }} </td>
                <td style="text-align: right;"> ${{ number_format($total_saldo_pendiente, 0, ',', '.') }} </td>
            </tr>
        </tbody>
    </table>
    <div style="width: 100%; text-align: right; margin-top:20px;">
        Generado el {{ date('d-m-Y, H:m A')}}
    </div>
</div>