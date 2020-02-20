<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion;
?>

@extends('transaccion.show')

@section('botones_acciones')
	{{ Form::bsBtnCreate( 'tesoreria/pagos_cxp/create'.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.$id_transaccion ) }}
	@if($doc_encabezado->estado != 'Anulado')
        <button class="btn btn-danger btn-xs" id="btn_anular"><i class="fa fa-btn fa-close"></i> Anular </button>
    @endif
@endsection

@section('botones_imprimir_email')
	Formato: {{ Form::select('formato_impresion_id',['estandar'=>'Estándar','pos'=>'POS'],null, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'tesoreria_pagos_cxp_imprimir/'.$id.$variables_url.'&formato_impresion_id=estandar' ) }}
@endsection

@section('botones_anterior_siguiente')
	{!! $botones_anterior_siguiente->dibujar( 'tesoreria/pagos_cxp/', $variables_url ) !!}
@endsection

@section('div_advertencia_anulacion')
	<div class="alert alert-warning" style="display: none;">
		<a href="#" id="close" class="close">&times;</a>
		<strong>Advertencia!</strong>
		<br>
		Al anular el documento se eliminan los registros de pagos a las Cuentas por Pagar y el movimiento contable relacionado.
		<br>
		Si realmente quiere anular el documento, haga click en el siguiente enlace: <small> <a href="{{ url('teso_anular_pago_cxp/'.$id.$variables_url ) }}"> Anular </a> </small>
	</div>
@endsection

@section('documento_vista')
	@include('tesoreria.pagos_cxp.documento_vista')
@endsection