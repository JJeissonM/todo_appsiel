@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <h3>Nueva habitacion</h3>
            <form method="POST" action="{{ url($hotelUrl::url('hotel/rooms')) }}">
                {{ csrf_field() }}
                @include('hotel.rooms.form')
                <button class="btn btn-primary">Guardar</button>
                <a href="{{ url($hotelUrl::url('hotel/rooms')) }}" class="btn btn-default">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
