<h3 style="width: 100%; text-align: center;">
    REPORTE DE DESCUENTOS POR PRONTO PAGO
</h3>
<hr>

<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <thead>
            <tr>
                <th> Ítem </th>
                <th> Doc. Pago </th>
                <th> Fecha Doc. Pago </th>
                <th> Fact. Compras </th>
                <th>  % participación Ítem </th>
                <th> Vlr. Descuento aplicado </th>
            </tr>
        </thead>
        <tbody>

            <?php
                $total_1_producto = 0;
            ?>

            @foreach( $items_con_descuento as $linea)

                <tr>
                    <td> {{ $linea->item }} </td>
                    <td> {{ $linea->doc_pago }} </td>
                    <td> {{ $linea->fecha_pago }} </td>
                    <td> {{ $linea->factura_compras }} </td>
                    <td style="text-align: center;"> {{ $linea->porcentaje_participacion_item }} </td>
                    <td style="text-align: right;"> ${{ number_format( $linea->valor_descuento, 2, ',', '.') }} </td>
                </tr>

                <?php

                    $total_1_producto += $linea->valor_descuento;
                ?>
            @endforeach

            <tr style=" background-color: #67cefb; font-weight: bolder;">
                <td colspan="5"> </td>
                <td style="text-align: right;"> ${{ number_format( $total_1_producto, 2, ',', '.') }} </td>
            </tr>
        </tbody>
    </table>
</div>