@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8"><h3>Habitacion {{ $room->room_number }}</h3></div>
                <div class="col-md-4 text-right">
                    <a href="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/edit')) }}" class="btn btn-warning btn-sm">Editar</a>
                    <a href="{{ url($hotelUrl::url('hotel/rooms')) }}" class="btn btn-default btn-sm">Volver</a>
                </div>
            </div>
            <table class="table table-bordered">
                <tr><th>Tipo</th><td>{{ $room->room_type }}</td></tr>
                <tr><th>Producto</th><td>{{ $room->product ? $room->product->descripcion : $room->inv_producto_id }}</td></tr>
                <tr><th>Piso</th><td>{{ $room->floor }}</td></tr>
                <tr><th>Capacidad</th><td>{{ $room->capacity }}</td></tr>
                <tr><th>Estado</th><td>{{ $room->status }}</td></tr>
                <tr><th>Activa</th><td>{{ $room->is_active ? 'Si' : 'No' }}</td></tr>
                <tr><th>Descripcion</th><td>{{ $room->description }}</td></tr>
            </table>
            <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/status')) }}" class="form-inline">
                {{ csrf_field() }}
                <select name="status" class="form-control">
                    @foreach(App\Hotel\HotelRoom::statuses() as $status)
                        <option value="{{ $status }}" {{ $room->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Cambiar estado</button>
            </form>
            <hr>
            <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms/'.$room->id.'/deactivate')) }}">
                {{ csrf_field() }}
                <button class="btn btn-danger" onclick="return confirm('Desea desactivar esta habitacion?')">Desactivar</button>
            </form>
        </div>
    </div>
@endsection
