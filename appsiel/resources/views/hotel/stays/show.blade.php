@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    <?php $form_create = array('campos' => array(array('tipo' => 'cliente_autocomplete'))); ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8">
                    <h3>Estadia #{{ $stay->id }} - Habitacion {{ $stay->room ? $stay->room->room_number : $stay->room_id }}</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ url($hotelUrl::url('hotel?id=22')) }}" class="btn btn-default btn-sm">Volver</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <h4>
                        Informacion estadia
                        @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA && (!isset($editBlockMessage) || $editBlockMessage == ''))
                            <a href="{{ url($hotelUrl::url('web/'.$stay->id.'/edit?id=22&id_modelo=364&id_transaccion=')) }}"><i class="fa fa-edit"></i></a>
                        @elseif($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA && isset($editBlockMessage) && $editBlockMessage != '')
                            <i class="fa fa-edit text-muted" title="{{ $editBlockMessage }}"></i>
                        @endif
                    </h4>
                    <table class="table table-bordered">
                        <tr><th>Huesped principal</th><td>{{ $stay->mainGuest && $stay->mainGuest->tercero ? $stay->mainGuest->tercero->descripcion : $stay->main_cliente_id }}</td><th>Huespedes</th><td>{{ $stay->total_guests }} ({{ $stay->adults_count }} adultos, {{ $stay->children_count }} niños)</td></tr>
                        <tr><th>Check-in</th><td>{{ $stay->check_in_at }}</td><th>Check-out</th><td>{{ $stay->check_out_at }}</td></tr>
                        <tr><th>Salida esperada</th><td>{{ $stay->expected_check_out_at }}</td><th>Dias estadia</th><td>{{ $stay->stayDays() }}</td></tr>
                        <tr><th>Notas</th><td>{{ $stay->notes }}</td><th>Estado</th><td>{{ $stay->status }}</td></tr>
                        <tr><th>Creador por</th><td>{{ $stay->creador_por ? $stay->creador_por->name : '--' }}</td><th>Modificado por</th><td>{{ $stay->modificador_por ? $stay->modificador_por->name : '--' }}</td></tr>
                    </table>


                    <div class="btn-group" role="group">

                        @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                            <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/orders')) }}" style="display:inline-block;">
                                {{ csrf_field() }}
                                <button class="btn btn-primary btn-sm">Nuevo pedido</button>
                            </form>
                            @if(isset($checkOutBlockMessage) && $checkOutBlockMessage != '')
                                <button type="button" class="btn btn-success btn-sm" disabled title="{{ $checkOutBlockMessage }}">Check-out</button>
                            @else
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#hotelCheckOutModal">Check-out</button>
                            @endif
                            @if(isset($cancelBlockMessage) && $cancelBlockMessage != '')
                                <button type="button" class="btn btn-danger btn-sm" disabled title="{{ $cancelBlockMessage }}">Anular</button>
                            @else
                                <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/cancel')) }}" style="display:inline-block;">
                                    {{ csrf_field() }}
                                    <button class="btn btn-danger btn-sm hotel-confirm-submit" data-message="Anular estadia?">Anular</button>
                                </form>
                            @endif
                        @endif
                    </div>
                    <!-- @ if(isset($cancelBlockMessage) && $cancelBlockMessage != '')
                        <br><br>
                        <div class="alert alert-warning">{{ $cancelBlockMessage }}</div>
                    @ endif
                    -->
                    @if(isset($checkOutBlockMessage) && $checkOutBlockMessage != '')
                        <br><br>
                        <div class="alert alert-warning">{{ $checkOutBlockMessage }}</div>
                    @endif
                </div>

                <div class="col-md-4">
                    <h4>Huespedes</h4>
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>Cliente</th><th>Principal</th><th>Accion</th></tr></thead>
                        <tbody>
                            @foreach($stay->guests as $guest)
                                <?php $isMainGuest = (int)$guest->is_main_guest == 1 || (int)$guest->cliente_id == (int)$stay->main_cliente_id; ?>
                                <tr>
                                    <td>{{ $guest->cliente && $guest->cliente->tercero ? $guest->cliente->tercero->descripcion : $guest->cliente_id }}</td>
                                    <td>{{ $isMainGuest ? 'Si' : 'No' }}</td>
                                    <td>
                                        @if(!$isMainGuest && $stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                                            <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/guests/'.$guest->id.'/delete')) }}">
                                                {{ csrf_field() }}
                                                <button class="btn btn-danger btn-xs" title="Eliminar acompañante">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                        <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/guests')) }}" class="form-inline">
                            {{ csrf_field() }}
                            <div class="hotel-cliente-autocomplete-wrap" style="position: relative; display: inline-block; min-width: 260px;">
                                <label>Acompañante:</label>
                                <input type="text" class="form-control hotel-cliente-autocomplete-input" data-target="hotel_stay_guest_cliente_id" placeholder="Buscar acompañante" autocomplete="off" required>
                                <input type="hidden" name="cliente_id" id="hotel_stay_guest_cliente_id" required>
                                <div class="hotel-cliente-autocomplete-results list-group" style="display:none; position:absolute; z-index:1050; left:0; right:0;"></div>
                            </div>
                            <button class="btn btn-primary" title="Agregar acompañante"> <i class="fa fa-plus"></i> </button>
                        </form>
                    @endif
                </div>
            </div>

            <hr>

            <?php
                $saldoPendientePedidosAbiertos = 0;
                foreach ($stay->orders as $order) {
                    if ($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO) {
                        foreach ($order->lines as $line) {
                            $saldoPendientePedidosAbiertos += (float)$line->line_total;
                        }
                    }
                }

                $saldoAnticiposDisponibles = 0;
                if (isset($anticipos) && is_array($anticipos)) {
                    foreach ($anticipos as $anticipo) {
                        $saldoAnticipo = isset($anticipo['saldo_pendiente']) ? (float)$anticipo['saldo_pendiente'] : 0;
                        if ($saldoAnticipo < 0) {
                            $saldoAnticiposDisponibles += abs($saldoAnticipo);
                        }
                    }
                }

                $saldoPendienteNeto = max(0, $saldoPendientePedidosAbiertos - $saldoAnticiposDisponibles);
            ?>
            <div class="alert alert-info" style="font-size:18px; font-weight:bold;">
                Saldo pendiente por registrar:
                <span class="pull-right">$ {{ number_format($saldoPendienteNeto, 2, ',', '.') }}</span>
                <div class="clearfix"></div>
                <small style="font-weight:normal;">
                    Pedidos abiertos: $ {{ number_format($saldoPendientePedidosAbiertos, 2, ',', '.') }}
                    | Anticipos disponibles: $ {{ number_format($saldoAnticiposDisponibles, 2, ',', '.') }}
                </small>
            </div>

            <h4>Pedidos hoteleros</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Creador por</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Factura</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stay->orders as $order)
                            <?php
                                $orderTotal = 0;
                                foreach ($order->lines as $line) {
                                    $orderTotal += (float)$line->line_total;
                                }
                            ?>
                            <tr>
                                <td>{{ $order->document_number ? $order->document_number : 'PED-' . $order->id }}</td>
                                <td>{{ $order->creador_por ? $order->creador_por->name : '--' }}</td>
                                <td>{{ $order->order_date }}</td>
                                <td>{{ $order->status }}</td>
                                <td class="text-right">{{ number_format($orderTotal, 2, ',', '.') }}</td>
                                <td>
                                    @if($order->invoiceUrl() != '')
                                        <a href="{{ $order->invoiceUrl() }}" target="_blank">{{ $order->invoiceLabel() }}</a>
                                    @else
                                        {{ $order->invoiceLabel() }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ url($hotelUrl::url('hotel/orders/'.$order->id, array('id_modelo' => $hotelUrl::modelId('App\\Hotel\\HotelOrderHeader')))) }}" class="btn btn-primary btn-xs">Ver pedido</a>
                                    @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO && isset($canCancelHotelOrder) && $canCancelHotelOrder)
                                        <form method="POST" action="{{ url($hotelUrl::url('hotel/orders/'.$order->id.'/cancel', array('id_modelo' => $hotelUrl::modelId('App\\Hotel\\HotelOrderHeader')))) }}" style="display:inline-block;">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-danger btn-xs hotel-confirm-submit" data-message="Anular pedido hotelero?">Anular</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        @if(count($stay->orders) == 0)
                            <tr>
                                <td colspan="7">No hay pedidos hoteleros registrados.</td>
                            </tr>
                        @endif
                        <tr>
                            <td colspan="4" class="text-right"><strong>Pedidos abiertos</strong></td>
                            <td class="text-right"><strong>{{ number_format($saldoPendientePedidosAbiertos, 2, ',', '.') }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Anticipos disponibles</strong></td>
                            <td class="text-right"><strong>- {{ number_format($saldoAnticiposDisponibles, 2, ',', '.') }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Saldo pendiente por registrar</strong></td>
                            <td class="text-right"><strong>{{ number_format($saldoPendienteNeto, 2, ',', '.') }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
        <div class="modal fade" id="hotelCheckOutModal" tabindex="-1" role="dialog" aria-labelledby="hotelCheckOutModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/check-out')) }}">
                        {{ csrf_field() }}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="hotelCheckOutModalLabel">Confirmar check-out</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="hotel_check_out_at">Fecha y hora de check-out</label>
                                <input type="datetime-local" name="check_out_at" id="hotel_check_out_at" class="form-control" value="{{ date('Y-m-d\\TH:i') }}" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">Confirmar check-out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @include('hotel.partials.cliente_autocomplete_modal')
@endsection

@section('scripts')
    @parent
    @include('hotel.partials.cliente_autocomplete_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.hotel-confirm-submit').on('click', function(event) {
                event.preventDefault();

                var $button = $(this);
                var $form = $button.closest('form');
                var message = $button.data('message') || 'Confirmar accion?';

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
                            $button.prop('disabled', true);
                            $button.data('hotel-original-html', $button.html());
                            $button.html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
                            $form.submit();
                        }
                    });
                } else if (window.confirm(message)) {
                    $button.prop('disabled', true);
                    $button.data('hotel-original-html', $button.html());
                    $button.html('<i class="fa fa-spinner fa-spin"></i> Procesando...');
                    $form.submit();
                }
            });
        });
    </script>
@endsection
