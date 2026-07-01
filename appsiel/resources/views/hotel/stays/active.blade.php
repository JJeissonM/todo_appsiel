@extends('layouts.principal')

@section('content')
    <?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8"><h3>Estadias activas</h3></div>
                <div class="col-md-4 text-right">
                    <a href="{{ url($hotelUrl::url('web/create')) }}" class="btn btn-success btn-sm">Check-in</a>
                </div>
            </div>
            @include('hotel.stays.table')
            {!! $stays->render() !!}
        </div>
    </div>
@endsection
