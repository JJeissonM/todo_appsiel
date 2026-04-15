{{--
    Vista parcial: mapeo de productos XML → productos Appsiel
    Se incluye en compras/show.blade.php solo cuando sincronizado_bot = true

    Variables recibidas:
      $doc_encabezado        → ComprasDocEncabezado (con proveedor_id)
      $doc_registros         → Collection de ComprasDocRegistro
      $pivot_items_xml       → Collection de ComprasPivotItemXml (del proveedor)
      $productos_para_select → Collection de InvProducto [id, descripcion]
--}}

<div class="marco_formulario" style="margin-top: 20px; border-top: 3px solid #5bc0de;">
    <h4>
        <i class="fa fa-link" style="color:#5bc0de;"></i>
        &nbsp; Mapeo de Productos XML &rarr; Productos Appsiel
    </h4>
    <p class="text-muted small">
        Asocie cada producto del XML del proveedor con su equivalente en Appsiel.
        Las asociaciones quedan guardadas por proveedor y se aplican automáticamente
        en futuras sincronizaciones.
    </p>

    @if( session('flash_message') )
        <div class="alert alert-success">{{ session('flash_message') }}</div>
    @endif

    {{ Form::open(['route' => 'compras.mapeo.productos.xml', 'id' => 'form_mapeo_xml']) }}

        {{ Form::hidden('compras_doc_encabezado_id', $doc_encabezado->id) }}
        {{ Form::hidden('proveedor_id',              $doc_encabezado->proveedor_id) }}
        {{ Form::hidden('url_id',                    Input::get('id')) }}
        {{ Form::hidden('url_id_modelo',             Input::get('id_modelo')) }}
        {{ Form::hidden('url_id_transaccion',        Input::get('id_transaccion')) }}

        <div class="table-responsive">
            <table class="table table-bordered table-condensed">
                <thead style="background-color: #f5f5f5;">
                    <tr>
                        <th>#</th>
                        <th>Descripción en XML (Proveedor)</th>
                        <th>SKU / Código XML</th>
                        <th style="text-align:right;">Cant.</th>
                        <th style="text-align:right;">Precio Unit.</th>
                        <th style="text-align:right;">IVA %</th>
                        <th style="text-align:right;">Total</th>
                        <th style="min-width:280px;">Producto en Appsiel</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                @php $i = 1; @endphp
                @foreach($doc_registros as $registro)
                    @php
                        /*
                         * Buscar el pivot correspondiente a este registro.
                         * Utilizamos la nueva columna xml_codigo como llave primaria más segura que el nombre
                         */
                        $pivot = $pivot_items_xml
                            ->where('codigo_item_xml', $registro->xml_codigo)
                            ->first();

                        $ya_mapeado = $pivot && $pivot->inv_producto_id > 0;
                    @endphp
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>
                            <strong>{{ $registro->xml_producto }}</strong>
                            @if($pivot && $pivot->unidad_medida_xml)
                                <br><small class="text-muted">UM: {{ $pivot->unidad_medida_xml }}</small>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $registro->xml_codigo ?: '—' }}
                            </small>
                        </td>
                        <td style="text-align:right;">{{ $registro->cantidad }}</td>
                        <td style="text-align:right;">
                            $ {{ number_format($registro->precio_unitario, 2, ',', '.') }}
                        </td>
                        <td style="text-align:right;">{{ $registro->tasa_impuesto }}%</td>
                        <td style="text-align:right;">
                            $ {{ number_format($registro->precio_total, 2, ',', '.') }}
                        </td>
                        <td>
                            {{-- Campos hidden con índice para el array mapeos[] --}}
                            <input type="hidden"
                                   name="mapeos[{{ $i }}][pivot_id]"
                                   value="{{ $pivot->id ?? 0 }}">
                            <input type="hidden"
                                   name="mapeos[{{ $i }}][compras_doc_registro_id]"
                                   value="{{ $registro->id }}">

                            <select name="mapeos[{{ $i }}][inv_producto_id]"
                                    class="form-control select2">
                                <option value="">-- Seleccionar producto --</option>
                                @foreach($productos_para_select as $prod)
                                    <option value="{{ $prod->id }}"
                                        {{ ($ya_mapeado && $pivot->inv_producto_id == $prod->id)
                                            ? 'selected' : '' }}>
                                        {{ $prod->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            @if($ya_mapeado)
                                <span class="label label-success">
                                    <i class="fa fa-check"></i> Vinculado
                                </span>
                            @else
                                <span class="label label-warning">
                                    <i class="fa fa-clock-o"></i> Pendiente
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="text-align: right; margin-top: 10px;">
            @if($doc_encabezado->estado != 'Anulado')
                <button type="submit" class="btn btn-primary" id="btn_guardar_mapeo">
                    <i class="fa fa-save"></i> Guardar mapeo de productos
                </button>
            @endif
        </div>

    {{ Form::close() }}
</div>
