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
                <th> Espera (min.) </th>
            </tr>
        </thead>
        <tbody>
            @foreach($documentos_ventas as $documento)
                <?php  
                    $fechaAntigua  = $documento->created_at;
                    $fechaReciente = \Carbon\Carbon::now();

                    $cantidadMinutos = $fechaAntigua->diffInMinutes($fechaReciente);
                ?>
                <tr class="success">
                    <td> {{ $documento->fecha }} </td>
                    <td> {!! $documento->get_link_pedido() !!} </td>
                    <td> {{ $documento->cliente->tercero->descripcion }} </td>
                    <td> {{ $documento->vendedor->tercero->descripcion }} </td>
                    <td> {{ $documento->estado }} </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td> ${{ number_format( $documento->valor_total, 0, ',', '.') }} </td>
                    <td> {{ $documento->created_at }} </td>
                    <td> 
                        @if( $documento->created_at != $documento->updated_at)
                            {{ $documento->updated_at }} 
                        @endif
                    </td>
                    <td> 
                        @if( $documento->estado == 'Pendiente')
                            {{ $cantidadMinutos }} 
                        @endif
                    </td>
                </tr>
                <?php 
                    $lineas_registros = $documento->lineas_registros;
                ?>
                @foreach($lineas_registros as $linea)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td> {{ $linea->producto->id . ' ' . $linea->producto->descripcion . ' (' . $linea->producto->unidad_medida1 . ')' }} </td>
                        <td> {{ number_format( $linea->cantidad, 0, ',', '.') }} </td>
                        <td> ${{ number_format( $linea->precio_unitario, 0, ',', '.') }} </td>
                        <td> ${{ number_format( $linea->precio_total, 0, ',', '.') }} </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endforeach
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>