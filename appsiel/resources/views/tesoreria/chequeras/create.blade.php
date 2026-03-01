@extends('layouts.principal')

@section('content')
    <div class="container-fluid">
        <h4>Nueva chequera</h4>
        <p><b>Cuenta bancaria:</b> {{ $cuenta->get_value_to_show() }}</p>
        <hr>

        @include('layouts.mensajes')

        {{ Form::open(['url' => 'teso_cuentas_bancarias/'.$cuenta->id.'/chequeras?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')]) }}
            <div class="row">
                <div class="col-md-6">
                    {{ Form::bsText('descripcion', null, 'Descripción', ['required' => 'required']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::bsText('numero_inicial', null, 'Número inicial', ['required' => 'required']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::bsText('numero_final', null, 'Número final', ['required' => 'required']) }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    {{ Form::bsText('consecutivo_actual', null, 'Consecutivo actual', ['required' => 'required']) }}
                </div>
                <div class="col-md-3">
                    {{ Form::bsSelect('estado', 'Activo', 'Estado', ['Activo' => 'Activo', 'Inactiva' => 'Inactiva', 'Agotada' => 'Agotada'], ['required' => 'required']) }}
                </div>
            </div>

            <button class="btn btn-primary btn-sm" type="submit">Guardar</button>
            <a class="btn btn-default btn-sm" href="{{ url('teso_cuentas_bancarias/'.$cuenta->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion')) }}">Volver</a>
        {{ Form::close() }}
    </div>
@endsection
