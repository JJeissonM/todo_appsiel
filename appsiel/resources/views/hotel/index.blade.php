@extends('layouts.principal')

@section('estilos_2')
    <style>
        .hotel-topbar {
            background: #34464c;
            color: #fff;
            padding: 12px 18px;
            margin: -15px -15px 20px -15px;
            font-size: 18px;
        }

        .hotel-filters {
            margin-bottom: 18px;
        }

        .hotel-summary {
            margin-bottom: 18px;
        }

        .hotel-summary-item {
            display: inline-block;
            min-width: 118px;
            margin: 0 8px 8px 0;
            padding: 8px 12px;
            border-radius: 4px;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        .hotel-grid {
            display: -webkit-flex;
            display: flex;
            -webkit-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-left: -12px;
            margin-right: -12px;
        }

        .hotel-room-wrap {
            width: 25%;
            padding: 12px;
        }

        .hotel-room {
            color: #fff;
            min-height: 158px;
            position: relative;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .18);
            overflow: hidden;
            border-radius: 4px;
        }

        .hotel-room-main {
            min-height: 116px;
            padding: 18px 16px 12px 16px;
            position: relative;
        }

        .hotel-room-number {
            font-size: 34px;
            font-weight: bold;
            line-height: 1;
        }

        .hotel-room-type {
            font-size: 16px;
            margin-top: 12px;
            line-height: 1.25;
            max-width: 78%;
            min-height: 40px;
            word-break: normal;
        }

        .hotel-room-meta {
            font-size: 12px;
            margin-top: 8px;
            opacity: .95;
        }

        .hotel-room-icon {
            position: absolute;
            right: 18px;
            top: 42px;
            font-size: 48px;
            opacity: .85;
        }

        .hotel-room-status {
            min-height: 42px;
            padding: 10px 12px;
            text-align: center;
            font-weight: bold;
            background: rgba(0, 0, 0, .16);
        }

        .hotel-room-actions {
            background: rgba(255, 255, 255, .96);
            padding: 8px;
            min-height: 45px;
        }

        .hotel-room-actions .btn {
            margin: 0 4px 4px 0;
        }

        .hotel-room-disponible,
        .hotel-summary-disponible {
            background: #07945d;
        }

        .hotel-room-ocupada,
        .hotel-summary-ocupada {
            background: #c7553c;
        }

        .hotel-room-reservada,
        .hotel-summary-reservada {
            background: #ce80f0;
        }

        .hotel-room-limpieza,
        .hotel-summary-limpieza {
            background: #3b8fc0;
        }

        .hotel-room-mantenimiento,
        .hotel-summary-mantenimiento {
            background: #d49a28;
        }

        .hotel-room-bloqueada,
        .hotel-summary-bloqueada {
            background: #606873;
        }

        .hotel-pdv-panel {
            background: #fff;
            border: 1px solid #eee;
            margin-bottom: 16px;
            padding: 18px 24px;
            min-height: 140px;
            border-radius: 4px;
        }

        .hotel-pdv-name {
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .hotel-pdv-status {
            margin-bottom: 28px;
        }

        @media (max-width: 1199px) {
            .hotel-room-wrap {
                width: 33.3333%;
            }
        }

        @media (max-width: 767px) {
            .hotel-room-wrap {
                width: 100%;
            }

            .hotel-room-number {
                font-size: 30px;
            }
        }
    </style>
@endsection

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    <?php $vistaController = 'App\\Http\\Controllers\\Sistema\\VistaController'; ?>
    <?php $returnTo = \Illuminate\Support\Facades\Request::fullUrl(); ?>

    <div class="container-fluid">

        <div class="hotel-pdv-panel">
            <div class="row">
                <div class="col-md-4">
                    @if(!is_null($pdvData['pdv']))
                        <div class="hotel-pdv-name">{{ $pdvData['pdv']->descripcion }}</div>
                        <hr>
                        <div class="hotel-pdv-status">
                            <b>Estado:</b>
                            <i class="fa fa-circle" style="color: {{ $pdvData['color'] }}"></i>
                            {{ $pdvData['status'] }}
                            <small>| desde {{ $pdvData['since'] }}</small>
                        </div>
                        <div>
                            @if($pdvData['status'] != 'Abierto')
                                <a href="{{ url($pdvData['apertura_url']) }}" class="btn btn-success btn-xs">Apertura</a>
                            @endif
                            @if($pdvData['status'] == 'Abierto')
                                <a href="{{ url($pdvData['cierre_url']) }}" class="btn btn-danger btn-xs">Cierre</a>
                            @endif
                            @if($pdvData['status'] != 'Abierto')
                                <a href="{{ url($pdvData['arqueo_url']) }}" class="btn btn-info btn-xs">Hacer arqueo</a>
                            @endif
                        </div>
                    @else
                        <div class="hotel-pdv-name">{{ $pdvData['status'] }}</div>
                        <hr>
                        <div class="{{ !empty($pdvData['is_admin']) ? 'alert alert-info' : 'alert alert-warning' }}">{{ $pdvData['message'] }}</div>
                    @endif
                </div>
            </div>
        </div>

        @if($dashboardEnabled)
            @if(!$dashboardCanTransact)
                <div class="alert alert-info">
                    La apertura del turno habilita el Dashboard Gestión Hotelera.
                </div>
            @endif

            <div class="marco_formulario">
                <div class="row">
                    <div class="col-md-8">
                        <div class="hotel-filters">
                            <h4>Filtros</h4>
                            <form method="GET" action="{{ url('hotel') }}" class="form-inline">
                                @if(Input::get('id') != '')
                                    <input type="hidden" name="id" value="{{ Input::get('id') }}">
                                @endif
                                @if(Input::get('id_modelo') != '')
                                    <input type="hidden" name="id_modelo" value="{{ Input::get('id_modelo') }}">
                                @endif
                                @if(Input::get('id_transaccion') != '')
                                    <input type="hidden" name="id_transaccion" value="{{ Input::get('id_transaccion') }}">
                                @endif

                                <select name="floor" class="form-control" style="min-width: 260px;">
                                    <option value="">Seleccione el nivel/piso</option>
                                    @foreach($floors as $floor => $label)
                                        <option value="{{ $floor }}" {{ Input::get('floor') == $floor ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>

                                <select name="status" class="form-control" style="min-width: 210px;">
                                    <option value="">Todos los estados</option>
                                    @foreach($statuses as $status => $label)
                                        <option value="{{ $status }}" {{ Input::get('status') == $status ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>

                                <button class="btn btn-primary"><i class="fa fa-filter"></i></button>
                                <a href="{{ url($hotelUrl::url('hotel')) }}" class="btn btn-default"><i class="fa fa-refresh"></i></a>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#hotelGuestCreateModal"><i class="fa fa-plus"></i> Huesped</button>
                        <a href="{{ url($hotelUrl::url('web/create', array('id_modelo' => $reservationModelId))) }}" class="btn btn-info"><i class="fa fa-calendar"></i> Reserva</a>

                        @if($dashboardCanTransact)
                            <!-- <a href="{ { url($hotelUrl::url('web/create', array('id_modelo' => $stayModelId))) }}" class="btn btn-info"><i class="fa fa-sign-in"></i> Check-in</a> -->
                            <a href="{{ url($pdvData['factura_directa_url']) }}" class="btn btn-success" target="_blank"><i class="fa fa-calculator"></i> Fact. Directa</a>
                        @endif
                    </div>
                </div>

            </div>

            <div class="hotel-summary">
                @foreach($summary as $status => $count)
                    <span class="hotel-summary-item hotel-summary-{{ strtolower($status) }}">{{ $status }}: {{ $count }}</span>
                @endforeach
            </div>

            <div class="hotel-grid">
                @foreach($rooms as $room)
                    <?php
                        $stay = $room->activeStay->first();
                        $todayReservation = $room->activeTodayReservation->first();
                        $dashboardStatus = $room->status;
                        if ($stay) {
                            $dashboardStatus = App\Hotel\HotelRoom::STATUS_OCUPADA;
                        } elseif ($todayReservation && $dashboardStatus == App\Hotel\HotelRoom::STATUS_DISPONIBLE) {
                            $dashboardStatus = App\Hotel\HotelRoom::STATUS_RESERVADA;
                        }
                        $statusClass = 'hotel-room-' . strtolower($dashboardStatus);
                        $guestName = '';
                        $expected_check_out_at = '';
                        if ($stay && $stay->mainGuest && $stay->mainGuest->tercero) {
                            $guestName = $stay->mainGuest->tercero->descripcion;

                            $expected_check_out_at = Carbon\Carbon::parse($stay->expected_check_out_at)->format('d/m/Y');
                        } elseif ($todayReservation && $todayReservation->cliente && $todayReservation->cliente->tercero) {
                            $guestName = 'Reserva: ' . $todayReservation->cliente->tercero->descripcion;
                        }
                    ?>
                    <div class="hotel-room-wrap">
                        <div class="hotel-room {{ $statusClass }}">
                            <div class="hotel-room-main">
                                <div class="hotel-room-number">
                                    HAB. {{ $room->room_number }}
                                    <small style="font-size: 12px; display: block;"> (${{ number_format($stay->getSaldoPendienteNeto(), 0, '.', ',') }})</small>
                                </div>
                                <div class="hotel-room-type">
                                    {{ $room->description != '' ? $room->description : ucfirst(strtolower($room->room_type)) }}
                                </div>
                                <div class="hotel-room-meta">
                                    F.S.: {{ $expected_check_out_at }}
                                    @if($guestName != '')
                                        <br>{{ substr($guestName, 0, 34) }}
                                    @endif
                                </div>
                            </div>
                            <div class="hotel-room-status">
                                {{ $dashboardStatus }} <i class="fa fa-arrow-circle-right"></i>
                            </div>
                            <div class="hotel-room-actions">
                                <!-- <a href="{ { url('web/'.$room->id.'?id='.$appId.'&id_modelo='.$roomModelId) }}" class="btn btn-default btn-xs" title="Ver habitacion"><i class="fa fa-eye"></i></a> -->

                                @if($dashboardStatus == App\Hotel\HotelRoom::STATUS_DISPONIBLE && $room->is_active)
                                    @if($dashboardCanTransact)
                                        <a href="{{ url($hotelUrl::url('web/create', array('id_modelo' => $stayModelId, 'room_id' => $room->id))) }}" class="btn btn-success btn-xs"><i class="fa fa-sign-in"></i> Check-in</a>
                                    @endif
                                    <a href="{{ url($hotelUrl::url('web/create', array('id_modelo' => $reservationModelId, 'room_id' => $room->id))) }}" class="btn btn-info btn-xs"><i class="fa fa-calendar"></i> Reservar</a>
                                    <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/status', array('id_modelo' => $roomModelId))) }}" style="display:inline-block;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="status" value="{{ App\Hotel\HotelRoom::STATUS_MANTENIMIENTO }}">
                                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                        <button class="btn btn-warning btn-xs" title="Mantenimiento"><i class="fa fa-wrench"></i></button>
                                    </form>
                                    <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/status', array('id_modelo' => $roomModelId))) }}" style="display:inline-block;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="status" value="{{ App\Hotel\HotelRoom::STATUS_BLOQUEADA }}">
                                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                        <button class="btn btn-danger btn-xs" title="Bloquear"><i class="fa fa-lock"></i></button>
                                    </form>
                                @elseif($dashboardStatus == App\Hotel\HotelRoom::STATUS_RESERVADA && $todayReservation)
                                    @if($dashboardCanTransact)
                                        <a href="{{ url($hotelUrl::url('web/create', array('id_modelo' => $stayModelId, 'room_id' => $room->id, 'main_cliente_id' => $todayReservation->cliente_id))) }}" class="btn btn-success btn-xs"><i class="fa fa-sign-in"></i> Check-in</a>
                                    @endif
                                    <form method="POST" action="{{ url($hotelUrl::url('hotel/reservations/'.$todayReservation->id.'/cancel', array('id_modelo' => $reservationModelId))) }}" style="display:inline-block;">
                                        {{ csrf_field() }}
                                        <button class="btn btn-danger btn-xs hotel-confirm-submit" data-message="Anular reserva?"><i class="fa fa-ban"></i> Anular</button>
                                    </form>
                                @elseif($dashboardStatus == App\Hotel\HotelRoom::STATUS_OCUPADA && $stay)
                                    <a href="{{ url('hotel/stays/'.$stay->id.'?id='.$appId.'&id_modelo='.$stayModelId) }}" class="btn btn-danger btn-xs"><i class="fa fa-user"></i> Estadia</a>
                                    <?php
                                        $dashboardOrder = null;
                                        $dashboardOpenOrdersCount = 0;
                                        foreach ($stay->orders as $stayOrder) {
                                            if ($stayOrder->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO) {
                                                $dashboardOpenOrdersCount++;
                                                $dashboardOrder = $stayOrder;
                                            }
                                        }
                                        if (is_null($dashboardOrder)) {
                                            $dashboardOrder = $stay->orders->first();
                                        }
                                    ?>
                                    @if($dashboardOrder)
                                        <a href="{{ url($hotelUrl::url('hotel/orders/'.$dashboardOrder->id, array('id_modelo' => $orderModelId))) }}" class="btn btn-primary btn-xs"><i class="fa fa-file-invoice"></i> Facturar</a>
                                    @endif
                                    @if($dashboardOpenOrdersCount > 0)
                                        <button type="button"
                                                class="btn btn-success btn-xs"
                                                disabled
                                                title="No se puede registrar check-out porque la estadia tiene pedidos hoteleros abiertos pendientes por facturar.">
                                            <i class="fa fa-sign-out"></i> Check-out
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-success btn-xs hotel-dashboard-checkout-btn"
                                                data-action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/check-out', array('id_modelo' => $stayModelId))) }}"
                                                data-room="{{ $room->room_number }}"
                                                data-checkout-at="{{ date('Y-m-d\\TH:i') }}">
                                            <i class="fa fa-sign-out"></i> Check-out
                                        </button>
                                    @endif
                                @elseif($dashboardStatus == App\Hotel\HotelRoom::STATUS_LIMPIEZA)
                                    <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/status', array('id_modelo' => $roomModelId))) }}" style="display:inline-block;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="status" value="{{ App\Hotel\HotelRoom::STATUS_DISPONIBLE }}">
                                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                        <button class="btn btn-success btn-xs"><i class="fa fa-check"></i> Disponible</button>
                                    </form>
                                    <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/status', array('id_modelo' => $roomModelId))) }}" style="display:inline-block;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="status" value="{{ App\Hotel\HotelRoom::STATUS_MANTENIMIENTO }}">
                                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                        <button class="btn btn-warning btn-xs"><i class="fa fa-wrench"></i></button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/status', array('id_modelo' => $roomModelId))) }}" style="display:inline-block;">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="status" value="{{ App\Hotel\HotelRoom::STATUS_DISPONIBLE }}">
                                        <input type="hidden" name="return_to" value="{{ $returnTo }}">
                                        <button class="btn btn-success btn-xs"><i class="fa fa-check"></i> Disponible</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(count($rooms) == 0)
                <div class="alert alert-info">No hay habitaciones para los filtros seleccionados.</div>
            @endif

            <div class="marco_formulario">
                <h4>Reservas activas</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Habitacion</th>
                                <th>Huesped</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th>Estado</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeReservations as $reservation)
                                <tr>
                                    <td>{{ $reservation->room ? $reservation->room->room_number : $reservation->room_id }}</td>
                                    <td>{{ $reservation->cliente && $reservation->cliente->tercero ? $reservation->cliente->tercero->descripcion : $reservation->cliente_id }}</td>
                                    <td>{{ $reservation->reserved_from }}</td>
                                    <td>{{ $reservation->reserved_until }}</td>
                                    <td>{{ $reservation->status }}</td>
                                    <td>
                                        <form method="POST" action="{{ url($hotelUrl::url('hotel/reservations/'.$reservation->id.'/cancel', array('id_modelo' => $reservationModelId))) }}" style="display:inline-block;">
                                            {{ csrf_field() }}
                                            <button class="btn btn-danger btn-xs hotel-confirm-submit" data-message="Anular reserva?">Anular</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            @if(count($activeReservations) == 0)
                                <tr>
                                    <td colspan="6">No hay reservas activas.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="marco_formulario">
                <h4>Anticipos de clientes</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Documento</th>
                                <th>Fecha</th>
                                <th>Detalle</th>
                                <th>Saldo a favor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customerAdvances as $advance)
                                <tr>
                                    <td>{{ $advance->tercero }}</td>
                                    <td>{{ $advance->documento }}</td>
                                    <td>{{ $advance->fecha }}</td>
                                    <td>{{ $advance->detalle }}</td>
                                    <td class="text-right">{{ number_format(abs((float)$advance->saldo_pendiente), 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            @if(count($customerAdvances) == 0)
                                <tr>
                                    <td colspan="5">No hay anticipos de clientes pendientes por aplicar.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                La apertura del turno habilita el Dashboard Gestión Hotelera.
            </div>
        @endif

        <div class="modal fade" id="hotelGuestCreateModal" tabindex="-1" role="dialog" aria-labelledby="hotelGuestCreateModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    {{ Form::open(array('url' => $guestFormCreate['url'], 'id' => 'hotel_guest_create_form', 'files' => true)) }}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="hotelGuestCreateModalLabel">Nuevo huésped</h4>
                        </div>
                        <div class="modal-body">
                            @if(count($guestFormCreate['campos']) > 0)
                                {{ $vistaController::campos_dos_colummnas($guestFormCreate['campos']) }}
                            @else
                                <div class="alert alert-warning">No se encontraron campos configurados para el modelo Huesped.</div>
                            @endif

                            {{ Form::hidden('url_id', $appId) }}
                            {{ Form::hidden('url_id_modelo', $guestModelId) }}
                            {{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}
                            {{ Form::hidden('return_to', $returnTo) }}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success" id="hotel_guest_save_button"><i class="fa fa-save"></i> Guardar huésped</button>
                        </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        <div class="modal fade" id="hotelDashboardCheckOutModal" tabindex="-1" role="dialog" aria-labelledby="hotelDashboardCheckOutModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="POST" id="hotel_dashboard_check_out_form" action="">
                        {{ csrf_field() }}
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="hotelDashboardCheckOutModalLabel">Confirmar check-out</h4>
                        </div>
                        <div class="modal-body">
                            <p id="hotel_dashboard_check_out_room_text">Registrar check-out de la habitacion.</p>
                            <div class="form-group">
                                <label for="hotel_dashboard_check_out_at">Fecha y hora de check-out</label>
                                <input type="datetime-local" name="check_out_at" id="hotel_dashboard_check_out_at" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success"><i class="fa fa-sign-out"></i> Registrar check-out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script type="text/javascript">
        $(document).ready(function() {
            $('#hotel_guest_create_form').on('submit', function() {
                var $button = $('#hotel_guest_save_button');
                $button.prop('disabled', true);
                $button.html('<i class="fa fa-spinner fa-spin"></i> Guardando...');
            });

            $('.hotel-dashboard-checkout-btn').on('click', function() {
                var action = $(this).data('action');
                var room = $(this).data('room');
                var checkOutAt = $(this).data('checkout-at');

                $('#hotel_dashboard_check_out_form').attr('action', action);
                $('#hotel_dashboard_check_out_at').val(checkOutAt);
                $('#hotel_dashboard_check_out_room_text').text('Registrar check-out de la habitacion ' + room + '.');
                $('#hotelDashboardCheckOutModal').modal('show');
            });

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
                            $form.submit();
                        }
                    });
                }
            });
        });
    </script>
@endsection
