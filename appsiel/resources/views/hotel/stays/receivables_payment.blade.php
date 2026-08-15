@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <style>
        .hotel-payment-card { border: 1px solid #ddd; border-radius: 5px; padding: 18px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,.12); }
        .hotel-payment-card h4 { margin-top: 0; color: #777; }
        .hotel-payment-summary { background: #9c27b0; color: #fff; padding: 16px 20px; border-radius: 4px; font-size: 18px; margin-bottom: 20px; }
        .hotel-payment-summary strong { font-size: 22px; }
        .hotel-payment-overlay { display:none; position:fixed; z-index:2000; inset:0; background:rgba(255,255,255,.82); text-align:center; padding-top:18%; }
        .hotel-payment-overlay i { font-size:52px; color:#3c8dbc; }
        .hotel-payment-overlay p { margin-top:12px; font-size:18px; font-weight:bold; }
        .hotel-money { text-align:right; white-space:nowrap; }
        .hotel-payment-error { display:none; }
    </style>

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8">
                    <h3>Pago de facturas crédito</h3>
                    <p>
                        Estadía #{{ $stay->id }} · Habitación {{ $stay->room ? $stay->room->room_number : $stay->room_id }} ·
                        <strong>{{ $stay->mainGuest && $stay->mainGuest->tercero ? $stay->mainGuest->tercero->descripcion : '' }}</strong>
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ url($hotelUrl::url('hotel/stays/'.$stay->id)) }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver a la estadía</a>
                </div>
            </div>

            <div id="hotel-payment-error" class="alert alert-warning hotel-payment-error" role="alert"></div>

            <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/receivables/payment')) }}" id="hotel-receivables-payment-form">
                {{ csrf_field() }}
                <input type="hidden" name="payment_methods" id="hotel-payment-methods-json" value="[]">

                <div class="hotel-payment-card">
                    <h4>Facturas pendientes por cobrar</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width:45px;"><input type="checkbox" id="hotel-select-all-invoices" title="Seleccionar todas"></th>
                                    <th>Documento</th>
                                    <th>Creado por</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th class="hotel-money">Valor</th>
                                    <th class="hotel-money">Pagado</th>
                                    <th class="hotel-money">Saldo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    <tr>
                                        <td><input type="checkbox" class="hotel-invoice-check" name="invoice_ids[]" value="{{ $invoice->id }}" data-balance="{{ (float)$invoice->saldo_pendiente }}" {{ is_array(old('invoice_ids')) && in_array($invoice->id, old('invoice_ids')) ? 'checked' : '' }}></td>
                                        <td>
                                            <a href="{{ url('enlace_show_documento/'.$hotelUrl::appId().'/'.$invoice->core_tipo_transaccion_id.'/'.$invoice->core_tipo_doc_app_id.'/'.$invoice->consecutivo) }}" target="_blank" rel="noopener noreferrer">
                                                {{ $invoice->documento }}
                                            </a>
                                        </td>
                                        <td>{{ $invoice->creatorLabel() }}</td>
                                        <td>{{ $invoice->fecha }}</td>
                                        <td>{{ $invoice->estado }}</td>
                                        <td class="hotel-money">{{ number_format($invoice->valor_documento, 2, ',', '.') }}</td>
                                        <td class="hotel-money">{{ number_format($invoice->valor_pagado, 2, ',', '.') }}</td>
                                        <td class="hotel-money"><strong>{{ number_format($invoice->saldo_pendiente, 2, ',', '.') }}</strong></td>
                                    </tr>
                                @endforeach
                                @if($invoices->count() == 0)
                                    <tr><td colspan="8">El huésped no tiene facturas crédito pendientes por cobrar.</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <div class="hotel-payment-card">
                            <h4>Anticipos / saldos a favor</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead><tr><th></th><th>Documento</th><th>Fecha</th><th>Detalle</th><th class="hotel-money">Disponible</th></tr></thead>
                                    <tbody>
                                        @foreach($advances as $advance)
                                            <tr>
                                                <td><input type="checkbox" class="hotel-advance-check" name="advance_ids[]" value="{{ $advance->id }}" data-balance="{{ abs((float)$advance->saldo_pendiente) }}" {{ is_array(old('advance_ids')) && in_array($advance->id, old('advance_ids')) ? 'checked' : '' }}></td>
                                                <td>{{ $advance->documento }}</td>
                                                <td>{{ $advance->fecha }}</td>
                                                <td>{{ $advance->detalle }}</td>
                                                <td class="hotel-money">{{ number_format(abs($advance->saldo_pendiente), 2, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                        @if($advances->count() == 0)
                                            <tr><td colspan="5">El huésped no tiene anticipos disponibles.</td></tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <p class="help-block">
                                <strong>Opcional:</strong> puede pagar sin seleccionar anticipos. Si selecciona alguno, se aplicará primero y el valor restante se cubrirá con los medios de pago.
                            </p>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div id="hotel_medios_pago_panel">
                            @include('tesoreria.incluir.medios_recaudos')
                        </div>
                    </div>
                </div>

                <div class="hotel-payment-summary">
                    <div class="row">
                        <div class="col-sm-4">Facturas seleccionadas<br><strong id="hotel-invoice-total">$ 0,00</strong></div>
                        <div class="col-sm-4">Anticipos aplicados<br><strong id="hotel-advance-total">- $ 0,00</strong></div>
                        <div class="col-sm-4 text-right">Pendiente por medios de pago<br><strong id="hotel-required-total">$ 0,00</strong></div>
                    </div>
                </div>

                <div class="text-right">
                    <a href="{{ url($hotelUrl::url('hotel/stays/'.$stay->id)) }}" class="btn btn-default">Cancelar</a>
                    <button type="submit" class="btn btn-success btn-lg" id="hotel-save-payment" {{ $invoices->count() == 0 ? 'disabled' : '' }}>
                        <i class="fa fa-save"></i> Guardar recaudo
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="hotel-payment-overlay" id="hotel-payment-overlay">
        <i class="fa fa-spinner fa-spin"></i>
        <p>Registrando recaudo y actualizando cartera…</p>
    </div>
@endsection

@section('scripts')
@parent
<script>
    $.fn.actualizar_medio_recaudo = function () {
        if (typeof window.hotelReceivablesRefreshTotals === 'function') {
            window.hotelReceivablesRefreshTotals();
        }
        return this;
    };
</script>
<script type="text/javascript" src="{{ asset('assets/js/tesoreria/medios_recaudos.js?aux=' . uniqid()) }}"></script>
<script>
    $(function () {
        var tolerance = 0.01;

        function money(value) {
            return '$ ' + Number(value || 0).toLocaleString('es-CO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function parseMoney(value) {
            value = (value || '').toString().replace('$', '').replace(/\s/g, '');
            if (value.indexOf(',') >= 0 && value.indexOf('.') >= 0) {
                value = value.replace(/\./g, '').replace(',', '.');
            } else {
                value = value.replace(/,/g, '');
            }
            var parsed = parseFloat(value);
            return isNaN(parsed) ? 0 : parsed;
        }

        function cellId(row, index) {
            var text = $.trim(row.find('td').eq(index).text());
            var match = text.match(/^(\d+)-/);
            return match ? parseInt(match[1], 10) : 0;
        }

        function paymentMethods() {
            var methods = [];
            $('#ingreso_registros_medios_recaudo tbody tr').each(function () {
                var row = $(this);
                var value = parseMoney(row.find('td').eq(4).text());
                var mediumId = cellId(row, 0);
                if (mediumId <= 0 || value <= 0) return;
                methods.push({
                    medium_id: mediumId,
                    cash_box_id: cellId(row, 2),
                    bank_account_id: cellId(row, 3),
                    reference: '',
                    value: value
                });
            });
            return methods;
        }

        function sumChecked(selector) {
            var total = 0;
            $(selector + ':checked').each(function () { total += parseFloat($(this).data('balance')) || 0; });
            return total;
        }

        function totals() {
            var invoices = sumChecked('.hotel-invoice-check');
            var advances = Math.min(invoices, sumChecked('.hotel-advance-check'));
            var required = Math.max(0, invoices - advances);
            var payments = 0;
            $.each(paymentMethods(), function (index, method) { payments += method.value; });
            $('#hotel-invoice-total').text(money(invoices));
            $('#hotel-advance-total').text('- ' + money(advances));
            $('#hotel-required-total').text(money(Math.max(0, required - payments)));
            updatePendingModalLabel(required, payments);
            var summary = { invoices: invoices, advances: advances, required: required, payments: payments };
            clearResolvedWarning(summary);
            return summary;
        }

        function updatePendingModalLabel(required, payments) {
            var pending = Math.max(0, required - payments);
            $('#lbl_hotel_vlr_pendiente_ingresar').text(money(pending));
        }

        function clearResolvedWarning(summary) {
            var warningType = $('#hotel-payment-error').data('validation-type');
            var resolved = warningType === 'invoice-selection' && summary.invoices > tolerance;
            resolved = resolved || (warningType === 'payment-total' && summary.invoices > tolerance && Math.abs(summary.payments - summary.required) <= tolerance);

            if (resolved) {
                $('#hotel-payment-error').hide().text('').removeData('validation-type');
            }
        }

        function showWarning(message, validationType) {
            $('#hotel-payment-error').text(message).data('validation-type', validationType).show();
            $('html, body').animate({ scrollTop: $('#hotel-payment-error').offset().top - 80 }, 250);
        }

        window.hotelReceivablesRefreshTotals = totals;

        $(document).on('change', '.hotel-invoice-check, .hotel-advance-check', totals);
        $('#hotel-select-all-invoices').on('change', function () {
            $('.hotel-invoice-check').prop('checked', $(this).is(':checked'));
            totals();
        });

        $('#recaudoModal').on('shown.bs.modal.hotelReceivablesPending', function () {
            if ($('#div_hotel_pendiente_ingresar_medio_recaudo').length === 0) {
                $('#form_registro').before('<div id="div_hotel_pendiente_ingresar_medio_recaudo" style="color:red; font-size:18px; margin-bottom:12px;">Pendiente por registrar: <span id="lbl_hotel_vlr_pendiente_ingresar">$ 0,00</span></div>');
            }
            totals();
        });
        $('#recaudoModal').on('hidden.bs.modal.hotelReceivablesPending', function () {
            $('#div_hotel_pendiente_ingresar_medio_recaudo').remove();
        });

        $('#hotel-receivables-payment-form').on('submit', function (event) {
            event.preventDefault();
            $('#hotel-payment-error').hide().text('').removeData('validation-type');
            var summary = totals();
            if (summary.invoices <= tolerance) return showWarning('Seleccione al menos una factura pendiente por cobrar.', 'invoice-selection');

            var methods = paymentMethods();
            if (summary.payments < summary.required - tolerance) {
                return showWarning('Aún falta registrar ' + money(summary.required - summary.payments) + ' en medios de pago. Los anticipos son opcionales.', 'payment-total');
            }
            if (summary.payments > summary.required + tolerance) {
                return showWarning('Los medios de pago superan en ' + money(summary.payments - summary.required) + ' el valor pendiente del recaudo. Ajuste el valor para continuar.', 'payment-total');
            }
            if (!window.confirm('¿Confirma el pago de las facturas seleccionadas por ' + money(summary.invoices) + '? Esta operación generará los movimientos de tesorería y CxC.')) return;

            $('#hotel-payment-methods-json').val(JSON.stringify(methods));
            $('#hotel-save-payment').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Guardando…');
            $('#hotel-payment-overlay').show();
            this.submit();
        });

        totals();
    });
</script>
@endsection
