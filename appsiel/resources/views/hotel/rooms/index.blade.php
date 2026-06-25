@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8"><h3>Habitaciones</h3></div>
                <div class="col-md-4 text-right">
                    <a href="{{ url('hotel/stays/check-in') }}" class="btn btn-success btn-sm">Check-in</a>
                    <a href="{{ url('hotel/rooms/create') }}" class="btn btn-primary btn-sm">Nueva habitacion</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Numero</th>
                            <th>Tipo</th>
                            <th>Producto</th>
                            <th>Piso</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Activa</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $room)
                            <tr>
                                <td>{{ $room->room_number }}</td>
                                <td>{{ $room->room_type }}</td>
                                <td>{{ $room->product ? $room->product->descripcion : $room->inv_producto_id }}</td>
                                <td>{{ $room->floor }}</td>
                                <td class="text-right">{{ $room->capacity }}</td>
                                <td>{{ $room->status }}</td>
                                <td>{{ $room->is_active ? 'Si' : 'No' }}</td>
                                <td>
                                    <a href="{{ url('hotel/rooms/'.$room->id) }}" class="btn btn-info btn-xs">Ver</a>
                                    <a href="{{ url('hotel/rooms/'.$room->id.'/edit') }}" class="btn btn-warning btn-xs">Editar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {!! $rooms->render() !!}
        </div>
    </div>
@endsection
