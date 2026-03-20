<?php
    $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
    $usar_apm_pago_cxp = (int) config('tesoreria.usar_apm_pago_cxp');
    $formatos_impresion = ['estandar' => 'Estándar', 'pos' => 'POS'];
    $formato_default = 'estandar';

    if ($usar_apm_pago_cxp === 1) {
        $formatos_impresion['apm'] = 'Appsiel Print Manager';
        $formato_default = 'apm';
    }
?>

@extends('transaccion.show')

@section('botones_acciones')
    {{ Form::bsBtnCreate('tesoreria/pagos_cxp/create' . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion) }}
    @if($doc_encabezado->estado != 'Anulado')
        <button class="btn-gmail" id="btn_anular" title="Anular"><i class="fa fa-btn fa-close"></i></button>
    @endif
@endsection

@section('botones_imprimir_email')
    Formato: {{ Form::select('formato_impresion_id', $formatos_impresion, $formato_default, [ 'id' => 'formato_impresion_id' ]) }}
    {{ Form::bsBtnPrint('tesoreria_pagos_cxp_imprimir/' . $id . $variables_url . '&formato_impresion_id=' . $formato_default) }}
@endsection

@section('botones_anterior_siguiente')
    {!! $botones_anterior_siguiente->dibujar('tesoreria/pagos_cxp/', $variables_url) !!}
@endsection

@section('div_advertencia_anulacion')
    <div class="alert alert-warning" style="display: none;">
        <a href="#" id="close" class="close">&times;</a>
        <strong>Advertencia!</strong>
        <br>
        Al anular el documento se eliminan los registros de pagos a las Cuentas por Pagar y el movimiento contable relacionado.
        <br>
        Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url('teso_anular_pago_cxp/' . $id . $variables_url) }}"> Anular </a> </small>
    </div>
@endsection

@section('documento_vista')
    @include('tesoreria.pagos_cxp.documento_vista')
@endsection

@section('otros_scripts')
    @if($usar_apm_pago_cxp === 1)
        <input type="hidden" id="usar_apm_pago_cxp" value="1">
        <input type="hidden" id="apm_ws_url" value="{{ config('ventas.apm_ws_url') }}">
        <input type="hidden" id="apm_printer_id_pago_cxp" value="{{ config('tesoreria.apm_printer_id_pago_cxp') }}">
        <input type="hidden" id="pago_cxp_apm_payload_url" value="{{ url('tesoreria_pagos_cxp_apm_payload/' . $id . $variables_url) }}">
        <input type="hidden" id="pago_cxp_document_label" value="{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}">
        <input type="hidden" id="pago_cxp_core_empresa_id" value="{{ $doc_encabezado->core_empresa_id }}">
        <input type="hidden" id="pago_cxp_core_tipo_transaccion_id" value="{{ $doc_encabezado->core_tipo_transaccion_id }}">
        <input type="hidden" id="pago_cxp_core_tipo_doc_app_id" value="{{ $doc_encabezado->core_tipo_doc_app_id }}">
        <input type="hidden" id="pago_cxp_consecutivo" value="{{ $doc_encabezado->consecutivo }}">

        <script src="{{ asset('assets/js/apm/client.js?aux=' . uniqid()) }}"></script>
        <script src="{{ asset('assets/js/tesoreria/pagos_cxp_apm.js?aux=' . uniqid()) }}"></script>
    @endif
@endsection
