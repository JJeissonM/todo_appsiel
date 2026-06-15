{{--
    Vista parcial: mapeo de productos XML → productos Appsiel
    Se incluye en compras/show.blade.php solo cuando sincronizado_bot = true

    Variables recibidas:
      $doc_encabezado        → ComprasDocEncabezado (con proveedor_id)
      $doc_registros         → Collection de ComprasDocRegistro
      $pivot_items_xml       → Collection de ComprasPivotItemXml (del proveedor)
      $productos_para_select → Collection de InvProducto [id, descripcion]
--}}

@php
    $solo_lectura = !isset($mostrar_boton_confirmar) || !$mostrar_boton_confirmar;
@endphp

<div class="marco_formulario" style="margin-top: 20px; border-top: 3px solid #5bc0de;">
    <h4>
        <i class="fa fa-link" style="color:#5bc0de;"></i>
        &nbsp; Mapeo de Productos XML &rarr; Productos Appsiel
    </h4>
    <p class="text-muted small" style="color: #31708f !important;background-color: #d1ecf1 !important;">
        Asocie cada producto XML del proveedor con su equivalente en Appsiel. Las asociaciones se guardan por proveedor y se aplican automáticamente en futuras sincronizaciones. Si maneja unidades distintas (ej. cajas/docenas), configure la cantidad convertida y/o el factor de conversión según su operación. 
        <strong>NO modificable total del XML y el precio unitario es editable.</strong>
        <br><em>Nota: utilizar únicamente para facturas directas (sin EA).</em>
    </p>

    @if($solo_lectura)
        <div class="alert alert-warning" style="padding: 8px; margin-bottom: 15px;">
            <i class="fa fa-lock"></i> <strong>Mapeo bloqueado:</strong> El documento ya se encuentra confirmado y sus registros contables han sido causados. No es posible modificar el mapeo de productos.
        </div>
    @endif

    @if( session('flash_message') )
        <div class="alert alert-success">{{ session('flash_message') }}</div>
    @endif

    {{ Form::open(['route' => 'compras.mapeo.productos.xml', 'id' => 'form_mapeo_xml']) }}

        {{ Form::hidden('compras_doc_encabezado_id', $doc_encabezado->id) }}
        {{ Form::hidden('proveedor_id',              $doc_encabezado->proveedor_id) }}
        {{ Form::hidden('fecha_doc',                $doc_encabezado->fecha) }}
        {{ Form::hidden('url_id',                    Input::get('id')) }}
        {{ Form::hidden('url_id_modelo',             Input::get('id_modelo')) }}
        {{ Form::hidden('url_id_transaccion',        Input::get('id_transaccion')) }}

        <div class="table-responsive">
            <table class="table table-bordered table-condensed">
                <thead style="background-color: #f5f5f5;">
                    <tr>
                        <th style="text-align:center;">#</th>
                        <th>Descripción en XML (Proveedor)</th>
                        <th style="display:none;">SKU / Código XML</th>
                        <th style="text-align:center; width:80px;">Cant. XML</th>
                        <th style="text-align:center; width:120px;">Precio Unit. XML</th>
                        <th style="text-align:center; width:60px;">IVA %</th>
                        <th style="text-align:center; width:110px;">Total XML</th>
                        <th style="text-align:left; min-width:220px;">Producto en Appsiel <span style="font-weight:normal;">(U.M.)</span></th>
                        <th style="text-align:center; width:110px;" title="Cantidad convertida (en la U.M. del producto Appsiel).">
                            Cant. convertida
                        </th>
                        <th style="text-align:center; width:80px;" title="Factor de conversión configurado para este código XML y proveedor.">
                            Factor
                        </th>
                        <th style="text-align:center; width:120px;" title="Operación del factor para convertir la cantidad.">
                            Operación
                        </th>
                        <th style="text-align:center; width:140px;" title="Sugerido: total_xml / cantidad_convertida (editable).">
                            Precio Unit. (calculado)
                        </th>
                        <th style="text-align:center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                @php $i = 1; @endphp
                @foreach($doc_registros as $registro)
                    @php
                        $pivot = $pivot_items_xml->filter(function($p) use ($registro) {
                            return (string)$p->codigo_item_xml === (string)$registro->xml_codigo;
                        })->first();

                        $ya_mapeado = $pivot && $pivot->inv_producto_id > 0;

                        // Factor de conversión guardado (por defecto 1)
                        $factor_guardado = ($pivot && $pivot->factor_conversion > 0)
                            ? (float)$pivot->factor_conversion
                            : 1;

                        $tipo_factor_guardado = ($pivot && !empty($pivot->tipo_factor))
                            ? $pivot->tipo_factor
                            : 'division';

                        // Valores fieles al XML (NO deben cambiar nunca)
                        // Si existen columnas xml_* en la tabla, se usan.
                        // Si aún no existen, se hace fallback a los campos actuales.
                        $cantidad_xml = isset($registro->xml_cantidad) && $registro->xml_cantidad !== null
                            ? (float) $registro->xml_cantidad
                            : (float) $registro->cantidad;
                        $total_xml = (float) $registro->precio_total;
                        $precio_unitario_xml = isset($registro->xml_precio_unitario) && $registro->xml_precio_unitario !== null
                            ? (float) $registro->xml_precio_unitario
                            : (float) $registro->precio_unitario;

                        // Cantidad convertida sugerida a partir de factor+operación
                        if ($tipo_factor_guardado === 'multiplicacion') {
                            $cantidad_convertida = $cantidad_xml * $factor_guardado;
                        } else {
                            $cantidad_convertida = $factor_guardado > 0 ? ($cantidad_xml / $factor_guardado) : $cantidad_xml;
                        }
                        if ($cantidad_convertida <= 0) {
                            $cantidad_convertida = $cantidad_xml;
                        }

                        // Precio unitario sugerido (editable): total_xml / cantidad_convertida
                        $precio_unitario_sugerido = $cantidad_convertida > 0
                            ? round($total_xml / $cantidad_convertida, 6)
                            : 0;
                    @endphp
                    <tr>
                        <td>{{ $i }}</td>
                        <td>
                            <strong>{{ $registro->xml_producto }}</strong>
                            @if($pivot && $pivot->unidad_medida_xml)
                                <br><small class="text-muted">U.M. XML: {{ $pivot->unidad_medida_xml }}</small>
                            @endif
                        </td>
                        <td style="display:none;">
                            <small class="text-muted">
                                {{ $registro->xml_codigo ?: '—' }}
                            </small>
                        </td>
                        <td style="text-align:center;">{{ $cantidad_xml }}</td>
                        <td style="text-align:center;">
                            $ {{ number_format($precio_unitario_xml, 2, ',', '.') }}
                        </td>
                        <td style="text-align:center;">{{ $registro->tasa_impuesto }}%</td>
                        <td style="text-align:center;">
                            $ {{ number_format($total_xml, 2, ',', '.') }}
                        </td>
                        <td>
                            {{-- Campos hidden con índice --}}
                            <input type="hidden" name="mapeos[{{ $i }}][pivot_id]"
                                   value="{{ $pivot->id ?? 0 }}">
                            <input type="hidden" name="mapeos[{{ $i }}][compras_doc_registro_id]"
                                   value="{{ $registro->id }}">
                            <input type="hidden" class="cantidad-xml" value="{{ $cantidad_xml }}">
                            <input type="hidden" class="total-xml" value="{{ $total_xml }}">
                            <input type="hidden" name="mapeos[{{ $i }}][xml_cantidad]" value="{{ $cantidad_xml }}">
                            <input type="hidden" name="mapeos[{{ $i }}][xml_precio_unitario]" value="{{ $precio_unitario_xml }}">

                            <div style="display:flex; align-items:center; gap:4px;">
                                <select name="mapeos[{{ $i }}][inv_producto_id]"
                                        class="form-control select2 select2-mapeo" {{ $solo_lectura ? 'disabled' : '' }}
                                        style="flex:1;">
                                    <option value="">-- Seleccionar producto --</option>
                                    @foreach($productos_para_select as $prod)
                                        <option value="{{ $prod->id }}"
                                            {{ ($ya_mapeado && $pivot->inv_producto_id == $prod->id)
                                                ? 'selected' : '' }}>
                                            {{ $prod->descripcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="label label-info label-um-appsiel" style="font-size:1em; white-space:nowrap;">—</span>
                            </div>
                        </td>
                        <td style="text-align:center;">
                            <input type="number"
                                   name="mapeos[{{ $i }}][cantidad_convertida]"
                                   class="form-control input-sm cantidad-convertida"
                                   min="0.000001"
                                   step="any"
                                   value="{{ $cantidad_convertida }}"
                                   style="width:100px; text-align:center;" {{ $solo_lectura ? 'readonly' : '' }}>
                        </td>
                        <td style="text-align:center;">
                            <input type="number"
                                   name="mapeos[{{ $i }}][factor_conversion]"
                                   class="form-control input-sm factor-conversion"
                                   min="0.000001"
                                   step="any"
                                   value="{{ $factor_guardado }}"
                                   style="width:80px; text-align:center;" {{ $solo_lectura ? 'readonly' : '' }}>
                        </td>
                        <td style="text-align:center;">
                            <select name="mapeos[{{ $i }}][tipo_factor]"
                                    class="form-control input-sm tipo-factor"
                                    style="width:120px; text-align:center;" {{ $solo_lectura ? 'disabled' : '' }}>
                                <option value="multiplicacion" {{ $tipo_factor_guardado === 'multiplicacion' ? 'selected' : '' }}>
                                    Multiplicación
                                </option>
                                <option value="division" {{ $tipo_factor_guardado === 'division' ? 'selected' : '' }}>
                                    División
                                </option>
                            </select>
                        </td>
                        <td style="text-align:center;">
                            <div class="input-group input-group-sm" style="width:140px; margin:0 auto;">
                                <span class="input-group-addon">$</span>
                                <input type="number"
                                       name="mapeos[{{ $i }}][precio_unitario_final]"
                                       class="form-control precio-unitario-final"
                                       step="any"
                                       value="{{ $precio_unitario_sugerido }}"
                                       style="text-align:center;"
                                       title="Sugerido: total_xml/cantidad_convertida. Editable." {{ $solo_lectura ? 'readonly' : '' }}>
                            </div>
                        </td>
                        <td style="text-align:center;">
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
                @php $i++; @endphp
                @endforeach
                </tbody>
            </table>
        </div>

        @if(!$solo_lectura)
            <div class="alert alert-info alert-sm" style="padding:8px; margin-top:8px;">
                <i class="fa fa-lightbulb-o"></i>
                <strong>Reglas:</strong> El <em>Total XML</em> no cambia.
                El sistema sugiere <em>Precio Unitario</em> como <code>Total XML / Cant. convertida</code>.
                Puede ajustar manualmente el precio unitario; el <em>factor</em> y la <em>operación</em>
                quedan guardados por proveedor/código XML para futuras sincronizaciones.
            </div>

            <div style="text-align: right; margin-top: 10px;">
                <button type="submit" class="btn btn-primary" id="btn_guardar_mapeo">
                    <i class="fa fa-save"></i> Guardar mapeo de productos
                </button>
            </div>
        @endif

    {{ Form::close() }}
</div>
