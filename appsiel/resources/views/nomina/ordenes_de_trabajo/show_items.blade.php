<div class="table-responsive">
    <table class="table table-bordered table-striped" id="ingreso_productos">
        <thead>
            <tr>
                <th data-override="inv_producto_id">Cod.</th>
                <th width="280px">Producto</th>
                <th width="200px" data-override="motivo">Motivo</th>
                <th data-override="costo_unitario"> Costo Unit. </th>
                <th data-override="cantidad">Cantidad</th>
                <th data-override="costo_total">Costo Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
                $total_cantidad = 0;
                $total_factura = 0;
                $items = $orden_de_trabajo->items;
            ?>
            @foreach( $items as $item_orden_trabajo )
                <tr id="{{ $item_orden_trabajo->item->id }}">
                    <td>{{ $item_orden_trabajo->item->id }}</td>
                    <td class="nom_prod">
                        {{ $item_orden_trabajo->item->id }} {{ $item_orden_trabajo->item->descripcion }} ({{ $item_orden_trabajo->item->unidad_medida1 }})
                    </td>
                    <td>
                        <span style="color:transparent;">{{config('inventarios.motivo_salida_id_ot')}}-</span><span style="color:red;">Salida</span>
                        <input type="hidden" class="movimiento" value="entrada">
                    </td>
                    <td style="text-align: right;">
                        ${{ number_format( $item_orden_trabajo->costo_unitario, 2, '.', '') }}
                    </td>
                    <td class="cantidad" align="center">@if( $orden_de_trabajo->inv_doc_encabezado_id == 0 )<div class="elemento_modificar" title="Doble click para modificar." data-url_modificar="{{ url('nom_ordenes_trabajo_cambiar_cantidad_items') . "/" . $orden_de_trabajo->id . "/" . $item_orden_trabajo->item->id }}"> {{ $item_orden_trabajo->cantidad }}</div> {{ $item_orden_trabajo->item->unidad_medida1 }}@else{{ number_format( $item_orden_trabajo->cantidad, 2, ',', '.') }} {{ $item_orden_trabajo->item->unidad_medida1 }}@endif</td>
                    <td class="costo_total" style="text-align: right;">
                        ${{ number_format( $item_orden_trabajo->costo_total, 2, '.', '') }}
                    </td>
                </tr>
                <?php 
                    $total_cantidad += $item_orden_trabajo->cantidad;
                    $total_factura += $item_orden_trabajo->costo_total;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td>&nbsp;</td>
                <td align="center"> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
                <td style="text-align: right;"> $ {{ number_format($total_factura, 2, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>

<div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Documento de Salida de inventario</div>
<div class="table-responsive">
    <table class="table table-bordered">
        @if( $orden_de_trabajo->inv_doc_encabezado_id == 0 )
            <tr>
                <td>
                    {{ Form::open([ 'url' => 'inventarios', 'id'=>'form_create']) }}
                        <!-- { { Form::bsSelect('inv_bodega_id', null, 'Bodega', App\Inventarios\InvBodega::opciones_campo_select(), ['class'=>'form-control']) }} -->
                        <input type="hidden" name="inv_bodega_id" id="inv_bodega_id" value="{{ $orden_de_trabajo->inv_bodega_id }}">
                        <input type="hidden" name="core_empresa_id" id="core_empresa_id" value="{{ $orden_de_trabajo->core_empresa_id }}">
                        <!-- 4 = Salida de almacén -->
                        <input type="hidden" name="core_tipo_doc_app_id" id="core_tipo_doc_app_id" value="4">
                        <!-- 3 = Salida de almacén -->
                        <input type="hidden" name="core_tipo_transaccion_id" id="core_tipo_transaccion_id" value="3"> 
                        <input type="hidden" name="consecutivo" id="consecutivo" value="0">
                        <input type="hidden" name="fecha" id="fecha" value="{{ $orden_de_trabajo->fecha }}">
                        <input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{ $orden_de_trabajo->core_tercero_id }}">
                        <input type="hidden" name="descripcion" id="descripcion" value="Hecha con base en orden de trabajo {{ $orden_de_trabajo->tipo_documento_app->prefijo }} {{ $orden_de_trabajo->consecutivo }}.">
                        <input type="hidden" name="documento_soporte" id="documento_soporte" value="{{ $orden_de_trabajo->tipo_documento_app->prefijo }} {{ $orden_de_trabajo->consecutivo }}">
                        <input type="hidden" name="estado" id="estado" value="Activo">
                        <input type="hidden" name="creado_por" id="creado_por" value="{{ Auth::user()->email }}">

                        <input type="hidden" name="movimiento" id="movimiento" value="">
                        <input type="hidden" name="url_id" id="url_id" value="{{ Input::get('id') }}">
                        <!-- 249 = Salidas de almacén -->
                        <input type="hidden" name="url_id_modelo" id="url_id_modelo" value="249">
                        <input type="hidden" name="url_id_transaccion" id="url_id_transaccion" value="{{ Input::get('id_transaccion') }}">
                        <input type="hidden" name="ruta_redirect" id="ruta_redirect" value="nom_ordenes_trabajo/">
                        <input type="hidden" name="modelo_id_ruta" id="modelo_id_ruta" value="{{ Input::get('id_modelo') }}">
                        <input type="hidden" name="registro_id" id="registro_id" value="{{$id}}">
                    {{ Form::close() }}
                </td>
                <td>
                    <button class="btn btn-primary" id="btn_guardar_documento_inventario" title="Crear documento de inventario"><i class="fa fa-file-text"></i> Crear</button>
                </td>
            </tr>
        @else
            <tr>
                <td>
                    <b>{{ $orden_de_trabajo->documento_inventario->tipo_transaccion->descripcion }}: </b> {!! $orden_de_trabajo->documento_inventario->enlace_show_documento() !!}
                </td>
            </tr>
        @endif

    </table>
</div>