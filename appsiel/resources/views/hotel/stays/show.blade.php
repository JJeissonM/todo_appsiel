@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8">
                    <h3>Estadia #{{ $stay->id }} - Habitacion {{ $stay->room ? $stay->room->room_number : $stay->room_id }}</h3>
                </div>
                <div class="col-md-4 text-right">
                    @if($stay->order)
                        <a href="{{ url('hotel/orders/'.$stay->order->id) }}" class="btn btn-primary btn-sm">Ver pedido</a>
                    @endif
                    <a href="{{ url('hotel/stays') }}" class="btn btn-default btn-sm">Volver</a>
                </div>
            </div>
            <table class="table table-bordered">
                <tr><th>Huesped principal</th><td>{{ $stay->mainGuest && $stay->mainGuest->tercero ? $stay->mainGuest->tercero->descripcion : $stay->main_cliente_id }}</td></tr>
                <tr><th>Check-in</th><td>{{ $stay->check_in_at }}</td></tr>
                <tr><th>Salida esperada</th><td>{{ $stay->expected_check_out_at }}</td></tr>
                <tr><th>Check-out</th><td>{{ $stay->check_out_at }}</td></tr>
                <tr><th>Huespedes</th><td>{{ $stay->total_guests }} ({{ $stay->adults_count }} adultos, {{ $stay->children_count }} ninos)</td></tr>
                <tr><th>Estado</th><td>{{ $stay->status }}</td></tr>
                <tr><th>Notas</th><td>{{ $stay->notes }}</td></tr>
            </table>

            @if($stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                <form method="POST" action="{{ url('hotel/stays/'.$stay->id.'/check-out') }}" style="display:inline-block;">
                    {{ csrf_field() }}
                    <button class="btn btn-success" onclick="return confirm('Registrar check-out?')">Check-out</button>
                </form>
                <form method="POST" action="{{ url('hotel/stays/'.$stay->id.'/cancel') }}" style="display:inline-block;">
                    {{ csrf_field() }}
                    <button class="btn btn-danger" onclick="return confirm('Anular estadia?')">Anular</button>
                </form>
            @endif
        </div>

        <div class="marco_formulario">
            <h4>Huespedes</h4>
            <table class="table table-bordered table-striped">
                <thead><tr><th>Cliente</th><th>Principal</th><th>Relacion</th><th>Accion</th></tr></thead>
                <tbody>
                    @foreach($stay->guests as $guest)
                        <tr>
                            <td>{{ $guest->cliente && $guest->cliente->tercero ? $guest->cliente->tercero->descripcion : $guest->cliente_id }}</td>
                            <td>{{ $guest->is_main_guest ? 'Si' : 'No' }}</td>
                            <td>{{ $guest->relationship }}</td>
                            <td>
                                @if(!$guest->is_main_guest && $stay->status == App\Hotel\HotelStay::STATUS_ACTIVA)
                                    <form method="POST" action="{{ url('hotel/stays/'.$stay->id.'/guests/'.$guest->id.'/delete') }}">
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
                <form method="POST" action="{{ url('hotel/stays/'.$stay->id.'/guests') }}" class="form-inline">
                    {{ csrf_field() }}
                    <select name="cliente_id" class="form-control" required>
                        @foreach($clients as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="relationship" class="form-control" placeholder="Relacion">
                    <button class="btn btn-primary">Agregar huesped</button>
                </form>
            @endif
        </div>
    </div>
@endsection
