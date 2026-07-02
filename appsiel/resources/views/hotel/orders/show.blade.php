@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8">
                    <h3>Pedido hotelero {{ $order->document_number }}</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ url($hotelUrl::url('hotel/stays/'.$order->stay_id, array('id_modelo' => $hotelUrl::modelId('App\\Hotel\\HotelStay')))) }}" class="btn btn-default btn-sm">Volver a estadia</a>
                </div>
            </div>
            <table class="table table-bordered">
                <tr><th>Cliente</th><td>{{ $order->cliente && $order->cliente->tercero ? $order->cliente->tercero->descripcion : $order->cliente_id }}</td></tr>
                <tr><th>Habitacion</th><td>{{ $order->stay && $order->stay->room ? $order->stay->room->room_number : '' }}</td></tr>
                <tr><th>Fecha</th><td>{{ $order->order_date }}</td></tr>
                <tr><th>Estado</th><td>{{ $order->status }}</td></tr>
                <tr><th>Factura</th><td>{{ $order->invoice_type }} {{ $order->sales_doc_id ? 'Ventas #'.$order->sales_doc_id : '' }} {{ $order->pos_doc_id ? 'POS #'.$order->pos_doc_id : '' }}</td></tr>
            </table>

            <h4>Lineas del pedido</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripcion</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Descuento</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                            <th>Origen</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>
                        @foreach($order->lines as $line)
                            <?php $total += $line->line_total; ?>
                            <tr>
                                <form method="POST" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/lines/'.$line->id.'/update')) }}">
                                    {{ csrf_field() }}
                                    <td>{{ $line->product ? $line->product->descripcion : $line->producto_id }}</td>
                                    <td><input type="text" name="description" class="form-control input-sm" value="{{ $line->description }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="quantity" class="form-control input-sm text-right" value="{{ $line->quantity }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="unit_price" class="form-control input-sm text-right" value="{{ $line->unit_price }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="discount" class="form-control input-sm text-right" value="{{ $line->discount }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="tax_value" class="form-control input-sm text-right" value="{{ $line->tax_value }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td class="text-right">{{ number_format($line->line_total, 2, ',', '.') }}</td>
                                    <td>{{ $line->source_type }}</td>
                                    <td>
                                        @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                                            <button class="btn btn-warning btn-xs">Actualizar</button>
                                        @endif
                                </form>
                                        @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                                            <form method="POST" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/lines/'.$line->id.'/delete')) }}" style="display:inline-block;">
                                                {{ csrf_field() }}
                                                <button class="btn btn-danger btn-xs" onclick="return confirm('Eliminar linea?')">Eliminar</button>
                                            </form>
                                        @endif
                                    </td>
                            </tr>
                        @endforeach
                        <tr>
                            <th colspan="6" class="text-right">Total</th>
                            <th class="text-right">{{ number_format($total, 2, ',', '.') }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                <h4>Agregar consumo</h4>
                <form method="POST" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/lines')) }}">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Producto</label>
                                <select name="producto_id" id="hotel_producto_id" class="form-control" required>
                                    @foreach($products as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="text" name="quantity" id="hotel_quantity" class="form-control" value="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Precio</label>
                                <input type="text" name="unit_price" id="hotel_unit_price" class="form-control" placeholder="Automatico">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Descripcion</label>
                                <input type="text" name="description" id="hotel_line_description" class="form-control">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="source_type" value="MANUAL">
                    <button class="btn btn-primary">Agregar linea</button>
                    <br>
                </form>
            @endif
        </div>

        @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
            <div class="marco_formulario">
                <h4>Anticipos / saldos a favor</h4>
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
            </div>

            <div id="hotel_medios_pago_panel">
                @include('tesoreria.incluir.medios_recaudos')
            </div>
        @endif

        <div class="marco_formulario">
                <h4>Generar factura</h4>
            @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                <!-- <form method="POST" action="{ { url($hotelUrl::url('hotel/orders/'.$order->id.'/generate-standard-invoice')) }}" style="display:inline-block;">
                    {{ csrf_field() }}
                    <button class="btn btn-success" onclick="return confirm('Generar factura estandar?')">Generar factura estandar</button>
                </form>
                -->
                <form method="POST" id="hotel_generate_pos_invoice_form" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/generate-pos-invoice')) }}" style="display:inline-block;">
                    {{ csrf_field() }}
                    <label for="hotel_forma_pago">Forma de pago:</label>
                    <select name="forma_pago" id="hotel_forma_pago" class="form-control" style="display:inline-block; width:auto;">
                        <option value="contado">Contado</option>
                        <option value="credito">Credito</option>
                    </select>
                    <br>
                    <br>
                    <input type="hidden" name="lineas_registros_medios_recaudos" id="hotel_lineas_registros_medios_recaudos" value="[]">
                    <input type="hidden" name="object_anticipos" id="hotel_object_anticipos" value="null">
                    <button class="btn btn-primary" onclick="return confirm('Generar factura POS?')"> <i class="fa fa-save"></i> Guardar </button>
                </form>
            @endif
        </div>
    </div>
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
            var $formaPago = $('#hotel_forma_pago');
            var $mediosPagoPanel = $('#hotel_medios_pago_panel');
            var hotelOrderTotal = {{ (float)$order->lines->sum('line_total') }};
            var hotelAdvanceObjects = [];

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
                    return;
                }

                $precio.attr('placeholder', 'Consultando...');

                $.get("{{ url('inv_consultar_productos') }}", {
                    producto_id: productoId,
                    cliente_id: "{{ $order->cliente_id }}",
                    lista_precios_id: "{{ $order->cliente ? $order->cliente->lista_precios_id : '' }}",
                    fecha: "{{ date('Y-m-d') }}",
                    cantidad: $cantidad.val()
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

                    $precio.attr('placeholder', 'Automatico');
                }).fail(function() {
                    $precio.attr('placeholder', 'Automatico');
                });
            }

            $producto.on('change', cargarPrecioProducto);
            cargarPrecioProducto();

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

            function hotelAppendAdvancePayment(advance, value) {
                var exists = false;
                $('#ingreso_registros_medios_recaudo tbody tr').each(function() {
                    var $row = $(this);
                    if ($row.attr('data-hotel_anticipo_id') == advance.cxc_movimiento_id) {
                        exists = true;
                    }
                });

                if (exists) {
                    return;
                }

                $('#ingreso_registros_medios_recaudo tbody').append(
                    '<tr data-hotel_anticipo_id="' + advance.cxc_movimiento_id + '">' +
                        '<td>0-Anticipo</td>' +
                        '<td>0-Cruce anticipos</td>' +
                        '<td>0-</td>' +
                        '<td>0-</td>' +
                        '<td class="text-right">' + hotelMoneyLabel(value) + '</td>' +
                        '<td><button type="button" class="btn btn-danger btn-xs hotel-remove-advance"><i class="fa fa-trash"></i></button></td>' +
                    '</tr>'
                );
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

            function toggleMediosPago() {
                if ($formaPago.val() === 'credito' && hotelAdvanceObjects.length === 0) {
                    $mediosPagoPanel.hide();
                    return;
                }

                $mediosPagoPanel.show();
            }

            $formaPago.on('change', toggleMediosPago);
            toggleMediosPago();

            $('#hotel_btn_aplicar_anticipo').on('click', function() {
                var rows = hotelPaymentRows();
                var totalPayments = hotelPaymentTotal(rows);
                var remaining = hotelOrderTotal - totalPayments;

                if (remaining <= 1) {
                    alert('El pedido ya tiene medios de pago por el total.');
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

                    if (alreadyApplied || remaining <= 1) {
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

                    hotelAdvanceObjects.push(advance);
                    hotelAppendAdvancePayment(advance, valueToApply);
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

            $('#hotel_generate_pos_invoice_form').on('submit', function(event) {
                var rows = hotelPaymentRows();
                var totalPayments = hotelPaymentTotal(rows);
                var formaPago = $formaPago.val();
                var hasAdvances = hotelAdvanceObjects.length > 0;

                if (formaPago === 'credito' && !hasAdvances) {
                    $('#hotel_lineas_registros_medios_recaudos').val('[]');
                    $('#hotel_object_anticipos').val('null');
                    return true;
                }

                if (rows.length === 0) {
                    event.preventDefault();
                    alert('Debe ingresar los medios de pago para facturar de contado o aplicar anticipos.');
                    return false;
                }

                if (Math.abs(totalPayments - hotelOrderTotal) > 1) {
                    event.preventDefault();
                    alert('El valor total de los medios de pago debe ser igual al total del pedido hotelero.');
                    return false;
                }

                $('#hotel_lineas_registros_medios_recaudos').val(JSON.stringify(rows));
                $('#hotel_object_anticipos').val(hasAdvances ? $.map(hotelAdvanceObjects, function(advance) {
                    return JSON.stringify(advance);
                }).join(',') : 'null');
                return true;
            });
        });
    </script>
@endsection
