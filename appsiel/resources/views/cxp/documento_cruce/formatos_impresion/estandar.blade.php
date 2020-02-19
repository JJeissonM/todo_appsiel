@extends('transaccion.formatos_impresion.estandar')

@section('lbl_tercero')
    Tercero:
@endsection

@section('encabezado_datos_adicionales')
    <br/>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

@section('documento_transaccion_prefijo_consecutivo')
    {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
@endsection

@section('tabla_registros_1')
    {!! $documento_vista !!}
@endsection


@section('tabla_registros_3')
    @include('transaccion.registros_contables')
    @include('transaccion.auditoria')
@endsection