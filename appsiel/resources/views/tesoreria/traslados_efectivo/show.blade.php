<?php
    $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
        {{ Form::bsBtnCreate( 'web/create'.$variables_url ) }}
        
    @if($doc_encabezado->estado != 'Anulado')
        <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
        <a class="btn-gmail" href="{{ url( 'teso_traslado_efectivo_recontabilizar/'.$id.$variables_url ) }}" title="Recontabilizar"><i class="fa fa-cog"></i></a>
    @endif
@endsection

@section('botones_imprimir_email')
        Formato: {{ Form::select('formato_impresion_id',['pos'=>'POS','estandar'=>'Estándar','estandar2'=>'Estándar v2','colegio'=>'Colegio'],null, [ 'id' =>'formato_impresion_id' ]) }}
        {{ Form::bsBtnPrint( 'tesoreria/recaudos_imprimir/'.$id.$variables_url.'&formato_impresion_id=pos' ) }}
@endsection

@section('botones_anterior_siguiente')
    {!! $botones_anterior_siguiente->dibujar( 'tesoreria/traslado_efectivo/', $variables_url ) !!}
@endsection

@section('datos_adicionales_encabezado')
@endsection

@section('filas_adicionales_encabezado')
    <tr>
        <td style="border: solid 1px #ddd;">
            <b>Tercero:</b> {{ $doc_encabezado->tercero_nombre_completo }}
            <br/>
            <b>{{ config("configuracion.tipo_identificador") }}: &nbsp;&nbsp;</b>
			@if( config("configuracion.tipo_identificador") == 'NIT') {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}	@else {{ $doc_encabezado->numero_identificacion}} @endif
            <br/>
            <b>Dirección: &nbsp;&nbsp;</b> {{ $doc_encabezado->direccion1 }}
            <br/>
            <b>Teléfono: &nbsp;&nbsp;</b> {{ $doc_encabezado->telefono1 }}
            @include('layouts.elementos.label_show_email',['email' => $doc_encabezado->email])
        </td>
    </tr>
    <tr>
        <td colspan="2" style="border: solid 1px #ddd;">
            <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
        </td>
    </tr>
@endsection

@section('div_advertencia_anulacion')
    <div class="alert alert-warning" style="display: none;">
        <a href="#" id="close" class="close">&times;</a>
        <strong>¡ADVERTENCIA!</strong>
        <br>
        Al anular el documento se eliminan los registros del movimiento contable relacionado. La anulación no puede revertirse. Si quieres confirmar, hacer click en:
        <a class="btn btn-danger btn-sm" href="{{ url( 'tesoreria/traslado_efectivo/anular/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
    </div>
@endsection

@section('documento_vista')
    @include('tesoreria.recaudos.documento_vista')
@endsection