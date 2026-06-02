@extends('transaccion.formatos_impresion.pos')

@section('lbl_tercero')
    Tercero:
@endsection

@section('documento_transaccion_prefijo_consecutivo')
    {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
@endsection

@section('encabezado_datos_adicionales')
    @if( isset($pdv) && !is_null($pdv) )
        <br>
        <b>Punto de ventas: &nbsp;&nbsp;</b> {{ $pdv->descripcion }}
    @endif
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

@section('tabla_registros_1')
    @include('tesoreria.formatos_impresion.partials.lineas_pos')
@endsection

@section('lbl_firma')
    Firma del aceptante:
@endsection
