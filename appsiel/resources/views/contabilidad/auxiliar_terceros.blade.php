@extends('layouts.principal')
@section('content')
<div class="container-fluid">
    <h3>Libro auxiliar por terceros</h3>
    <p>Acumulados mensuales, consolidado anual y movimientos de soporte.</p>
    @if(session('mensaje_error'))
        <div class="alert alert-danger">{{ session('mensaje_error') }}</div>
    @endif
    {{ Form::open(['url'=>'contab_auxiliar_terceros/exportar', 'method'=>'POST']) }}
    <div class="row">
        <div class="col-sm-6 form-group">
            <label for="empresa">Empresa activa</label>
            <select name="empresa" id="empresa" class="form-control"><option value="{{ $empresa->id }}">{{ $empresa->descripcion }}</option></select>
            <p class="help-block">Para consultar otra empresa, cambie la empresa activa de su sesión.</p>
        </div>
        <div class="col-sm-3 form-group">
            <label for="ano">Año</label>
            <input id="ano" name="ano" type="number" min="1900" max="9998" required value="{{ old('ano', 2025) }}" class="form-control">
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3 form-group"><label for="tercero">Tercero (opcional)</label>{{ Form::select('tercero', $terceros, old('tercero'), ['id'=>'tercero', 'class'=>'form-control']) }}</div>
        <div class="col-sm-3 form-group"><label for="cuenta">Cuenta (opcional)</label>{{ Form::select('cuenta', $cuentas, old('cuenta'), ['id'=>'cuenta', 'class'=>'form-control']) }}</div>
        <div class="col-sm-3 form-group"><label for="cuenta_desde">Código cuenta desde (opcional)</label><input id="cuenta_desde" name="cuenta_desde" pattern="[0-9]{1,30}" value="{{ old('cuenta_desde') }}" class="form-control"></div>
        <div class="col-sm-3 form-group"><label for="cuenta_hasta">Código cuenta hasta (opcional)</label><input id="cuenta_hasta" name="cuenta_hasta" pattern="[0-9]{1,30}" value="{{ old('cuenta_hasta') }}" class="form-control"></div>
    </div>
    <p>Deje los filtros opcionales vacíos para generar toda la empresa. Se incluyen cuentas y terceros con saldo anterior aunque no tengan movimientos en el año.</p>
    <p>Descargará un ZIP con Excel de dos hojas y un archivo de validaciones. Si el detalle excede el límite de Excel, se divide en partes por tercero y cuenta. La generación puede tardar varios minutos.</p>
    <button class="btn btn-primary" type="submit">Generar y descargar Excel</button>
    {{ Form::close() }}
</div>
@endsection
