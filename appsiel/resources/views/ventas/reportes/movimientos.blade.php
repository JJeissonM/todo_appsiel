<h3 style="width: 100%; text-align: center;">
    REPORTE DE MOVIMIENTOS DE VENTAS 
</h3>
<hr>

<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        {{ Form::bsTableHeader(['Fecha', 'Doc.', 'NIT/Cliente', 'Vend.', 'Zona', 'Forma Pago', 'Bod.', 'Mot.', 'Item', 'Cant.', 'P. Unit.', 'Base Imp. Tot.',  'Tasa Imp.', 'Imp. Tot.', 'P. Tot.','Tasa. Dcto.', 'Dcto. Tot.']) }}
        <tbody>
            <?php
                $suma_precio_total = 0;
                $suma_base_impuesto = 0;
                $suma_impuesto_total = 0;
                $suma_descuento_total = 0;
            ?>

            @foreach( $movimiento as $linea_movimiento )
                <tr>
                    <td> {{ $linea_movimiento->fecha }} </td>
                    <td> {{ $linea_movimiento->get_label_documento() }} </td>
                    <td> {{ $linea_movimiento->cliente->tercero->numero_identificacion }}  / {{ $linea_movimiento->cliente->tercero->descripcion }} </td>
                    <?php  
                        $vendedor = 'null';
                        if ($linea_movimiento->vendedor != null) {
                            if ($linea_movimiento->vendedor->tercero != null) {
                                $vendedor = $linea_movimiento->vendedor->tercero->descripcion;
                            }
                        }
                    ?>
                    <td> {{ $vendedor }} </td>
                    <?php  
                        $zona = 'null';
                        if ($linea_movimiento->zona != null) {
                            $zona = $linea_movimiento->zona->descripcion;
                        }
                    ?>
                    <td> {{ $zona }} </td>
                    <td> {{ $linea_movimiento->forma_pago }} </td>
                    <?php  
                        $bodega = 'null';
                        if ($linea_movimiento->bodega != null) {
                            $bodega = $linea_movimiento->bodega->descripcion;
                        }
                    ?>
                    <td> {{ $bodega }} </td>
                    <?php  
                        $motivo = 'null';
                        if ($linea_movimiento->motivo != null) {
                            $motivo = $linea_movimiento->motivo->descripcion;
                        }
                    ?>
                    <td> {{ $motivo }} </td>
                    <td> {{ $linea_movimiento->producto->id }} {{ $linea_movimiento->producto->descripcion }} </td>
                    <td> {{ number_format( $linea_movimiento->cantidad, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $linea_movimiento->precio_total, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $linea_movimiento->base_impuesto_total, 2, ',', '.') }} </td>
                    <td> {{ $linea_movimiento->tasa_impuesto }}% </td>
                    <td> ${{ number_format( $linea_movimiento->valor_impuesto * $linea_movimiento->cantidad, 2, ',', '.') }} </td>
                    <td> ${{ number_format( $linea_movimiento->precio_unitario, 2, ',', '.') }} </td>
                    <td> {{ $linea_movimiento->tasa_descuento }}% </td>
                    <td> ${{ number_format( $linea_movimiento->valor_total_descuento, 2, ',', '.') }} </td>
                </tr>
                <?php
                    $suma_precio_total += $linea_movimiento->precio_total;
                    $suma_base_impuesto += $linea_movimiento->base_impuesto_total;
                    $suma_impuesto_total += $linea_movimiento->valor_impuesto * $linea_movimiento->cantidad;
                    $suma_descuento_total += $linea_movimiento->valor_total_descuento;
                ?>
            @endforeach

            <tr style=" background-color: #67cefb; font-weight: bolder;">
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> &nbsp; </td>
                <td> ${{ number_format( $suma_base_impuesto, 2, ',', '.') }} </td>
                <td> &nbsp; </td>
                <td> ${{ number_format( $suma_impuesto_total, 2, ',', '.') }} </td>
                <td> ${{ number_format( $suma_precio_total, 2, ',', '.') }} </td>
                <td> &nbsp; </td>
                <td> ${{ number_format( $suma_descuento_total, 2, ',', '.') }} </td>
            </tr>
        </tbody>
    </table>
</div>