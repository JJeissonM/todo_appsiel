@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8">
                    <h3>Estadia #{{ $stay->id }} - Habitacion {{ $stay->room ? $stay->room->room_number : $stay->room_id }}</h3>
                </div>
                <div class="col-md-4 text-right">
                    &nbsp;
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <h4>
                        Informacion estadia
                        @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                            <a href="{{ url($hotelUrl::url('web/'.$stay->id.'/edit?id=22&id_modelo=364&id_transaccion=')) }}"><i class="fa fa-edit"></i></a>
                        @endif
                    </h4>
                    <table class="table table-bordered">
                        <tr><th>Huesped principal</th><td>{{ $stay->mainGuest && $stay->mainGuest->tercero ? $stay->mainGuest->tercero->descripcion : $stay->main_cliente_id }}</td></tr>
                        <tr><th>Check-in</th><td>{{ $stay->check_in_at }}</td></tr>
                        <tr><th>Salida esperada</th><td>{{ $stay->expected_check_out_at }}</td></tr>
                        <tr><th>Check-out</th><td>{{ $stay->check_out_at }}</td></tr>
                        <tr><th>Huespedes</th><td>{{ $stay->total_guests }} ({{ $stay->adults_count }} adultos, {{ $stay->children_count }} niños)</td></tr>
                        <tr><th>Estado</th><td>{{ $stay->status }}</td></tr>
                        <tr><th>Notas</th><td>{{ $stay->notes }}</td></tr>
                    </table>


                    <div class="btn-group" role="group">
                        <a href="{{ url($hotelUrl::url('hotel?id=22')) }}" class="btn btn-default btn-sm">Volver</a>

                        @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                            <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/orders')) }}" style="display:inline-block;">
                                {{ csrf_field() }}
                                <button class="btn btn-primary btn-sm">Nuevo pedido</button>
                            </form>
                            <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/check-out')) }}" style="display:inline-block;">
                                {{ csrf_field() }}
                                <button class="btn btn-success btn-sm" onclick="return confirm('Registrar check-out?')">Check-out</button>
                            </form>
                            <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/cancel')) }}" style="display:inline-block;">
                                {{ csrf_field() }}
                                <button class="btn btn-danger btn-sm" onclick="return confirm('Anular estadia?')">Anular</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <h4>Huespedes</h4>
                    <table class="table table-bordered table-striped">
                        <thead><tr><th>Cliente</th><th>Principal</th><th>Parentezco</th><th>Accion</th></tr></thead>
                        <tbody>
                            @foreach($stay->guests as $guest)
                                <?php $isMainGuest = (int)$guest->is_main_guest == 1 || (int)$guest->cliente_id == (int)$stay->main_cliente_id; ?>
                                <tr>
                                    <td>{{ $guest->cliente && $guest->cliente->tercero ? $guest->cliente->tercero->descripcion : $guest->cliente_id }}</td>
                                    <td>{{ $isMainGuest ? 'Si' : 'No' }}</td>
                                    <td>{{ $guest->relationship }}</td>
                                    <td>
                                        @if(!$isMainGuest && $stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                                            <form method="POST" action="{{ url($hotelUrl::url('hotel/stays/'.$stay->id.'/guests/'.$guest->id.'/delete')) }}">
                                                {{ csrf_field() }}
                                                <button class="btn btn-danger btn-xs">Eliminar</button>
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
                            Cliente:
                            <select name="cliente_id" class="combobox" required>
                                @foreach($clients as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="relationship" class="form-control" placeholder="Parentezco">
                            <button class="btn btn-primary">Agregar huesped</button>
                        </form>
                    @endif
                </div>
            </div>

            <hr>

            <h4>Anticipos / saldos a favor del huesped</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Documento</th>
                            <th>Fecha</th>
                            <th>Detalle</th>
                            <th>Saldo a favor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalAnticipos = 0; ?>
                        @foreach($anticipos as $anticipo)
                            <?php
                                $saldoAnticipo = abs((float)$anticipo['saldo_pendiente']);
                                $totalAnticipos += $saldoAnticipo;
                            ?>
                            <tr>
                                <td>{{ $anticipo['documento'] }}</td>
                                <td>{{ $anticipo['fecha'] }}</td>
                                <td>{{ $anticipo['detalle'] }}</td>
                                <td class="text-right">{{ number_format($saldoAnticipo, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        @if(count($anticipos) == 0)
                            <tr>
                                <td colspan="4">El huesped no tiene anticipos disponibles.</td>
                            </tr>
                        @endif
                        <tr>
                            <th colspan="3" class="text-right">Total</th>
                            <th class="text-right">{{ number_format($totalAnticipos, 2, ',', '.') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr>

            <h4>Pedidos hoteleros</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Documento</th>
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
                                <td>{{ $order->order_date }}</td>
                                <td>{{ $order->status }}</td>
                                <td class="text-right">{{ number_format($orderTotal, 2, ',', '.') }}</td>
                                <td>
                                    @if($order->invoice_type == App\Hotel\HotelOrderHeader::INVOICE_POS && $order->pos_doc_id)
                                        POS #{{ $order->pos_doc_id }}
                                    @elseif($order->invoice_type == App\Hotel\HotelOrderHeader::INVOICE_STANDARD && $order->sales_doc_id)
                                        Ventas #{{ $order->sales_doc_id }}
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ url($hotelUrl::url('hotel/orders/'.$order->id, array('id_modelo' => $hotelUrl::modelId('App\\Hotel\\HotelOrderHeader')))) }}" class="btn btn-primary btn-xs">Ver pedido</a>
                                </td>
                            </tr>
                        @endforeach

                        @if(count($stay->orders) == 0)
                            <tr>
                                <td colspan="6">No hay pedidos hoteleros registrados.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection
