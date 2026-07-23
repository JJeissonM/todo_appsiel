@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    <?php $form_create = array('campos' => array(array('tipo' => 'cliente_autocomplete'))); ?>
    <?php $roomBodegaId = $order->stay && $order->stay->room ? (int)$order->stay->room->inv_bodega_id : 0; ?>
    <?php $roomBodegaLabel = $order->stay && $order->stay->room && $order->stay->room->bodega ? $order->stay->room->bodega->descripcion : ''; ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8">
                    <h3>Pedido hotelero {{ $order->document_number }}</h3>
                </div>
                <div class="col-md-4 text-right">
                    @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO && isset($canCancelHotelOrder) && $canCancelHotelOrder)
                        <form method="POST" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/cancel', array('id_modelo' => $hotelUrl::modelId('App\\Hotel\\HotelOrderHeader')))) }}" style="display:inline-block;">
                            {{ csrf_field() }}
                            <button type="submit" class="btn btn-danger btn-sm hotel-confirm-submit" data-message="Anular pedido hotelero?">
                                <i class="fa fa-ban"></i> Anular pedido
                            </button>
                        </form>
                    @endif
                    <a href="{{ url($hotelUrl::url('hotel/stays/'.$order->stay_id, array('id_modelo' => $hotelUrl::modelId('App\\Hotel\\HotelStay')))) }}" class="btn btn-default btn-sm">Volver a estadia</a>
                </div>
            </div>
            <table class="table table-bordered">
                <tr><th>Cliente</th><td>{{ $order->cliente && $order->cliente->tercero ? $order->cliente->tercero->descripcion : $order->cliente_id }}</td><th>Estado</th><td>{{ $order->status }}</td></tr>
                <tr><th>Habitacion</th><td>{{ $order->stay && $order->stay->room ? $order->stay->room->room_number : '' }}</td>
                    <th>Factura</th>
                    <td>
                        @if($order->invoiceUrl() != '')
                            <a href="{{ $order->invoiceUrl() }}" target="_blank">{{ $order->invoiceLabel() }}</a>
                        @else
                            {{ $order->invoiceLabel() }}
                        @endif
                    </td></tr>
                <tr><th>Fecha</th><td>{{ $order->order_date }}</td>
                    <th>Creado por:</th>
                    <td>
                        {{ $order->creador_por() ? $order->creador_por()->first()->name : '' }}
                    </td></tr>
                <tr><th>Bodega minibar</th><td colspan="3">{{ $roomBodegaLabel }}</td></tr>
            </table>

            <h4>Lineas del pedido</h4>
            @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                <form method="POST" id="hotel_order_lines_form" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/save-lines')) }}">
                    {{ csrf_field() }}
            @endif
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Producto</th>
                            <th>Bodega</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Vlr. Dcto. ($)</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="hotel_order_lines_body">
                        <?php $total = 0; ?>
                        @foreach($order->lines as $line)
                            <?php $total += $line->line_total; ?>
                            <tr class="hotel-order-line-row" data-line-id="{{ $line->id }}" data-line-total="{{ $line->line_total }}">
                                <td style="white-space: normal;">{{ $line->product ? $line->product->descripcion : $line->producto_id }}</td>
                                <td>{{ $line->bodega ? $line->bodega->descripcion : $line->inv_bodega_id }}</td>
                                <td class="text-right">
                                    ${{ number_format($line->unit_price, 2, ',', '.')   }}
                                    <input type="hidden" name="lines[{{ $line->id }}][unit_price]" value="{{ $line->unit_price }}">
                                </td>
                                <td>
                                    @if($line->product_is_a_room())
                                        {{ $line->quantity }}
                                        <input type="hidden" name="lines[{{ $line->id }}][quantity]" value="{{ $line->quantity }}">
                                    @else
                                        <input type="text" name="lines[{{ $line->id }}][quantity]" class="form-control input-sm text-right" value="{{ $line->quantity }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }} style="font-size: 14px;">
                                    @endif
                                </td>
                                <td><input type="text" name="lines[{{ $line->id }}][discount]" class="form-control input-sm text-right" value="{{ $line->discount }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }} style="font-size: 14px;"></td>
                                <td class="text-right">
                                    ${{ number_format($line->tax_value, 2, ',', '.') }}
                                </td>
                                <td class="text-right">
                                    ${{ number_format($line->line_total, 2, ',', '.') }}
                                </td>
                                <td>
                                    @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                                        @can('hotel_pedido_retirar_producto_habitacion')
                                            <button type="button" class="btn btn-danger btn-xs hotel-remove-line" data-line-id="{{ $line->id }}"><i class="fa fa-trash"></i></button>
                                        @else
                                            @if(!$line->product_is_a_room())
                                                <button type="button" class="btn btn-danger btn-xs hotel-remove-line" data-line-id="{{ $line->id }}"><i class="fa fa-trash"></i></button>
                                            @endif
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr id="hotel_order_total_row">
                            <th colspan="6" class="text-right">Total</th>
                            <th class="text-right">{{ number_format($total, 2, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                <h4>Agregar consumo</h4>
                    <div id="hotel_deleted_lines"></div>
                    <input type="hidden" name="room_id" value="{{ $order->stay ? $order->stay->room_id : '' }}">
                    <input type="hidden" id="hotel_inv_bodega_id" value="{{ $roomBodegaId }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Producto/Servicio</label>
                                <select id="hotel_producto_id" class="form-control">
                                    @foreach($products as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="text" id="hotel_quantity" class="form-control" value="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Stock</label>
                                <input type="text" id="hotel_stock" class="form-control" placeholder="Stock" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Precio</label>
                                <input type="text" id="hotel_unit_price" class="form-control" placeholder="Automático" {{ $canEditHotelOrderPrice ? '' : 'readonly' }}>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-info btn-block" type="button" id="hotel_add_pending_line"><i class="fa fa-plus"></i> Agregar</button>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-primary hotel-backend-submit" type="submit"><i class="fa fa-save"></i> Guardar cambios</button>
                    <br>
                </form>
            @endif
        </div>

        @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)

            <div class="row">
                <div class="col-md-4">
                    <div class="marco_formulario">
                        <h5>Anticipos / saldos a favor</h5>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="hotel_anticipos_table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Documento</th>
                                        <th>Fecha</th>
                                        <th>Detalle</th>
                                        <th>Saldo a favor</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($anticipos as $anticipo)
                                        <?php $saldoAnticipo = abs((float)$anticipo['saldo_pendiente']); ?>
                                        <tr>
                                            <td>
                                                <input type="checkbox"
                                                    class="hotel-advance-check"
                                                    data-cxc_movimiento_id="{{ $anticipo['id'] }}"
                                                    data-documento="{{ $anticipo['documento'] }}"
                                                    data-fecha="{{ $anticipo['fecha'] }}"
                                                    data-detalle="{{ $anticipo['detalle'] }}"
                                                    data-saldo_pendiente="{{ $anticipo['saldo_pendiente'] }}"
                                                    data-valor_disponible="{{ $saldoAnticipo }}">
                                            </td>
                                            <td>{{ $anticipo['documento'] }}</td>
                                            <td>{{ $anticipo['fecha'] }}</td>
                                            <td>{{ $anticipo['detalle'] }}</td>
                                            <td class="text-right">{{ number_format($saldoAnticipo, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach

                                    @if(count($anticipos) == 0)
                                        <tr>
                                            <td colspan="5">El cliente no tiene anticipos disponibles.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <button class="btn btn-success btn-sm" type="button" id="hotel_btn_aplicar_anticipo">
                            <i class="fa fa-check"></i> Aplicar anticipo
                        </button>
                        <br><br>
                    </div>
                </div>
                <div class="col-md-5">
                    <div id="hotel_medios_pago_panel">
                        @include('tesoreria.incluir.medios_recaudos')
                    </div>
                </div>
                <div class="col-md-3">

            <div class="marco_formulario">
                <h5>Generar factura</h5>
                <hr>
                <form method="POST" id="hotel_generate_pos_invoice_form" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/generate-pos-invoice')) }}" style="display:block;">
                    {{ csrf_field() }}
                    <label>Tipo de factura:</label>
                    <select name="invoice_document_type" class="form-control" style="display:inline-block; width:auto;">
                        <option value="pos">POS int.</option>
                        <option value="electronic">F.E.</option>
                    </select>

                    <br>
                    <label>Facturar a:</label>
                    <div class="radio">
                        <label>
                            <input type="radio" name="invoice_customer_mode" value="guest" checked>
                            Huesped del pedido
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="invoice_customer_mode" value="other">
                            Otro cliente
                        </label>
                    </div>

                    <div id="hotel_invoice_customer_picker" style="display:none;">
                        <div class="hotel-cliente-autocomplete-wrap" style="position: relative;">
                            <input type="text"
                                class="form-control hotel-cliente-autocomplete-input"
                                data-target="hotel_invoice_cliente_id"
                                placeholder="Buscar cliente"
                                autocomplete="off">
                            <input type="hidden" name="invoice_cliente_id" id="hotel_invoice_cliente_id">
                            <div class="hotel-cliente-autocomplete-results list-group" style="display:none; position:absolute; z-index:1050; left:0; right:0;"></div>
                        </div>
                        <br>
                    </div>

                    <label for="hotel_forma_pago">Forma de pago:</label>
                    <select name="forma_pago" id="hotel_forma_pago" class="form-control" style="display:inline-block; width:auto;">
                        <option value="contado">Contado</option>
                        <option value="credito">Credito</option>
                    </select>
                    <br>
                    <br>
                    <input type="hidden" name="lineas_registros_medios_recaudos" id="hotel_lineas_registros_medios_recaudos" value="[]">
                    <input type="hidden" name="object_anticipos" id="hotel_object_anticipos" value="null">
                    <input type="hidden" id="hotel_electronic_resolution_status" value="{{ isset($electronicResolutionValidation->status) ? $electronicResolutionValidation->status : 'error' }}">
                    <input type="hidden" id="hotel_electronic_resolution_message" value="{{ isset($electronicResolutionValidation->message) ? $electronicResolutionValidation->message : 'No fue posible validar la resolucion de facturacion electronica.' }}">
                    <button class="btn btn-primary hotel-backend-submit" type="submit"> <i class="fa fa-save"></i> Guardar Factura</button>
                </form>
                <br><br><br><br>
            </div>
            </div>
            </div>
        @endif
    </div>
    @include('hotel.partials.cliente_autocomplete_modal')
@endsection

@section('scripts')
    @parent
    @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
        <script type="text/javascript">
            $.fn.actualizar_medio_recaudo = function() {
                return this;
            };
        </script>
        <script type="text/javascript" src="{{ asset('assets/js/tesoreria/medios_recaudos.js?aux=' . uniqid()) }}"></script>
    @endif
    <script type="text/javascript">
        $(document).ready(function() {
            var $producto = $('#hotel_producto_id');
            var $precio = $('#hotel_unit_price');
            var $descripcion = $('#hotel_line_description');
            var $cantidad = $('#hotel_quantity');
            var $stock = $('#hotel_stock');
            var $bodega = $('#hotel_inv_bodega_id');
            var hotelRoomId = "{{ $order->stay ? $order->stay->room_id : '' }}";
            var hotelBodegaLabel = {!! json_encode($roomBodegaLabel) !!};
            var hotelNewLineIndex = 0;
            var $formaPago = $('#hotel_forma_pago');
            var $mediosPagoPanel = $('#hotel_medios_pago_panel');
            var $invoiceCustomerPicker = $('#hotel_invoice_customer_picker');
            var hotelOrderTotal = {{ (float)$order->lines->sum('line_total') }};
            var hotelAdvanceObjects = [];
            var hotelCanEditUnitPrice = {{ $canEditHotelOrderPrice ? 'true' : 'false' }};
            var hotelSelectedTaxRate = 0;

            function hotelSwalAlert(message, icon) {
                icon = icon || 'warning';
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: 'Atencion',
                        text: message,
                        icon: icon,
                        confirmButtonText: 'Aceptar'
                    });
                }
            }

            function hotelSwalConfirm(message, onConfirm) {
                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: 'Confirmar',
                        text: message,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'Cancelar'
                    }).then(function(result) {
                        if (result && (result.isConfirmed || result.value)) {
                            onConfirm();
                        }
                    });
                } else if (window.confirm(message)) {
                    onConfirm();
                }
            }

            $('.hotel-confirm-submit').on('click', function(event) {
                event.preventDefault();

                var $button = $(this);
                var $form = $button.closest('form');
                var message = $button.data('message') || 'Confirmar accion?';

                hotelSwalConfirm(message, function() {
                    hotelSetBackendButtonLoading($button, 'Procesando...');
                    $form.submit();
                });
            });

            function hotelSetBackendButtonLoading($button, label) {
                if ($button.length === 0) {
                    return;
                }

                if ($button.data('hotel-loading') == '1') {
                    return;
                }

                $button.data('hotel-loading', '1');
                $button.data('hotel-original-html', $button.html());
                $button.prop('disabled', true);
                $button.html('<i class="fa fa-spinner fa-spin"></i> ' + label);
            }

            function hotelSetFormLoading($form, label) {
                hotelSetBackendButtonLoading($form.find('button[type="submit"]').first(), label);
            }

            if ($.fn.select2) {
                $producto.select2();
            }

            function normalizarRespuesta(respuesta) {
                if (typeof respuesta === 'string') {
                    try {
                        respuesta = $.parseJSON(respuesta);
                    } catch (e) {
                        respuesta = {};
                    }
                }

                return respuesta || {};
            }

            function cargarPrecioProducto() {
                var productoId = $producto.val();

                if (!productoId) {
                    $precio.val('');
                    $precio.attr('placeholder', 'Automatico');
                    $stock.val('');
                    $stock.attr('placeholder', 'Stock');
                    hotelSelectedTaxRate = 0;
                    return;
                }

                $precio.attr('placeholder', 'Consultando...');
                $stock.attr('placeholder', 'Consultando...');

                $.get("{{ url('inv_consultar_productos') }}", {
                    producto_id: productoId,
                    cliente_id: "{{ $order->cliente_id }}",
                    lista_precios_id: "{{ $order->cliente ? $order->cliente->lista_precios_id : '' }}",
                    fecha: "{{ date('Y-m-d') }}",
                    cantidad: $cantidad.val(),
                    bodega_id: $bodega.val()
                }).done(function(respuesta) {
                    respuesta = normalizarRespuesta(respuesta);

                    if (respuesta.unit_price !== undefined && respuesta.unit_price !== null && respuesta.unit_price !== '') {
                        $precio.val(respuesta.unit_price);
                    } else if (respuesta.precio_venta !== undefined && respuesta.precio_venta !== null && respuesta.precio_venta !== '') {
                        $precio.val(respuesta.precio_venta);
                    } else if (respuesta.precio_unitario !== undefined && respuesta.precio_unitario !== null && respuesta.precio_unitario !== '') {
                        $precio.val(respuesta.precio_unitario);
                    }

                    if ($descripcion.val() === '' && respuesta.descripcion !== undefined) {
                        $descripcion.val(respuesta.descripcion);
                    }

                    if (respuesta.stock !== undefined && respuesta.stock !== null && respuesta.stock !== '') {
                        $stock.val(respuesta.stock);
                    } else if (respuesta.existencia_actual !== undefined && respuesta.existencia_actual !== null && respuesta.existencia_actual !== '') {
                        $stock.val(respuesta.existencia_actual);
                    } else {
                        $stock.val('0');
                    }

                    hotelSelectedTaxRate = respuesta.tasa_impuesto !== undefined && respuesta.tasa_impuesto !== null && respuesta.tasa_impuesto !== '' ? parseFloat(respuesta.tasa_impuesto) : 0;
                    if (isNaN(hotelSelectedTaxRate)) {
                        hotelSelectedTaxRate = 0;
                    }

                    $precio.attr('placeholder', 'Automatico');
                    $stock.attr('placeholder', 'Stock');
                }).fail(function() {
                    $precio.attr('placeholder', 'Automatico');
                    $stock.val('');
                    $stock.attr('placeholder', 'Stock');
                    hotelSelectedTaxRate = 0;
                });
            }

            $producto.on('change', cargarPrecioProducto);
            cargarPrecioProducto();

            function hotelEscapeHtml(value) {
                return $('<div/>').text(value || '').html();
            }

            function hotelProductLabel() {
                var text = $producto.find('option:selected').text() || '';
                return $.trim(text);
            }

            function hotelAppendPendingLine() {
                var productoId = $producto.val();
                var productLabel = hotelProductLabel();
                var quantity = $cantidad.val();
                var unitPrice = $precio.val();

                if (!productoId) {
                    hotelSwalAlert('Debe seleccionar un producto o servicio.');
                    return false;
                }

                if (parseFloat(quantity) <= 0 || isNaN(parseFloat(quantity))) {
                    hotelSwalAlert('La cantidad debe ser mayor a cero.');
                    return false;
                }

                if (unitPrice === '' && hotelCanEditUnitPrice) {
                    unitPrice = '0';
                }

                if (unitPrice !== '' && (parseFloat(unitPrice) < 0 || isNaN(parseFloat(unitPrice)))) {
                    hotelSwalAlert('El precio debe ser mayor o igual a cero.');
                    return false;
                }

                var rowIndex = hotelNewLineIndex++;
                var displayPrice = unitPrice === '' ? 'Automatico' : '$' + parseFloat(unitPrice).toFixed(2);
                var lineTotal = unitPrice === '' ? 0 : parseFloat(quantity) * parseFloat(unitPrice);
                var taxValue = 0;
                if (hotelSelectedTaxRate > 0 && lineTotal > 0) {
                    taxValue = lineTotal - (lineTotal / (1 + (hotelSelectedTaxRate / 100)));
                }
                var rowHtml =
                    '<tr class="hotel-pending-line hotel-order-line-row" data-line-total="' + hotelEscapeHtml(lineTotal) + '">' +
                        '<td style="white-space: normal;">' + hotelEscapeHtml(productLabel) +
                            '<input type="hidden" name="new_lines[' + rowIndex + '][producto_id]" value="' + hotelEscapeHtml(productoId) + '">' +
                            '<input type="hidden" name="new_lines[' + rowIndex + '][room_id]" value="' + hotelEscapeHtml(hotelRoomId) + '">' +
                            '<input type="hidden" name="new_lines[' + rowIndex + '][source_type]" value="MANUAL">' +
                        '</td>' +
                        '<td>' + hotelEscapeHtml(hotelBodegaLabel) + '</td>' +
                        '<td class="text-right">' + hotelEscapeHtml(displayPrice) +
                            '<input type="hidden" name="new_lines[' + rowIndex + '][unit_price]" value="' + hotelEscapeHtml(unitPrice) + '">' +
                        '</td>' +
                        '<td><input type="text" name="new_lines[' + rowIndex + '][quantity]" class="form-control input-sm text-right" value="' + hotelEscapeHtml(quantity) + '" style="font-size: 14px;"></td>' +
                        '<td><input type="text" name="new_lines[' + rowIndex + '][discount]" class="form-control input-sm text-right" value="0" style="font-size: 14px;"></td>' +
                        '<td class="text-right">$' + taxValue.toFixed(2) + '</td>' +
                        '<td class="text-right">$' + lineTotal.toFixed(2) + '</td>' +
                        '<td><button type="button" class="btn btn-danger btn-xs hotel-remove-line" data-new-line="1"><i class="fa fa-trash"></i></button></td>' +
                    '</tr>';

                $('#hotel_order_total_row').before(rowHtml);
                $producto.val('').trigger('change');
                $cantidad.val('1');
                $precio.val('');
                $stock.val('');
                return true;
            }

            function hotelRefreshLinesTotal() {
                var total = 0;

                $('#hotel_order_lines_body tr.hotel-order-line-row:visible').each(function() {
                    var rowTotal = parseFloat($(this).attr('data-line-total'));
                    if (!isNaN(rowTotal)) {
                        total += rowTotal;
                    }
                });

                $('#hotel_order_total_row th').eq(1).text(new Intl.NumberFormat('de-DE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(total));
            }

            $(document).on('click', '.hotel-remove-line', function() {
                var $button = $(this);
                var $row = $button.closest('tr');
                var lineId = $button.attr('data-line-id');

                hotelSwalConfirm('Eliminar esta linea del pedido?', function() {
                    if (lineId !== undefined && lineId !== '') {
                        $('#hotel_deleted_lines').append(
                            '<input type="hidden" name="deleted_lines[]" value="' + hotelEscapeHtml(lineId) + '">'
                        );
                    }

                    $row.remove();
                    hotelRefreshLinesTotal();
                });
            });

            $('#hotel_add_pending_line').on('click', function() {
                hotelAppendPendingLine();
            });

            $('#hotel_order_lines_form').on('submit', function(event) {
                if ($producto.val()) {
                    if (!hotelAppendPendingLine()) {
                        event.preventDefault();
                        return false;
                    }
                }

                hotelSetFormLoading($(this), 'Guardando...');
                return true;
            });

            function hotelParseMoney(value) {
                value = (value || '').toString();
                value = value.replace('$', '');
                value = value.replace(/\s/g, '');

                if (value.indexOf(',') >= 0 && value.indexOf('.') >= 0) {
                    value = value.replace(/\./g, '').replace(',', '.');
                } else {
                    value = value.replace(/,/g, '');
                }

                var number = parseFloat(value);
                return isNaN(number) ? 0 : number;
            }

            function hotelPaymentCell($row, index) {
                var text = $.trim($row.find('td').eq(index).text());
                return text === '-' ? '0-' : text;
            }

            function hotelPaymentRows() {
                var rows = [];
                $('#ingreso_registros_medios_recaudo tbody tr').each(function() {
                    var $row = $(this);
                    var value = $.trim($row.find('td').eq(4).text());

                    if (value === '' || value === '$0.00') {
                        return;
                    }

                    rows.push({
                        teso_medio_recaudo_id: hotelPaymentCell($row, 0),
                        teso_motivo_id: hotelPaymentCell($row, 1),
                        teso_caja_id: hotelPaymentCell($row, 2),
                        teso_cuenta_bancaria_id: hotelPaymentCell($row, 3),
                        valor: value
                    });
                });

                return rows;
            }

            function hotelMoneyLabel(value) {
                return '$' + parseFloat(value || 0).toFixed(2);
            }

            function hotelPendingPaymentValue() {
                var pending = hotelOrderTotal - hotelPaymentTotal(hotelPaymentRows());
                return pending < 0 ? 0 : pending;
            }

            function hotelFormatPendingPayment(value) {
                return '$ ' + parseFloat(value || 0).toFixed(2);
            }

            function hotelSetPendingPaymentLabel() {
                $('#lbl_hotel_vlr_pendiente_ingresar').text(hotelFormatPendingPayment(hotelPendingPaymentValue()));
            }

            $.fn.actualizar_medio_recaudo = function() {
                hotelSetPendingPaymentLabel();
                return this;
            };

            function hotelAppendAdvancePayment(advance, value) {
                var exists = false;
                $('#ingreso_registros_medios_recaudo tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.attr('data-hotel_anticipo_id') == advance.cxc_movimiento_id) {
                        exists = true;
                    }
                });

                if (exists) {
                    return false;
                }

                $('#ingreso_registros_medios_recaudo tbody').append(
                    '<tr data-hotel_anticipo_id="' + advance.cxc_movimiento_id + '">' +
                        '<td>0-Anticipo</td>' +
                        '<td>0-Cruce anticipos</td>' +
                        '<td>0-</td>' +
                        '<td>0-</td>' +
                        '<td class="valor_total text-right">' + hotelMoneyLabel(value) + '</td>' +
                        '<td><button type="button" class="btn btn-danger btn-xs hotel-remove-advance"><i class="fa fa-trash"></i></button></td>' +
                    '</tr>'
                );

                return true;
            }

            function hotelAdvanceTotal() {
                var total = 0;
                $.each(hotelAdvanceObjects, function(index, advance) {
                    total += hotelParseMoney(advance.valor_aplicar);
                });
                return total;
            }

            function hotelPaymentTotal(rows) {
                var total = 0;
                $.each(rows, function(index, row) {
                    total += hotelParseMoney(row.valor);
                });
                return total;
            }

            if (typeof calcular_totales_medio_recaudos === 'function') {
                calcular_totales_medio_recaudos();
            }

            $('#recaudoModal').on('shown.bs.modal.hotelPendingPayment', function() {
                if ($('#div_hotel_pendiente_ingresar_medio_recaudo').length === 0) {
                    $('#form_registro').before('<div id="div_hotel_pendiente_ingresar_medio_recaudo" style="color: red; font-size: 18px; margin-bottom: 12px;">Pendiente por registrar: <span id="lbl_hotel_vlr_pendiente_ingresar">$ 0.00</span></div>');
                }

                hotelSetPendingPaymentLabel();
            });

            $('#recaudoModal').on('hidden.bs.modal.hotelPendingPayment', function() {
                $('#div_hotel_pendiente_ingresar_medio_recaudo').remove();
            });

            function toggleMediosPago() {
                if ($formaPago.val() === 'credito' && hotelAdvanceObjects.length === 0) {
                    $mediosPagoPanel.hide();
                    return;
                }

                $mediosPagoPanel.show();
            }

            $formaPago.on('change', toggleMediosPago);
            toggleMediosPago();

            function toggleInvoiceCustomerPicker() {
                if ($('input[name="invoice_customer_mode"]:checked').val() === 'other') {
                    $invoiceCustomerPicker.show();
                    return;
                }

                $invoiceCustomerPicker.hide();
                $('#hotel_invoice_cliente_id').val('');
                $invoiceCustomerPicker.find('.hotel-cliente-autocomplete-input').val('');
            }

            $('input[name="invoice_customer_mode"]').on('change', toggleInvoiceCustomerPicker);
            toggleInvoiceCustomerPicker();

            $('#hotel_btn_aplicar_anticipo').on('click', function() {
                var rows = hotelPaymentRows();
                var totalPayments = hotelPaymentTotal(rows);
                var remaining = hotelOrderTotal - totalPayments;

                if (remaining <= 1) {
                    hotelSwalAlert('El pedido ya tiene medios de pago por el total.');
                    return;
                }

                $('.hotel-advance-check:checked').each(function() {
                    var $check = $(this);
                    var advanceId = $check.attr('data-cxc_movimiento_id');
                    var alreadyApplied = false;

                    $.each(hotelAdvanceObjects, function(index, advance) {
                        if (advance.cxc_movimiento_id == advanceId) {
                            alreadyApplied = true;
                        }
                    });

                    if ($('#ingreso_registros_medios_recaudo tbody tr[data-hotel_anticipo_id="' + advanceId + '"]').length > 0) {
                        alreadyApplied = true;
                    }

                    if (alreadyApplied || remaining <= 1) {
                        $check.prop('checked', false);
                        $check.prop('disabled', true);
                        return;
                    }

                    var available = parseFloat($check.attr('data-valor_disponible')) || 0;
                    var valueToApply = Math.min(available, remaining);

                    if (valueToApply <= 0) {
                        return;
                    }

                    var advance = {
                        cxc_movimiento_id: advanceId,
                        Documento: $check.attr('data-documento'),
                        Fecha: $check.attr('data-fecha'),
                        saldo_pendiente: $check.attr('data-saldo_pendiente'),
                        valor_aplicar: valueToApply.toFixed(2)
                    };

                    if (!hotelAppendAdvancePayment(advance, valueToApply)) {
                        $check.prop('checked', false);
                        $check.prop('disabled', true);
                        return;
                    }

                    hotelAdvanceObjects.push(advance);
                    remaining -= valueToApply;
                    $check.prop('checked', false);
                    $check.prop('disabled', true);
                });

                if (hotelAdvanceTotal() > 0) {
                    $formaPago.val('contado');
                    toggleMediosPago();
                }

                if (typeof calcular_totales_medio_recaudos === 'function') {
                    calcular_totales_medio_recaudos();
                }
            });

            $(document).on('click', '.hotel-remove-advance', function() {
                var $row = $(this).closest('tr');
                var advanceId = $row.attr('data-hotel_anticipo_id');
                var newAdvances = [];

                $.each(hotelAdvanceObjects, function(index, advance) {
                    if (advance.cxc_movimiento_id != advanceId) {
                        newAdvances.push(advance);
                    }
                });

                hotelAdvanceObjects = newAdvances;
                $('.hotel-advance-check[data-cxc_movimiento_id="' + advanceId + '"]').prop('disabled', false);
                $row.remove();
                toggleMediosPago();

                if (typeof calcular_totales_medio_recaudos === 'function') {
                    calcular_totales_medio_recaudos();
                }
            });

            function prepareHotelInvoiceSubmit($form) {
                if ($('input[name="invoice_document_type"]:checked').val() === 'electronic') {
                    $form.attr('target', '_blank');
                    window.setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                    return;
                }

                $form.removeAttr('target');
            }

            $('#hotel_generate_pos_invoice_form').on('submit', function(event) {
                var $form = $(this);
                var rows = hotelPaymentRows();
                var totalPayments = hotelPaymentTotal(rows);
                var formaPago = $formaPago.val();
                var hasAdvances = hotelAdvanceObjects.length > 0;
                var invoiceCustomerMode = $('input[name="invoice_customer_mode"]:checked').val();

                if ($form.data('hotel-confirmed') == '1') {
                    return true;
                }

                event.preventDefault();

                if (invoiceCustomerMode === 'other' && $('#hotel_invoice_cliente_id').val() === '') {
                    hotelSwalAlert('Debe seleccionar el cliente a quien se emitira la factura.');
                    return false;
                }

                function submitAfterConfirm() {
                    if (formaPago === 'credito' && !hasAdvances) {
                        $('#hotel_lineas_registros_medios_recaudos').val('[]');
                        $('#hotel_object_anticipos').val('null');
                    } else {
                        if (rows.length === 0) {
                            hotelSwalAlert('Debe ingresar los medios de pago para facturar de contado o aplicar anticipos.');
                            return false;
                        }

                        if (Math.abs(totalPayments - hotelOrderTotal) > 1) {
                            hotelSwalAlert('El valor total de los medios de pago debe ser igual al total del pedido hotelero.');
                            return false;
                        }

                        $('#hotel_lineas_registros_medios_recaudos').val(JSON.stringify(rows));
                        $('#hotel_object_anticipos').val(hasAdvances ? $.map(hotelAdvanceObjects, function(advance) {
                            return JSON.stringify(advance);
                        }).join(',') : 'null');
                    }

                    hotelSwalConfirm('Generar factura?', function() {
                        prepareHotelInvoiceSubmit($form);
                        $form.data('hotel-confirmed', '1');
                        hotelSetFormLoading($form, 'Generando...');
                        $form.submit();
                    });
                }

                if ($('input[name="invoice_document_type"]:checked').val() === 'electronic') {
                    var status = $('#hotel_electronic_resolution_status').val();
                    var message = $('#hotel_electronic_resolution_message').val();

                    if (status === 'error') {
                        hotelSwalAlert(message);
                        return false;
                    }

                    if (status === 'warning' && message !== '') {
                        hotelSwalConfirm(message + "\n\nDesea continuar con la generacion de la factura electronica?", submitAfterConfirm);
                        return false;
                    }
                }

                submitAfterConfirm();
                return false;
            });
        });
    </script>
    @include('hotel.partials.cliente_autocomplete_scripts')
@endsection
