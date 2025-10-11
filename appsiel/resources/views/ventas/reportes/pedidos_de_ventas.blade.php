<h3 style="width: 100%; text-align: center;">
    Pedidos de ventas 
</h3>
<span style="color: rgb(58, 58, 58);">{!! $mensaje !!}</span> 
<hr>

<div class="table-responsive">
    <table id="myTable" class="table">
        <thead>
            <tr>
                <th> Fecha </th>
                <th> Doc. Ventas </th>
                <th> Cliente </th>
                <th> Vendedor </th>
                <th> Estado </th>
                <th> Producto </th>
                <th> Cant. </th>
                <th> Precio </th>
                <th> Total </th>
                <th> Creado en </th>
                <th> Actualizado en </th>

                @if($divisor_minutos == 1)
                    <th> Creado (mín.) </th>
                @else
                    <th> Creado (días) </th>
                @endif
            </tr>
        </thead>
        <tbody>
            <?php 
                $total_cantidad = 0;
                $total_precio = 0;
            ?>
            @foreach($documentos_ventas as $documento)
                <?php  
                    $fechaAntigua  = $documento->created_at;
                    $fechaReciente = \Carbon\Carbon::now();

                    $cantidadMinutos = round( $fechaAntigua->diffInMinutes($fechaReciente) / $divisor_minutos, 0);
                ?>
                <?php 
                    $lineas_registros = $documento->lineas_registros;
                ?>
                @foreach($lineas_registros as $linea)
                    <tr>
                        <td> {{ $documento->fecha }} </td>
                        <td> {!! $documento->get_link_pedido() !!} </td>
                        <td> {{ $documento->cliente->tercero->descripcion }} </td>
                        <td> {{ $documento->vendedor->tercero->descripcion }} </td>
                        <td> {{ $documento->estado }} </td>
                        <td> {{ str_replace('(UND)', '', $linea->producto->get_value_to_show() ) }} </td>
                        <td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                        <td align="right"> ${{ number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                        <td align="right"> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
                        <td> {{ $documento->created_at }} </td>
                        <td> 
                            @if( $documento->created_at != $documento->updated_at)
                                {{ $documento->updated_at }} 
                            @endif
                        </td>
                        <td align="center"> 
                            @if( $documento->estado == 'Pendiente')
                                {{ $cantidadMinutos }} 
                            @endif
                        </td>
                    </tr>
                    <?php 
                        $total_cantidad += $linea->cantidad;
                        $total_precio += $linea->precio_total;
                    ?>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td>{{ number_format( $total_cantidad, 0, ',', '.') }}</td>
                <td></td>
                <td align="right">${{ number_format( $total_precio, 0, ',', '.') }}</td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>
</div>