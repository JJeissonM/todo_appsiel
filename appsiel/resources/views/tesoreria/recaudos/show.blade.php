<?php
    $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
    @if(isset($nombre))
        {{ Form::bsBtnCreate( 'web/create'.$variables_url ) }}
    @else
        {{ Form::bsBtnCreate( 'tesoreria/recaudos/create'.$variables_url ) }}
    @endif
	@if($doc_encabezado->estado != 'Anulado')
        @if(isset($nombre))
            <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
        @else
            @can('teso_anular_recaudo_general')
                <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-close"></i></button>
            @endcan
        @endif
        @if( $doc_encabezado->core_tipo_transaccion_id == 43)
            <a class="btn-gmail" href="{{ url( 'teso_traslado_efectivo_recontabilizar/'.$id.$variables_url ) }}" title="Recontabilizar"><i class="fa fa-cog"></i></a>
        @endif
    @endif
@endsection

@section('botones_imprimir_email')
    @if(isset($nombre))
        Imprimir
        {{ Form::bsBtnPrint( 'tesoreria/traslado_efectivo/traslado/imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
    @else
         @include('tesoreria.recaudos.botones_imprimir_email')
    @endif
@endsection

@section('botones_anterior_siguiente')
    @if(isset($nombre))
        {!! $botones_anterior_siguiente->dibujar( 'tesoreria/traslado_efectivo/', $variables_url ) !!}
    @else
        {!! $botones_anterior_siguiente->dibujar( 'tesoreria/recaudos/', $variables_url ) !!}
    @endif
@endsection

@section('datos_adicionales_encabezado')
    @if(isset($documentos_cruce) && $documentos_cruce->count() > 0)
        <br/>
        <b>{{ $documentos_cruce->count() > 1 ? 'Documentos de cruce:' : 'Documento de cruce:' }}</b>
        @foreach($documentos_cruce as $documento_cruce)
            <a class="label label-info"
               href="{{ url($documento_cruce->url_form_create.'/'.$documento_cruce->id.'?id='.$documento_cruce->core_app_id.'&id_modelo='.$documento_cruce->core_modelo_id.'&id_transaccion='.$documento_cruce->core_tipo_transaccion_id) }}"
               title="Ver documento de cruce {{ $documento_cruce->prefijo }} {{ $documento_cruce->consecutivo }}">
                <i class="fa fa-exchange"></i>
                {{ $documento_cruce->prefijo }} {{ $documento_cruce->consecutivo }}
            </a>
        @endforeach
    @endif
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
        @if( isset($pdv) && !is_null($pdv) )
            <td style="border: solid 1px #ddd;">
                <b>Punto de ventas: &nbsp;&nbsp;</b> {{ $pdv->descripcion }}
            </td>
        @endif
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
        @if(isset($nombre))
            <a class="btn btn-danger btn-sm" href="{{ url( 'tesoreria/traslado_efectivo/anular/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
        @else
            @can('teso_anular_recaudo_general')
                <a class="btn btn-danger btn-sm" href="{{ url( 'tesoreria/recaudos_anular/'.$id.$variables_url ) }}"><i class="fa fa-arrow-right" aria-hidden="true"></i> Anular </a>
            @else
                <span class="text-danger">No tiene permiso para anular recaudos generales.</span>
            @endcan
        @endif
    </div>
@endsection

@section('documento_vista')
    @include('tesoreria.recaudos.documento_vista')
@endsection
