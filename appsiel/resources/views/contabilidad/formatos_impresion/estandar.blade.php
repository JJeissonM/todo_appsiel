@extends('transaccion.formatos_impresion.estandar')

@section('documento_transaccion_prefijo_consecutivo')
    {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
@endsection

@section('documento_datos_adicionales')
    
@endsection

@section('lbl_tercero')
    Tercero:
@endsection

@section('encabezado_datos_adicionales')
    <br>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

@section('tabla_registros_1')
    @include( 'contabilidad.incluir.tabla_registros_documento' )
@endsection

@section('tabla_registros_2')
@endsection

@section('tabla_registros_3')
    @include('transaccion.auditoria')
@endsection