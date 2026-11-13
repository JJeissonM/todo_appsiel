@extends('layouts.principal')
@section('content')
{{ Form::bsMigaPan($miga_pan) }}
@include('layouts.mensajes')
<div class="container-fluid">
    <h3>Clasificación fiscal de retenciones</h3>
    <p>Asocie cada retención con su impuesto y concepto fiscal del año. Para ICA, use una retención por municipio y tarifa; indique el código DANE del municipio de la operación y su actividad ICA.</p>
    <form method="get" action="{{ url('contab_retenciones/configuracion') }}">
        <input type="hidden" name="id" value="{{ Input::get('id') }}">
        <label>Año <input type="number" name="anio" min="2000" max="2100" value="{{ $anio }}" required></label>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
    <div class="table-responsive"><table class="table table-striped">
        <thead><tr><th>Retención</th><th>Clasificación actual</th><th>Actualizar clasificación del año {{ $anio }}</th></tr></thead>
        <tbody>
        @foreach($retenciones as $retencion)
            <?php $actual = isset($config[$retencion->id]) ? $config[$retencion->id] : null; ?>
            <tr><td>{{ $retencion->descripcion }} ({{ $retencion->tasa_retencion }}%)</td>
                <td>{{ $actual ? $actual->tipo_impuesto : 'Sin clasificar' }}<br>{{ $actual ? $actual->concepto : '' }} {{ $actual ? $actual->codigo_municipio : '' }}</td>
                <td><form method="post" action="{{ url('contab_retenciones/configuracion') }}">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{ Input::get('id') }}"><input type="hidden" name="anio" value="{{ $anio }}"><input type="hidden" name="contab_retencion_id" value="{{ $retencion->id }}">
                    <label>Impuesto {{ Form::select('tipo_impuesto', ['FUENTE'=>'Fuente','ICA'=>'ICA','IVA'=>'IVA'], $actual ? $actual->tipo_impuesto : null, ['required'=>'required']) }}</label>
                    <label>Concepto {{ Form::select('concepto', $conceptos, $actual ? $actual->concepto : null, ['required'=>'required']) }}</label>
                    <label>Municipio ICA <input name="codigo_municipio" placeholder="Código DANE (5 dígitos)" maxlength="5" value="{{ $actual ? $actual->codigo_municipio : '' }}"></label>
                    <label>Actividad ICA <input name="actividad_ica" maxlength="30" value="{{ $actual ? $actual->actividad_ica : '' }}"></label>
                    <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                </form></td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
</div>
@endsection
