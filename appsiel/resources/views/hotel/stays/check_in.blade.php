@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <h3>Check-in</h3>
            <form method="POST" action="{{ url('hotel/stays/check-in') }}">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Huesped principal</label>
                            <select name="main_cliente_id" class="form-control" required>
                                @foreach($clients as $key => $label)
                                    <option value="{{ $key }}" {{ old('main_cliente_id') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Habitacion disponible</label>
                            <select name="room_id" class="form-control" required>
                                <option value=""></option>
                                @foreach($rooms as $room)
                                    <option value="{{ $room->id }}" {{ old('room_id', Input::get('room_id')) == $room->id ? 'selected' : '' }}>{{ $room->room_number }} - {{ $room->room_type }} - cap. {{ $room->capacity }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Check-in</label>
                            <input type="text" name="check_in_at" class="form-control" value="{{ old('check_in_at', date('Y-m-d H:i:s')) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Salida esperada</label>
                            <input type="text" name="expected_check_out_at" class="form-control" value="{{ old('expected_check_out_at') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Adultos</label>
                            <input type="number" name="adults_count" min="1" class="form-control" value="{{ old('adults_count', 1) }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ninos</label>
                            <input type="number" name="children_count" min="0" class="form-control" value="{{ old('children_count', 0) }}">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notas</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
                <button class="btn btn-success">Registrar check-in</button>
                <a href="{{ url('hotel/stays') }}" class="btn btn-default">Cancelar</a>
            </form>
        </div>
    </div>
@endsection
