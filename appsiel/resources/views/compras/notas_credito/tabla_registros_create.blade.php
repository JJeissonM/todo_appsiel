<div class="table-responsive">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','Precio','IVA','Cantidad','Total', 'Motivo','Cantidad a devolver']) }}
        <tbody>
            @foreach($doc_registros as $linea )
                <?php 
                    $cantidad_real = $linea->cantidad - $linea->cantidad_devuelta;
                    
                    $linea_entrada_almacen = App\Inventarios\InvDocRegistro::where( 'inv_doc_encabezado_id', $entrada_almacen->id )
                                                ->where( 'inv_producto_id', $linea->producto_id )
                                                ->where( 'cantidad', $linea->cantidad )
                                                ->get()
                                                ->first();

                ?>
                <tr data-cantidad_original="{{$linea->cantidad}}" data-bodega_id="{{$linea_entrada_almacen->inv_bodega_id}}" data-producto_id="{{$linea_entrada_almacen->inv_producto_id}}" >
                    <td> {{ $linea->producto_id }} </td>
                    <td> {{ $linea->producto_descripcion }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_unitario, 2, ',', '.') }} </td>
                    <td> {{ number_format( $linea->tasa_impuesto, 0, ',', '.').'%' }} </td>
                    <td> {{ number_format( $cantidad_real, 2, ',', '.') }} {{ $linea->unidad_medida1 }} </td>
                    <td> {{ '$ '.number_format( $linea->precio_unitario * $cantidad_real, 2, ',', '.') }} </td>
                    <td> {{ Form::select('motivos_ids[]',$motivos,null,['id'=>'inv_motivo_id']) }} </td>
                    <td> 
                        <input type="hidden" class="cantidad_anterior" value="{{ $linea->cantidad }}">
                        <input type="hidden" class="cantidad_linea" value="{{ $cantidad_real }}">
                        <input type="hidden" name="doc_registros_ids[]" value="{{ $linea->id }}">
                        <input type="text" name="cantidad_devolver[]" class="form-control cantidad_devolver" autocomplete="off">
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>