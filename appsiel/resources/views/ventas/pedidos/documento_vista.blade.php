<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Item','Producto','Cantidad','Vr. unitario','IVA','Total Bruto','Total']) }}
        <tbody>
            <?php 
            $i = 1;
            $total_cantidad = 0;
            $subtotal = 0;
            $total_impuestos = 0;
            $total_factura = 0;
            $array_tasas = [];
            ?>
            @foreach($doc_registros as $linea )
                <tr>
                    <td> {{ $i }} </td>
                    <td width="250px"> {{ $linea->producto_descripcion }} </td>
                    <td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) , 0, ',', '.') }} </td>
                    <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_unitario / (1+$linea->tasa_impuesto/100) * $linea->cantidad, 0, ',', '.') }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_total, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $i++;
                    $total_cantidad += $linea->cantidad;
                    $subtotal += (float)$linea->base_impuesto * (float)$linea->cantidad;
                    $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                    $total_factura += $linea->precio_total;

                    // Si la tasa no está en el array, se agregan sus valores por primera vez
                    if ( !isset( $array_tasas[$linea->tasa_impuesto] ) )
                    {
                        // Clasificar el impuesto
                        $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA '.$linea->tasa_impuesto.'%';
                        if ( $linea->tasa_impuesto == 0)
                        {
                            $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA 0%';
                        }
                        // Guardar la tasa en el array
                        $array_tasas[$linea->tasa_impuesto]['tasa'] = $linea->tasa_impuesto;


                        // Guardar el primer valor del impuesto y base en el array
                        $array_tasas[$linea->tasa_impuesto]['precio_total'] = (float)$linea->precio_total;
                        $array_tasas[$linea->tasa_impuesto]['base_impuesto'] = (float)$linea->base_impuesto * (float)$linea->cantidad;
                        $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] = (float)$linea->valor_impuesto * (float)$linea->cantidad;

                    }else{
                        // Si ya está la tasa creada en el array
                        // Acumular los siguientes valores del valor base y valor de impuesto según el tipo
                        $precio_total_antes = $array_tasas[$linea->tasa_impuesto]['precio_total'];
                        $array_tasas[$linea->tasa_impuesto]['precio_total'] = $precio_total_antes + (float)$linea->precio_total;
                        $array_tasas[$linea->tasa_impuesto]['base_impuesto'] += (float)$linea->base_impuesto * (float)$linea->cantidad;
                        $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] += (float)$linea->valor_impuesto * (float)$linea->cantidad;
                    }
                ?>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <tr>
            <td width="75%"> <b> &nbsp; </b> <br> </td>
            <td style="text-align: right; font-weight: bold;"> Subtotal: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;"> $ {{ number_format($subtotal, 2, ',', '.') }} </td>
        </tr>

        @foreach( $array_tasas as $key => $value )
            <tr>
                <td width="75%"> <b> &nbsp; </b> <br> </td>
                <td style="text-align: right; font-weight: bold;"> {{ $value['tipo'] }} </td>
                <td style="text-align: right; font-weight: bold;"> ${{ number_format( $value['valor_impuesto'], 0, ',', '.') }} </td>
            </tr>
        @endforeach
        <tr>
            <td width="75%"> <b> &nbsp; </b> <br> </td>
            <td style="text-align: right; font-weight: bold;"> Total factura: &nbsp; </td>
            <td style="text-align: right; font-weight: bold;"> $ {{ number_format($total_factura, 2, ',', '.') }} </td>
        </tr>
    </table>
</div>