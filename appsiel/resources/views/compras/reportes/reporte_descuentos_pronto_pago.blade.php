<h3 style="width: 100%; text-align: center;">
    REPORTE DE DESCUENTOS POR PRONTO PAGO
</h3>
<hr>

<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th> Ítem </th>
                <th> Vlr. Descuento aplicado </th>
            </tr>
        </thead>
        <tbody>

            <?php

                $total_1_producto = 0;

            ?>

            @foreach( $items_con_descuento as $linea)
                
                <tr>
                    <td> {{ $linea['item'] }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea['valor_descuento'], 2, ',', '.') }} </td>
                </tr>

                <?php

                    $total_1_producto += $linea['valor_descuento'];
                ?>
            @endforeach

            <tr style=" background-color: #67cefb; font-weight: bolder;">
                <td> </td>
                <td style="text-align: right;"> ${{ number_format( $total_1_producto, 2, ',', '.') }} </td>
            </tr>
        </tbody>
    </table>
</div>