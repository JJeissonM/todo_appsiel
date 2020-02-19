@if( $vec_saldos[1] != 0)
    <div class="alert alert-warning">
      <strong>Advertencia!</strong>
      <br>
      La Factura ya tiene registros de abonos. Su saldo pendiente es de ${{ number_format($vec_saldos[2],2,',','.')}}; que es diferente a su valor total.
    </div>
@endif
<table class="table table-bordered table-striped">
    {{ Form::bsTableHeader(['Cód.','Producto','Precio','IVA','Cantidad','Total', 'Motivo','Cantidad a devolver']) }}
    </thead>
    <tbody>
        @foreach($doc_registros as $linea )
            <?php 
                $cantidad_real = $linea->cantidad - $linea->cantidad_devuelta;
            ?>
            <tr>
                <td> {{ $linea->producto_id }} </td>
                <td> {{ $linea->producto_descripcion }} </td>
                <td> {{ '$ '.number_format( $linea->precio_unitario, 2, ',', '.') }} </td>
                <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                <td> {{ number_format( $cantidad_real, 2, ',', '.') }} {{ $linea->unidad_medida1 }} </td>
                <td> {{ '$ '.number_format( $linea->precio_unitario * $cantidad_real, 2, ',', '.') }} </td>
                <td> {{ Form::select('motivos_ids[]',$motivos,null,['id'=>'inv_motivo_id']) }} </td>
                <td> 
                    <input type="hidden" class="cantidad_linea" value="{{ $cantidad_real }}">
                    <input type="hidden" name="doc_registros_ids[]" value="{{ $linea->id }}">
                    <input type="text" name="cantidad_devolver[]" class="form-control cantidad_devolver" autocomplete="off">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>