@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <h3>Editar habitacion {{ $room->room_number }}</h3>
            <form method="POST" action="{{ url('hotel/rooms/'.$room->id) }}">
                {{ csrf_field() }}
                <input type="hidden" name="_method" value="PUT">
                @include('hotel.rooms.form')
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ url('hotel/rooms/'.$room->id) }}" class="btn btn-default">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
