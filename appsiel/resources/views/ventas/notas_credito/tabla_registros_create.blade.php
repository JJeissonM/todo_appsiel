@if( $vec_saldos[1] != 0)
    <div class="alert alert-warning">
      <strong>Advertencia!</strong>
      <br>
      La Factura ya tiene registros de abonos. Su saldo pendiente es de ${{ number_format($vec_saldos[2],2,',','.')}}; que es diferente a su valor total.
    </div>
@endif

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="tabla_registros_nota_credito">
        {{ Form::bsTableHeader(['Cód.','Producto','Precio','IVA','Cantidad','Total', 'Motivo','Cantidad a devolver', '<button type="button" class="btn btn-primary btn-xs" id="btn_devolver_todo">Devolver todo</button>']) }}
        <tbody>
            @foreach($doc_registros as $linea )
                <?php 
                    $cantidad_real = $linea->cantidad - $linea->cantidad_devuelta;
                    $cantidad_real_input = rtrim(rtrim(number_format((float)$cantidad_real, 6, '.', ''), '0'), '.');
                ?>
                <tr>
                    <td class="text-center"> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td class="text-right"> {{ '$ '.number_format( $linea->precio_unitario, 2, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td class="text-center"> {{ number_format( $cantidad_real, 2, ',', '.') }} {{ $linea->item->get_unidad_medida1() }} </td>
                    <td class="text-right"> {{ '$ '.number_format( $linea->precio_unitario * $cantidad_real, 2, ',', '.') }} </td>
                    <td> {{ Form::select('motivos_ids[]',$motivos,null,['id'=>'inv_motivo_id']) }} </td>
                    <td> 
                        <input type="hidden" class="cantidad_linea" value="{{ $cantidad_real_input }}">
                        <input type="hidden" name="doc_registros_ids[]" value="{{ $linea->id }}">
                        <input type="text" name="cantidad_devolver[]" class="form-control cantidad_devolver" autocomplete="off">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-default btn-xs btn_devolver_linea">Total</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
