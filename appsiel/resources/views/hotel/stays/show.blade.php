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
                    <h4>Informacion estadia <a href="{{ url($hotelUrl::url('web/'.$stay->id.'/edit?id=22&id_modelo=364&id_transaccion=')) }}"><i class="fa fa-edit"></i></a></h4>
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
                        @if($stay->order)
                            <a href="{{ url($hotelUrl::url('hotel/orders/'.$stay->order->id, array('id_modelo' => $hotelUrl::modelId('App\\Hotel\\HotelOrderHeader')))) }}" class="btn btn-primary btn-sm">Ver pedido</a>
                        @endif
                        <a href="{{ url($hotelUrl::url('hotel?id=22')) }}" class="btn btn-default btn-sm">Volver</a>

                        @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
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
        </div>

    </div>
@endsection
